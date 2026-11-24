<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->decimal('product_discount', 12, 2)->default(0)->after('subtotal');
            $table->decimal('discount', 12, 2)->default(0)->after('product_discount');
            $table->decimal('adjustment', 12, 2)->default(0)->after('discount');
            $table->decimal('total', 12, 2)->default(0)->after('adjustment');
        });

        Schema::table('sale_return_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('batch_id')->constrained()->nullOnDelete();
            $table->decimal('discount', 12, 2)->default(0)->after('unit_price');
            $table->decimal('cost_total', 12, 2)->default(0)->after('total');
        });

        // Populate total for existing returns
        DB::statement('UPDATE sale_returns SET total = subtotal WHERE total = 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_return_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn(['discount', 'cost_total']);
        });

        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropColumn(['product_discount', 'discount', 'adjustment', 'total']);
        });
    }
};
