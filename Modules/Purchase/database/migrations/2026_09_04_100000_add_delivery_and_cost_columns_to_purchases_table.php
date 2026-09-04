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
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('do_number')->nullable()->after('invoice_no');
            $table->date('do_date')->nullable()->after('do_number');
            $table->decimal('transportation_cost', 12, 2)->default(0)->after('delivery_charge');
            $table->decimal('adjustment_cost', 12, 2)->default(0)->after('transportation_cost');
            $table->string('vehicle_number')->nullable()->after('employee_phone');
            $table->string('delivery_person_name')->nullable()->after('vehicle_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'do_number',
                'do_date',
                'transportation_cost',
                'adjustment_cost',
                'vehicle_number',
                'delivery_person_name',
            ]);
        });
    }
};
