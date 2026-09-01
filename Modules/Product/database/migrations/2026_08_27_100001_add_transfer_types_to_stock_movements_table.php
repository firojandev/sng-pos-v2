<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY type ENUM(
            'purchase', 'purchase_reversal', 'sale', 'sale_reversal',
            'adjustment_increase', 'adjustment_decrease',
            'transfer_out', 'transfer_in',
            'sale_return', 'purchase_return'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY type ENUM(
            'purchase', 'purchase_reversal', 'sale', 'sale_reversal',
            'adjustment_increase', 'adjustment_decrease'
        ) NOT NULL");
    }
};
