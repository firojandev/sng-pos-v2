<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customers belong to the company; shop_id stays as the shop that created them.
 * Records without a shop are assigned to the default company.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->restrictOnDelete();
        });

        DB::table('customers')->whereNull('company_id')->update([
            'company_id' => DB::raw('(SELECT shops.company_id FROM shops WHERE shops.id = customers.shop_id)'),
        ]);

        // Records without a shop cannot be traced to a company; they go to the
        // default company (id 1, or the oldest company when 1 does not exist).
        if (DB::table('customers')->whereNull('company_id')->exists()) {
            $defaultCompanyId = DB::table('companies')->where('id', 1)->value('id')
                ?? DB::table('companies')->orderBy('id')->value('id');

            if (! $defaultCompanyId) {
                throw new RuntimeException('Customers without a shop need a default company, but no company exists.');
            }

            DB::table('customers')->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable(false)->change();
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The foreign key may be backed by the composite index, so it goes first.
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'status']);
            $table->dropColumn('company_id');
        });
    }
};
