<?php

namespace Modules\Shop\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Role;

/**
 * What every new shop needs to be usable: its Admin role, a main branch and
 * warehouse, and its cash account; and making a user an admin of a shop.
 */
class ShopProvisioner
{
    public function provision(Shop $shop, float $openingCash = 0, ?string $cashName = null): void
    {
        $this->adminRole($shop);

        $branch = Branch::withoutGlobalScopes()->firstOrCreate(
            ['shop_id' => $shop->id],
            ['name' => 'প্রধান শাখা', 'phone' => $shop->phone, 'address' => $shop->address, 'status' => 'active'],
        );

        Warehouse::withoutGlobalScopes()->firstOrCreate(
            ['shop_id' => $shop->id],
            ['branch_id' => $branch->id, 'name' => 'প্রধান গুদাম', 'phone' => $shop->phone, 'address' => $shop->address, 'status' => 'active', 'is_default' => true],
        );

        $cash = Account::withoutGlobalScopes()->firstOrCreate(
            ['shop_id' => $shop->id, 'type' => 'cash'],
            [
                'name' => $cashName ?: 'নগদ টাকা (Cash)',
                'opening_balance' => $openingCash,
                'current_balance' => $openingCash,
                'is_default' => true,
                'status' => 'active',
                'note' => 'প্রধান ক্যাশ অ্যাকাউন্ট (সিস্টেম নির্ধারিত)',
            ],
        );

        if ($openingCash > 0 && $cash->wasRecentlyCreated) {
            AccountTransaction::withoutGlobalScopes()->create([
                'shop_id' => $shop->id,
                'account_id' => $cash->id,
                'type' => 'in',
                'amount' => $openingCash,
                'balance_after' => $openingCash,
                'source' => 'opening_balance',
                'note' => 'প্রারম্ভিক ক্যাশ ব্যালেন্স (Opening Balance)',
                'occurred_at' => now(),
            ]);
        }
    }

    /**
     * Make the user an admin of the shop (and a member of its company).
     */
    public function assignAdmin(Shop $shop, User $user, bool $isOwner = false): void
    {
        DB::transaction(function () use ($shop, $user, $isOwner) {
            $previousTeam = getPermissionsTeamId();
            setPermissionsTeamId($shop->id);
            $user->assignRole($this->adminRole($shop));
            setPermissionsTeamId($previousTeam);

            $shop->users()->syncWithoutDetaching([$user->id => ['role' => 'Admin', 'is_owner' => $isOwner]]);

            if (! $user->shop_id) {
                $user->forceFill(['shop_id' => $shop->id])->save();
            }

            if (! $shop->company->users()->where('users.id', $user->id)->exists()) {
                $shop->company->users()->attach($user->id, ['role' => 'Member', 'is_owner' => false]);
            }
        });
    }

    /**
     * Take a user's admin role at a shop away (their other shops are kept).
     */
    public function removeAdmin(Shop $shop, User $user): void
    {
        DB::transaction(function () use ($shop, $user) {
            $previousTeam = getPermissionsTeamId();
            setPermissionsTeamId($shop->id);
            $user->removeRole($this->adminRole($shop));
            setPermissionsTeamId($previousTeam);

            $shop->users()->detach($user->id);

            if ((int) $user->shop_id === (int) $shop->id) {
                $user->forceFill(['shop_id' => $user->shops()->where('shops.id', '!=', $shop->id)->value('shops.id')])->save();
            }
        });
    }

    public function adminRole(Shop $shop): Role
    {
        return Role::where('shop_id', $shop->id)->where('name', 'Admin')->where('guard_name', 'web')->first()
            ?? Role::create(['shop_id' => $shop->id, 'name' => 'Admin', 'guard_name' => 'web']);
    }
}
