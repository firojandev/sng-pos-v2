<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Straight-line depreciation: an asset's cost less its residual value spread
 * evenly over its useful life (the validity), month by month from its
 * purchase date, each month recorded (and posted to the ledger) once.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->date('purchase_date')->nullable()->after('amount');
            $table->decimal('residual_value', 12, 2)->default(0)->after('purchase_date');
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('period');
            $table->decimal('amount', 12, 2);
            $table->decimal('accumulated', 12, 2);
            $table->decimal('book_value', 12, 2);
            $table->timestamps();
            $table->unique(['asset_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['purchase_date', 'residual_value']);
        });
    }
};
