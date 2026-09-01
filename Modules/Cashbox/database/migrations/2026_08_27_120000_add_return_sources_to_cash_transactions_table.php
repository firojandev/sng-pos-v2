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
        DB::statement("ALTER TABLE cash_transactions MODIFY source ENUM(
            'manual', 'sale', 'purchase', 'income', 'expense', 'sale_return', 'purchase_return'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE cash_transactions MODIFY source ENUM(
            'manual', 'sale', 'purchase', 'income', 'expense'
        ) NOT NULL");
    }
};
