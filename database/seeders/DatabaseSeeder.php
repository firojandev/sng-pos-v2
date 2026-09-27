<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Company\Models\Company;
use Modules\Core\Support\Permissions;
use Modules\Finance\Database\Seeders\AccountDatabaseSeeder;
use Modules\Shop\Database\Seeders\ShopDatabaseSeeder;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds with model events on: the app relies on them to keep its data
 * consistent (a shop gets its company, a user its shop membership).
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);

        $superAdmins = [
            'softngear@gmail.com' => 'SNGSuperAdmin',
            'admin@sngpos.com' => 'SNGPosAdmin',
        ];

        foreach ($superAdmins as $adminEmail => $adminUsername) {
            $superAdmin = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Super Admin',
                    'username' => $adminUsername,
                    'password' => bcrypt('SNGAdmin@2026!'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $superAdmin->email_verified_at) {
                $superAdmin->update(['email_verified_at' => now()]);
            }

            $superAdmin->syncRoles([$superAdminRole]);
        }

        // The Default Company groups every standalone shop; its admin sees them all.
        $defaultCompany = Company::defaultCompany();
        $defaultAdmin = User::firstOrCreate(
            ['email' => 'softngear@gmail.com'],
            [
                'name' => 'Soft N Gear',
                'username' => 'SNGCompanyAdmin',
                'phone' => '+8801886861430',
                'password' => bcrypt('SNGAdmin@2026!'),
                'email_verified_at' => now(),
            ]
        );
        $defaultCompany->users()->syncWithoutDetaching([$defaultAdmin->id => ['role' => Company::ROLE_ADMIN, 'is_owner' => false]]);

        // The demo shop is a standalone shop (its own plan, under the Default Company).
        $demoShop = Shop::firstOrCreate(
            ['slug' => 'sng-shop'],
            [
                'name' => 'Soft N Gear Shop',
                'phone' => '+8801700000000',
                'address' => 'Rajshahi, Bangladesh',
                'status' => 'active',
            ]
        );

        $demoAdminRole = Role::firstOrCreate([
            'shop_id' => $demoShop->id,
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);
        $demoAdminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $demoAdmin = User::updateOrCreate(
            ['email' => 'admin@softngear.com'],
            [
                'shop_id' => $demoShop->id,
                'name' => 'Admin',
                'username' => 'SNGShopAdmin',
                'password' => bcrypt('SNGAdmin@2026!'),
                'email_verified_at' => now(),
            ]
        );
        setPermissionsTeamId($demoShop->id);
        $demoAdmin->syncRoles([$demoAdminRole]);
        setPermissionsTeamId(null);

        // The demo shop admin owns the demo company.
        $demoShop->users()->syncWithoutDetaching([$demoAdmin->id => ['role' => 'Admin', 'is_owner' => true]]);
        $demoShop->company->users()->syncWithoutDetaching([$demoAdmin->id => ['role' => Company::ROLE_OWNER, 'is_owner' => true]]);

        $this->call(ShopDatabaseSeeder::class);
        $this->call(SubscriptionifySeeder::class);
        $this->call(AccountDatabaseSeeder::class);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan && ! $demoShop->subscribed()) {
            $demoShop->subscribe($standardPlan);
        }
    }
}
