<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Give every existing shop its own company (one company per shop) and move
 * the shop's billing records (subscriptions, direct feature grants and
 * feature usage) from the shop to that company.
 */
return new class extends Migration
{
    private const SHOP_CLASS = 'Modules\\Shop\\Models\\Shop';

    private const COMPANY_CLASS = 'Modules\\Company\\Models\\Company';

    /**
     * @var list<string>
     */
    private array $morphTables = ['subscriptions', 'feature_subscribable', 'feature_usages'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('shops')->whereNull('company_id')->orderBy('id')->each(function (object $shop) {
                $now = now();

                $companyId = DB::table('companies')->insertGetId([
                    'name' => $shop->name,
                    'slug' => $this->uniqueCompanySlug((string) ($shop->slug ?: $shop->name)),
                    'phone' => $shop->phone ?? null,
                    'email' => $shop->email ?? null,
                    'address' => $shop->address ?? null,
                    'logo' => $shop->logo ?? null,
                    'status' => $shop->status === 'inactive' ? 'inactive' : 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('shops')->where('id', $shop->id)->update(['company_id' => $companyId]);

                DB::table('shop_user')->where('shop_id', $shop->id)->orderBy('id')->each(function (object $member) use ($companyId, $now) {
                    DB::table('company_user')->insertOrIgnore([
                        'company_id' => $companyId,
                        'user_id' => $member->user_id,
                        'role' => $member->role,
                        'is_owner' => $member->is_owner,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });

                foreach ($this->morphTables as $table) {
                    DB::table($table)
                        ->where('subscribable_type', self::SHOP_CLASS)
                        ->where('subscribable_id', $shop->id)
                        ->update(['subscribable_type' => self::COMPANY_CLASS, 'subscribable_id' => $companyId]);
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function () {
            DB::table('shops')->whereNotNull('company_id')->orderBy('id')->each(function (object $shop) {
                $isFirstShopOfCompany = ! DB::table('shops')
                    ->where('company_id', $shop->company_id)
                    ->where('id', '<', $shop->id)
                    ->exists();

                if (! $isFirstShopOfCompany) {
                    return;
                }

                foreach ($this->morphTables as $table) {
                    DB::table($table)
                        ->where('subscribable_type', self::COMPANY_CLASS)
                        ->where('subscribable_id', $shop->company_id)
                        ->update(['subscribable_type' => self::SHOP_CLASS, 'subscribable_id' => $shop->id]);
                }
            });

            DB::table('shops')->update(['company_id' => null]);
            DB::table('company_user')->delete();
            DB::table('companies')->delete();
        });
    }

    private function uniqueCompanySlug(string $base): string
    {
        $base = Str::slug($base) ?: 'company';
        $slug = $base;
        $suffix = 2;

        while (DB::table('companies')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
};
