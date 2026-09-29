<?php

namespace Modules\FinanceManagement\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\FinanceManagement\Models\Asset;
use Modules\FinanceManagement\Models\AssetDepreciation;

/**
 * Straight-line depreciation: (cost − residual value) ÷ useful life in
 * months, charged each month from the purchase month to the end of the
 * asset's life (the last month takes the rounding). Each month is recorded
 * once; the ledger posts it (Dr Depreciation Expense, Cr Accumulated
 * Depreciation).
 */
class AssetDepreciationService
{
    /**
     * The asset's whole schedule.
     *
     * @return list<array{period: Carbon, amount: float, accumulated: float, book_value: float}>
     */
    public function schedule(Asset $asset): array
    {
        $months = $asset->usefulLifeMonths();

        if (! $asset->isStraightLine() || ! $asset->purchase_date || $months <= 0) {
            return [];
        }

        $cost = (float) $asset->amount;
        $depreciable = max($cost - (float) $asset->residual_value, 0);
        $monthly = round($depreciable / $months, 2);
        $accumulated = 0.0;
        $rows = [];

        for ($i = 0; $i < $months; $i++) {
            $amount = $i === $months - 1 ? round($depreciable - $accumulated, 2) : $monthly;
            $accumulated = round($accumulated + $amount, 2);
            $rows[] = [
                'period' => $asset->purchase_date->copy()->startOfMonth()->addMonthsNoOverflow($i),
                'amount' => $amount,
                'accumulated' => $accumulated,
                'book_value' => round($cost - $accumulated, 2),
            ];
        }

        return $rows;
    }

    /**
     * Record every month due up to (and including) a month, for one shop or
     * all shops.
     *
     * @return int the number of months recorded
     */
    public function recordUpTo(Carbon $month, ?int $shopId = null): int
    {
        $upTo = $month->copy()->startOfMonth();
        $recorded = 0;

        $assets = Asset::withoutGlobalScopes()
            ->where('depreciation_type', 'straight_line')
            ->whereNotNull('purchase_date')
            ->when($shopId, fn ($query) => $query->where('shop_id', $shopId))
            ->get();

        foreach ($assets as $asset) {
            $done = AssetDepreciation::withoutGlobalScopes()->where('asset_id', $asset->id)->pluck('period')
                ->map(fn ($period) => Carbon::parse($period)->format('Y-m'))
                ->flip();

            DB::transaction(function () use ($asset, $upTo, $done, &$recorded) {
                foreach ($this->schedule($asset) as $row) {
                    if ($row['period']->gt($upTo) || $done->has($row['period']->format('Y-m'))) {
                        continue;
                    }

                    AssetDepreciation::withoutGlobalScopes()->create([
                        'asset_id' => $asset->id,
                        'shop_id' => $asset->shop_id,
                        'period' => $row['period']->toDateString(),
                        'amount' => $row['amount'],
                        'accumulated' => $row['accumulated'],
                        'book_value' => $row['book_value'],
                    ]);
                    $recorded++;
                }
            });
        }

        return $recorded;
    }
}
