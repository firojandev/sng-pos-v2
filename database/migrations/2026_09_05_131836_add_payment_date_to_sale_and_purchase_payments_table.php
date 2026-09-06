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
        if (Schema::hasTable('sale_payments') && ! Schema::hasColumn('sale_payments', 'payment_date')) {
            Schema::table('sale_payments', function (Blueprint $table) {
                $table->date('payment_date')->nullable()->after('amount');
            });

            DB::table('sale_payments')->update([
                'payment_date' => DB::raw('DATE(created_at)'),
            ]);
        }

        if (Schema::hasTable('purchase_payments') && ! Schema::hasColumn('purchase_payments', 'payment_date')) {
            Schema::table('purchase_payments', function (Blueprint $table) {
                $table->date('payment_date')->nullable()->after('amount');
            });

            DB::table('purchase_payments')->update([
                'payment_date' => DB::raw('DATE(created_at)'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sale_payments') && Schema::hasColumn('sale_payments', 'payment_date')) {
            Schema::table('sale_payments', function (Blueprint $table) {
                $table->dropColumn('payment_date');
            });
        }

        if (Schema::hasTable('purchase_payments') && Schema::hasColumn('purchase_payments', 'payment_date')) {
            Schema::table('purchase_payments', function (Blueprint $table) {
                $table->dropColumn('payment_date');
            });
        }
    }
};
