<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leave carried into a new year follows each type's policy: a maximum to
 * carry (earned leave: 60 days under the Labour Act, editable), an optional
 * expiry some months into the year, and whether unused days are paid out
 * when an employee leaves.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_carry_forward')->nullable()->after('carry_forward');
            $table->boolean('carry_forward_expires')->default(false)->after('max_carry_forward');
            $table->unsignedSmallInteger('carry_forward_expiry_months')->nullable()->after('carry_forward_expires');
            $table->boolean('is_encashable')->default(false)->after('carry_forward_expiry_months');
        });

        DB::table('leave_types')->where('code', 'EL')->update(['max_carry_forward' => 60, 'is_encashable' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['max_carry_forward', 'carry_forward_expires', 'carry_forward_expiry_months', 'is_encashable']);
        });
    }
};
