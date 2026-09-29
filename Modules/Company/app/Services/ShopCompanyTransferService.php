<?php

namespace Modules\Company\Services;

use Illuminate\Support\Facades\DB;
use Modules\Company\Models\Company;
use Modules\Shop\Models\Shop;

/**
 * Moves a shop into another company, e.g. to group an owner's separate
 * single-shop companies into one multi-shop company.
 */
class ShopCompanyTransferService
{
    /**
     * @var list<string>
     */
    private const BILLING_TABLES = ['subscriptions', 'feature_subscribable', 'feature_usages'];

    public function move(Shop $shop, Company $target): void
    {
        DB::transaction(function () use ($shop, $target) {
            $source = $shop->company;

            $shop->company()->associate($target);
            $shop->save();

            $this->copyShopOwnersInto($shop, $target);

            if ($source && ! $source->shops()->exists()) {
                $this->retire($source, $target);
            }

            $target->clearSubscriptionCache();
            $shop->unsetRelation('company');
        });
    }

    /**
     * Owners of the moved shop become owners in the target company, unless
     * they are already members there.
     */
    private function copyShopOwnersInto(Shop $shop, Company $target): void
    {
        $owners = $shop->users()->wherePivot('is_owner', true)->get();

        foreach ($owners as $owner) {
            $target->users()->syncWithoutDetaching([
                $owner->id => ['role' => $owner->pivot->role, 'is_owner' => true],
            ]);
        }
    }

    /**
     * Archive a company that no longer has shops. When the target company
     * has no billing of its own yet, it takes over the retired company's
     * subscription, direct feature grants and usage.
     */
    private function retire(Company $source, Company $target): void
    {
        if (! $target->subscriptions()->exists()) {
            foreach (self::BILLING_TABLES as $table) {
                $rows = DB::table($table)
                    ->where('subscribable_type', Company::class)
                    ->where('subscribable_id', $source->id);

                // Grants and usage are unique per feature; keep the target's own rows.
                if ($table !== 'subscriptions') {
                    $rows->whereNotIn('feature_id', DB::table($table)
                        ->where('subscribable_type', Company::class)
                        ->where('subscribable_id', $target->id)
                        ->pluck('feature_id'));
                }

                $rows->update(['subscribable_id' => $target->id]);
            }
        }

        $source->users()->detach();
        $source->delete();
    }
}
