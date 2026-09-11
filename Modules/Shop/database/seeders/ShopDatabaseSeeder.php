<?php

namespace Modules\Shop\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Warehouse;

class ShopDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shopId = 1;

        $branch = Branch::firstOrCreate(
            ['shop_id' => $shopId, 'name' => 'Main Branch'],
            ['status' => 'active']
        );

        Warehouse::firstOrCreate(
            ['shop_id' => $shopId, 'branch_id' => $branch->id, 'name' => 'Main Warehouse'],
            ['status' => 'active', 'is_default' => true]
        );
    }
}
