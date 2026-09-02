<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Models\Account;
use Modules\Finance\Services\AccountTransactionService;
use Modules\Shop\Models\Shop;

class AccountDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = app(AccountTransactionService::class);

        foreach (Shop::all() as $shop) {
            $service->getDefaultAccount($shop->id);

            // Add demo Bank and MFS accounts for demo shop if they don't exist
            if ($shop->slug === 'rahim-general-store') {
                Account::firstOrCreate(
                    ['shop_id' => $shop->id, 'type' => 'bank', 'name' => 'ডাচ বাংলা ব্যাংক (DBBL)'],
                    [
                        'account_number' => '120.101.55442',
                        'bank_name' => 'Dutch-Bangla Bank',
                        'branch_name' => 'মিরপুর-১০ শাখা',
                        'opening_balance' => 50000,
                        'current_balance' => 50000,
                        'is_default' => false,
                        'status' => 'active',
                        'note' => 'প্রধান চলতি হিসাব',
                    ]
                );

                Account::firstOrCreate(
                    ['shop_id' => $shop->id, 'type' => 'mfs', 'name' => 'বিকাশ মার্চেন্ট (bKash)'],
                    [
                        'mfs_provider' => 'bkash',
                        'mfs_type' => 'merchant',
                        'account_number' => '01700000000',
                        'opening_balance' => 10000,
                        'current_balance' => 10000,
                        'is_default' => false,
                        'status' => 'active',
                        'note' => 'কাউন্টার কিউআর পেমেন্ট',
                    ]
                );
            }
        }
    }
}
