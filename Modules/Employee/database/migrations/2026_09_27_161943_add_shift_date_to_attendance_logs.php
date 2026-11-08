<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A punch belongs to a shift instance (the day its shift starts), not
 * simply to its calendar date: an 8 PM–4 AM shift's 4:05 AM punch is part
 * of the previous day's shift.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->date('shift_date')->nullable()->after('punched_at');
            $table->index(['employee_id', 'shift_date']);
        });

        DB::table('attendance_logs')->update(['shift_date' => DB::raw('DATE(punched_at)')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'shift_date']);
            $table->dropColumn('shift_date');
        });
    }
};
