<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HR, per company: departments, designations, shifts, holidays, leave
 * types, raw attendance punches (manual, device or import), the daily
 * attendance worked out from them, and leave requests.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['departments', 'designations'] as $table) {
            Schema::create($table, function (Blueprint $blueprint) {
                $blueprint->id();
                $blueprint->foreignId('company_id')->constrained()->cascadeOnDelete();
                $blueprint->string('name');
                $blueprint->timestamps();
                $blueprint->unique(['company_id', 'name']);
            });
        }

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('grace_minutes')->default(10);
            $table->json('weekend_days')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('name');
            $table->enum('type', ['festival', 'public', 'company'])->default('public');
            $table->timestamps();
            $table->unique(['company_id', 'date']);
        });

        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->unsignedSmallInteger('days_per_year')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->unsignedSmallInteger('earn_one_day_per')->nullable();
            $table->boolean('carry_forward')->default(false);
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('punched_at');
            $table->enum('source', ['manual', 'device', 'import'])->default('manual');
            $table->string('device_serial')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['employee_id', 'punched_at']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->unsignedSmallInteger('worked_minutes')->default(0);
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedSmallInteger('early_leave_minutes')->default(0);
            $table->unsignedSmallInteger('overtime_minutes')->default(0);
            $table->enum('status', ['present', 'late', 'half_day', 'absent', 'leave', 'holiday', 'weekend']);
            $table->boolean('is_manual')->default(false);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
            $table->index(['company_id', 'date']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->restrictOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedSmallInteger('days');
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['leave_requests', 'attendances', 'attendance_logs', 'leave_types', 'holidays', 'shifts', 'designations', 'departments'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
