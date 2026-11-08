<?php

namespace Modules\Customer\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Customer\Models\Customer;
use Modules\Customer\Models\CustomerMembership;
use Modules\Customer\Models\LoyaltyPointTransaction;
use Modules\Customer\Models\LoyaltyProgram;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\Shop;

/**
 * Earning, reversing and expiring loyalty points.
 *
 * A sale's points are kept in step with the sale: after it is created,
 * edited, partly returned or deleted, syncSale() works out what the sale
 * should have earned and records only the difference, so re-saving a sale
 * never earns twice.
 */
class LoyaltyService
{
    public function enroll(Customer $customer, ?string $cardNo = null): CustomerMembership
    {
        return CustomerMembership::withoutGlobalScopes()->updateOrCreate(
            ['customer_id' => $customer->id],
            [
                'company_id' => $customer->company_id,
                'card_no' => $cardNo ?: 'M'.str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT),
                'joined_at' => now()->toDateString(),
                'status' => 'active',
            ],
        );
    }

    public function balance(Customer $customer): int
    {
        return (int) LoyaltyPointTransaction::withoutGlobalScopes()->where('customer_id', $customer->id)->sum('points');
    }

    public function syncSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale = Sale::withoutGlobalScopes()->withTrashed()->with(['customer.membership', 'shop'])->findOrFail($sale->id);
            $earnedByCustomer = $this->earnedBySale($sale);

            // Points earned for a customer the sale no longer belongs to go back.
            foreach ($earnedByCustomer as $customerId => $earned) {
                if ((int) $customerId !== (int) $sale->customer_id && $earned !== 0) {
                    $this->record($sale, (int) $customerId, -$earned);
                }
            }

            if ($sale->customer_id) {
                $delta = $this->pointsDue($sale) - (int) ($earnedByCustomer[$sale->customer_id] ?? 0);

                if ($delta !== 0) {
                    $this->record($sale, (int) $sale->customer_id, $delta);
                }
            }

            $this->syncRedemption($sale);
        });
    }

    /**
     * Whether members earn and redeem points at the shop.
     */
    public function isActiveAt(?Shop $shop): bool
    {
        if (! $shop || ! $shop->loyalty_enabled) {
            return false;
        }

        $program = LoyaltyProgram::forCompany($shop->company_id);

        return $program->exists && $program->is_enabled;
    }

    /**
     * What a customer can redeem on a sale: their balance plus the points
     * already redeemed on the sale being edited.
     *
     * @return array{member: bool, balance: int, available: int, point_value: float, min_redeem_points: int, max_redeem_percent: int}
     */
    public function summary(Customer $customer, ?Sale $editing = null): array
    {
        $program = LoyaltyProgram::forCompany($customer->company_id);
        $balance = $this->balance($customer);
        $alreadyOnSale = $editing && (int) $editing->customer_id === (int) $customer->id
            ? (int) $editing->loyalty_points_redeemed
            : 0;

        return [
            'member' => $customer->isLoyaltyMember(),
            'balance' => $balance,
            'available' => max($balance + $alreadyOnSale, 0),
            'point_value' => (float) ($program->point_value ?? 0),
            'value' => round($balance * (float) ($program->point_value ?? 0), 2),
            'spend_amount' => (float) ($program->spend_amount ?? 0),
            'points_per_spend' => (int) ($program->points_per_spend ?? 0),
            'min_redeem_points' => (int) ($program->min_redeem_points ?? 0),
            'max_redeem_percent' => (int) ($program->max_redeem_percent ?? 100),
        ];
    }

    /**
     * The discount for redeeming points on a bill, after checking the
     * programme's rules. Throws a validation error when they are broken.
     */
    public function redemptionDiscount(?Customer $customer, int $points, float $billAmount, ?Shop $shop, ?Sale $editing = null): float
    {
        if ($points <= 0) {
            return 0.0;
        }

        $fail = fn (string $message) => throw ValidationException::withMessages(['loyalty_points' => $message]);

        if (! $customer || ! $customer->isLoyaltyMember() || ! $this->isActiveAt($shop)) {
            $fail('এই গ্রাহক পয়েন্ট ব্যবহার করতে পারবেন না (Points cannot be redeemed for this customer)।');
        }

        $summary = $this->summary($customer, $editing);

        if ($points > $summary['available']) {
            $fail("গ্রাহকের পর্যাপ্ত পয়েন্ট নেই (Only {$summary['available']} points available)।");
        }

        if ($points < $summary['min_redeem_points']) {
            $fail("কমপক্ষে {$summary['min_redeem_points']} পয়েন্ট রিডিম করতে হবে (Redeem at least {$summary['min_redeem_points']} points)।");
        }

        $discount = round($points * $summary['point_value'], 2);
        $maxDiscount = round(max($billAmount, 0) * $summary['max_redeem_percent'] / 100, 2);

        if ($discount > $maxDiscount) {
            $fail("বিলের সর্বোচ্চ {$summary['max_redeem_percent']}% পয়েন্টে পরিশোধ করা যায় (Points can pay at most ৳{$maxDiscount})।");
        }

        return $discount;
    }

    /**
     * Expire unspent points whose expiry date has passed.
     */
    public function expirePoints(?Carbon $today = null): int
    {
        $today ??= now()->startOfDay();
        $expired = 0;

        LoyaltyPointTransaction::withoutGlobalScopes()
            ->where('remaining', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $today->toDateString())
            ->orderBy('id')
            ->each(function (LoyaltyPointTransaction $earning) use (&$expired) {
                DB::transaction(function () use ($earning, &$expired) {
                    LoyaltyPointTransaction::withoutGlobalScopes()->create([
                        'company_id' => $earning->company_id,
                        'shop_id' => $earning->shop_id,
                        'customer_id' => $earning->customer_id,
                        'type' => LoyaltyPointTransaction::EXPIRE,
                        'points' => -$earning->remaining,
                        'note' => 'Points expired',
                    ]);

                    $expired += $earning->remaining;
                    $earning->update(['remaining' => 0]);
                });
            });

        return $expired;
    }

    /**
     * Keep the points redeemed on the sale in step with it: a new or larger
     * redemption spends the customer's oldest unspent points; a smaller one,
     * a deleted sale or a changed customer gives points back.
     */
    private function syncRedemption(Sale $sale): void
    {
        $redeemedByCustomer = LoyaltyPointTransaction::withoutGlobalScopes()
            ->where('source_type', Sale::class)
            ->where('source_id', $sale->id)
            ->whereIn('type', [LoyaltyPointTransaction::REDEEM, LoyaltyPointTransaction::ADJUST])
            ->groupBy('customer_id')
            ->selectRaw('customer_id, -SUM(points) as points')
            ->pluck('points', 'customer_id')
            ->map(fn ($points) => (int) $points);

        foreach ($redeemedByCustomer as $customerId => $redeemed) {
            if ((int) $customerId !== (int) $sale->customer_id && $redeemed > 0) {
                $this->giveBack($sale, (int) $customerId, $redeemed);
            }
        }

        if (! $sale->customer_id) {
            return;
        }

        $wanted = $sale->trashed() ? 0 : (int) $sale->loyalty_points_redeemed;
        $delta = $wanted - (int) ($redeemedByCustomer[$sale->customer_id] ?? 0);

        if ($delta > 0) {
            $this->spend($sale, (int) $sale->customer_id, $delta);
        } elseif ($delta < 0) {
            $this->giveBack($sale, (int) $sale->customer_id, -$delta);
        }
    }

    private function spend(Sale $sale, int $customerId, int $points): void
    {
        $program = LoyaltyProgram::forCompany($sale->shop?->company_id);

        LoyaltyPointTransaction::withoutGlobalScopes()->create([
            'company_id' => $sale->shop?->company_id,
            'shop_id' => $sale->shop_id,
            'customer_id' => $customerId,
            'type' => LoyaltyPointTransaction::REDEEM,
            'points' => -$points,
            'value' => round($points * (float) ($program->point_value ?? 0), 2),
            'source_type' => Sale::class,
            'source_id' => $sale->id,
            'note' => $sale->invoice_no,
            'created_by' => Auth::id(),
        ]);

        // Oldest-expiring points are spent first.
        $unspent = LoyaltyPointTransaction::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->where('remaining', '>', 0)
            ->orderByRaw('expires_at IS NULL, expires_at')
            ->orderBy('id')
            ->get();

        foreach ($unspent as $row) {
            if ($points <= 0) {
                break;
            }

            $take = min($points, $row->remaining);
            $row->update(['remaining' => $row->remaining - $take]);
            $points -= $take;
        }
    }

    private function giveBack(Sale $sale, int $customerId, int $points): void
    {
        $program = LoyaltyProgram::forCompany($sale->shop?->company_id);

        LoyaltyPointTransaction::withoutGlobalScopes()->create([
            'company_id' => $sale->shop?->company_id,
            'shop_id' => $sale->shop_id,
            'customer_id' => $customerId,
            'type' => LoyaltyPointTransaction::ADJUST,
            'points' => $points,
            'value' => round($points * (float) ($program->point_value ?? 0), 2),
            'remaining' => $points,
            'expires_at' => $program->points_expire_after_days
                ? now()->addDays($program->points_expire_after_days)->toDateString()
                : null,
            'source_type' => Sale::class,
            'source_id' => $sale->id,
            'note' => 'Redeemed points returned: '.$sale->invoice_no,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * What the sale should currently have earned its customer.
     */
    private function pointsDue(Sale $sale): int
    {
        $program = LoyaltyProgram::forCompany($sale->shop?->company_id);
        $customer = $sale->customer;

        $eligible = ! $sale->trashed()
            && $program->exists
            && $program->is_enabled
            && ($sale->shop?->loyalty_enabled ?? false)
            && $customer?->isLoyaltyMember();

        if (! $eligible) {
            return 0;
        }

        $returned = (float) $sale->returns()->sum('subtotal');
        $earnable = (float) $sale->total - (float) $sale->delivery_charge - $returned;

        return $program->pointsFor($earnable);
    }

    /**
     * Net points each customer has earned from the sale so far.
     *
     * @return array<int, int>
     */
    private function earnedBySale(Sale $sale): array
    {
        return LoyaltyPointTransaction::withoutGlobalScopes()
            ->where('source_type', Sale::class)
            ->where('source_id', $sale->id)
            ->whereIn('type', [LoyaltyPointTransaction::EARN, LoyaltyPointTransaction::REVERSE])
            ->groupBy('customer_id')
            ->selectRaw('customer_id, SUM(points) as points')
            ->pluck('points', 'customer_id')
            ->map(fn ($points) => (int) $points)
            ->all();
    }

    private function record(Sale $sale, int $customerId, int $points): void
    {
        $program = LoyaltyProgram::forCompany($sale->shop?->company_id);
        $isEarning = $points > 0;

        LoyaltyPointTransaction::withoutGlobalScopes()->create([
            'company_id' => $sale->shop?->company_id,
            'shop_id' => $sale->shop_id,
            'customer_id' => $customerId,
            'type' => $isEarning ? LoyaltyPointTransaction::EARN : LoyaltyPointTransaction::REVERSE,
            'points' => $points,
            'value' => round($points * (float) ($program->point_value ?? 0), 2),
            'remaining' => $isEarning ? $points : 0,
            'expires_at' => $isEarning && $program->points_expire_after_days
                ? now()->addDays($program->points_expire_after_days)->toDateString()
                : null,
            'source_type' => Sale::class,
            'source_id' => $sale->id,
            'note' => $sale->invoice_no,
            'created_by' => Auth::id(),
        ]);

        if (! $isEarning) {
            $this->consumeEarnings($sale, $customerId, -$points);
        }
    }

    /**
     * A reversal takes back the sale's own unspent points first (newest first).
     */
    private function consumeEarnings(Sale $sale, int $customerId, int $points): void
    {
        $earnings = LoyaltyPointTransaction::withoutGlobalScopes()
            ->where('source_type', Sale::class)
            ->where('source_id', $sale->id)
            ->where('customer_id', $customerId)
            ->where('type', LoyaltyPointTransaction::EARN)
            ->where('remaining', '>', 0)
            ->orderByDesc('id')
            ->get();

        foreach ($earnings as $earning) {
            if ($points <= 0) {
                break;
            }

            $take = min($points, $earning->remaining);
            $earning->update(['remaining' => $earning->remaining - $take]);
            $points -= $take;
        }
    }
}
