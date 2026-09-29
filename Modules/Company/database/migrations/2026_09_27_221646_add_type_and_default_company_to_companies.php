<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Companies come in three types:
 *
 * - company: a business with one or more shops; its plan covers them all.
 * - default: the Default Company, grouping every standalone shop. It has no
 *   plan of its own; its admins see (and can open) those shops.
 * - standalone: the private record behind one standalone shop, under the
 *   Default Company. It carries that shop's own plan and keeps its data
 *   (customers, catalogue, ledger, HR) apart from every other shop.
 *
 * Existing single-shop companies become standalone shops.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('type', 20)->default('company')->after('slug');
            $table->foreignId('parent_id')->nullable()->after('type')->constrained('companies')->nullOnDelete();
            $table->index('type');
        });

        $defaultId = DB::table('companies')->where('type', 'default')->value('id') ?? DB::table('companies')->insertGetId([
            'name' => 'ডিফল্ট কোম্পানি (Default Company)',
            'slug' => DB::table('companies')->where('slug', 'default-company')->exists() ? 'default-company-'.substr(md5((string) microtime(true)), 0, 6) : 'default-company',
            'type' => 'default',
            'status' => 'active',
            'fiscal_year_start_month' => 7,
            'currency' => 'BDT',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $singleShopCompanies = DB::table('shops')
            ->select('company_id')
            ->whereNotNull('company_id')
            ->groupBy('company_id')
            ->havingRaw('COUNT(*) = 1')
            ->pluck('company_id');

        DB::table('companies')
            ->whereIn('id', $singleShopCompanies)
            ->where('id', '!=', $defaultId)
            ->update(['type' => 'standalone', 'parent_id' => $defaultId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $defaultId = DB::table('companies')->where('type', 'default')->value('id');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'parent_id']);
        });

        if ($defaultId && ! DB::table('shops')->where('company_id', $defaultId)->exists()) {
            DB::table('companies')->where('id', $defaultId)->delete();
        }
    }
};
