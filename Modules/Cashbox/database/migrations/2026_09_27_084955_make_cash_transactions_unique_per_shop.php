<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A sale or purchase can now have one cashbox row per shop (a due settled
 * from another shop of the company), so the source is unique per shop.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->unique(['sourceable_type', 'sourceable_id', 'shop_id'], 'cash_transactions_source_shop_unique');
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropUnique(['sourceable_type', 'sourceable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->unique(['sourceable_type', 'sourceable_id']);
        });

        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropUnique('cash_transactions_source_shop_unique');
        });
    }
};
