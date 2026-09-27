<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogue records become company-owned (company_id set) or shared by all
 * companies (company_id null). Existing records become private to the
 * company of the shop that created them; records without a shop go to the
 * default company (id 1, or the oldest company). shop_id is kept as the
 * shop that created the record.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = ['categories', 'brands', 'units', 'product_models', 'products'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultCompanyId = DB::table('companies')->where('id', 1)->value('id')
            ?? DB::table('companies')->orderBy('id')->value('id');

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('company_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            });

            DB::table($table)->whereNotNull('shop_id')->update([
                'company_id' => DB::raw("(SELECT shops.company_id FROM shops WHERE shops.id = {$table}.shop_id)"),
            ]);

            if ($defaultCompanyId) {
                DB::table($table)->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
            }
        }

        Schema::table('categories', function (Blueprint $blueprint) {
            $blueprint->index(['company_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['company_id']);
        });

        Schema::table('categories', function (Blueprint $blueprint) {
            $blueprint->dropIndex(['company_id', 'type']);
            $blueprint->dropColumn('company_id');
        });

        foreach (array_diff($this->tables, ['categories']) as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('company_id');
            });
        }
    }
};
