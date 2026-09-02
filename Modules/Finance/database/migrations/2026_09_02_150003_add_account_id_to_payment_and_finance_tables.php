<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('sale_payments') && ! Schema::hasColumn('sale_payments', 'account_id')) {
            Schema::table('sale_payments', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->after('sale_id')->constrained('accounts')->nullOnDelete();
            });
        }

        if (Schema::hasTable('purchase_payments') && ! Schema::hasColumn('purchase_payments', 'account_id')) {
            Schema::table('purchase_payments', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->after('purchase_id')->constrained('accounts')->nullOnDelete();
            });
        }

        if (Schema::hasTable('incomes') && ! Schema::hasColumn('incomes', 'account_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->after('shop_id')->constrained('accounts')->nullOnDelete();
            });
        }

        if (Schema::hasTable('expenses') && ! Schema::hasColumn('expenses', 'account_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->after('shop_id')->constrained('accounts')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sale_payments') && Schema::hasColumn('sale_payments', 'account_id')) {
            Schema::table('sale_payments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('account_id');
            });
        }

        if (Schema::hasTable('purchase_payments') && Schema::hasColumn('purchase_payments', 'account_id')) {
            Schema::table('purchase_payments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('account_id');
            });
        }

        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'account_id')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropConstrainedForeignId('account_id');
            });
        }

        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'account_id')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('account_id');
            });
        }
    }
};
