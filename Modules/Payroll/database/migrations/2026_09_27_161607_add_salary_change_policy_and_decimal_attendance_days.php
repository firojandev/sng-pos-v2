<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How a salary change inside a month is paid, and attendance days that can
 * be halves (a half day is 0.5 present, 0.5 absent).
 *
 * Attendance deductions now count every working day without a record as
 * absent, so they stay on only for companies that keep attendance.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->enum('salary_change_policy', ['prorated', 'next_month', 'full_month'])->default('prorated')->after('attendance_deductions');
            $table->boolean('attendance_deductions')->default(false)->change();
        });

        $companiesKeepingAttendance = DB::table('attendances')->distinct()->pluck('company_id');
        DB::table('payroll_settings')->update(['attendance_deductions' => false]);
        DB::table('payroll_settings')->whereIn('company_id', $companiesKeepingAttendance)->update(['attendance_deductions' => true]);

        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('present_days', 5, 1)->default(0)->change();
            $table->decimal('absent_days', 5, 1)->default(0)->change();
            $table->decimal('paid_leave_days', 5, 1)->default(0)->change();
            $table->decimal('unpaid_leave_days', 5, 1)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->unsignedTinyInteger('present_days')->default(0)->change();
            $table->unsignedTinyInteger('absent_days')->default(0)->change();
            $table->unsignedTinyInteger('paid_leave_days')->default(0)->change();
            $table->unsignedTinyInteger('unpaid_leave_days')->default(0)->change();
        });

        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn('salary_change_policy');
            $table->boolean('attendance_deductions')->default(true)->change();
        });
    }
};
