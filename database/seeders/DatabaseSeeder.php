<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Core\Support\Permissions;
use Modules\Finance\Database\Seeders\AccountDatabaseSeeder;
use Modules\Shop\Database\Seeders\ShopDatabaseSeeder;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

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

        $demoShop = Shop::firstOrCreate(
            ['slug' => 'rahim-general-store'],
            [
                'name' => 'রহিম জেনারেল স্টোর',
                'phone' => '+8801700000000',
                'address' => 'মিরপুর-১০, ঢাকা-১২১৬',
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
            ]
        );
        setPermissionsTeamId($demoShop->id);
        $demoAdmin->syncRoles([$demoAdminRole]);
        setPermissionsTeamId(null);

        $this->call(ShopDatabaseSeeder::class);
        $this->call(SubscriptionifySeeder::class);
        $this->call(AccountDatabaseSeeder::class);

        $standardPlan = Plan::where('slug', 'standard')->first();
        if ($standardPlan && ! $demoShop->subscribed()) {
            $demoShop->subscribe($standardPlan);
        }
    }
}
