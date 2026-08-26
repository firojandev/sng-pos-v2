<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Conversion factor direction: by default (is_smaller_unit = false) a unit's
     * conversion_factor means "1 of this unit = conversion_factor base units"
     * (e.g. a Carton with factor 12 holds 12 base Pieces). Some units are instead
     * a subdivision of the base unit (e.g. Litre when the base unit is a Drum,
     * where 1 Drum = 204 Litres) -- for those, is_smaller_unit flips the math so
     * conversion_factor means "conversion_factor of this unit = 1 base unit".
     */
    public function up(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->boolean('is_smaller_unit')->default(false)->after('conversion_factor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn('is_smaller_unit');
        });
    }
};
