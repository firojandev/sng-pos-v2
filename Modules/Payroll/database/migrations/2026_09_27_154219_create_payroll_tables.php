<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payroll, per company: settings, the salary structure, salary revisions,
 * income tax years, loans and advances, monthly (and festival bonus) runs
 * with their payslips, salary payments and final settlements.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('overtime_enabled')->default(true);
            $table->decimal('overtime_multiplier', 4, 2)->default(2);
            $table->unsignedSmallInteger('overtime_hours_base')->default(208);
            $table->boolean('attendance_deductions')->default(true);
            $table->enum('absence_deduction_basis', ['basic', 'gross'])->default('basic');
            $table->unsignedTinyInteger('late_days_per_deduction')->nullable();
            $table->boolean('pf_enabled')->default(false);
            $table->decimal('pf_employee_percent', 5, 2)->default(10);
            $table->decimal('pf_employer_percent', 5, 2)->default(10);
            $table->unsignedSmallInteger('pf_eligible_after_months')->default(0);
            $table->unsignedTinyInteger('pf_employer_vesting_years')->default(3);
            $table->boolean('tax_enabled')->default(true);
            $table->decimal('festival_bonus_percent', 6, 2)->default(100);
            $table->unsignedSmallInteger('festival_bonus_min_months')->default(12);
            $table->timestamps();
        });

        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->enum('type', ['earning', 'deduction'])->default('earning');
            $table->enum('calculation', ['percent_of_gross', 'percent_of_basic', 'fixed'])->default('percent_of_gross');
            $table->decimal('value', 12, 2)->default(0);
            $table->boolean('is_basic')->default(false);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('employee_salary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->unique(['employee_id', 'salary_component_id']);
        });

        Schema::create('salary_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('effective_from');
            $table->decimal('previous_salary', 12, 2)->default(0);
            $table->decimal('new_salary', 12, 2);
            $table->enum('type', ['joining', 'increment', 'promotion', 'adjustment', 'decrement'])->default('increment');
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['employee_id', 'effective_from']);
        });

        Schema::create('tax_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 20);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->decimal('threshold_general', 14, 2);
            $table->decimal('threshold_female_senior', 14, 2);
            $table->decimal('threshold_disabled', 14, 2);
            $table->decimal('threshold_freedom_fighter', 14, 2);
            $table->decimal('exemption_percent', 5, 2)->default(33.33);
            $table->decimal('exemption_cap', 14, 2)->default(0);
            $table->decimal('minimum_tax', 10, 2)->default(0);
            $table->json('slabs');
            $table->timestamps();
            $table->index(['company_id', 'starts_on']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('tax_category', 20)->nullable()->after('tin');
        });

        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['advance', 'loan'])->default('advance');
            $table->decimal('amount', 12, 2);
            $table->decimal('installment', 12, 2);
            $table->date('issued_on');
            $table->date('deduct_from');
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['employee_id', 'status']);
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['salary', 'bonus'])->default('salary');
            $table->string('title')->nullable();
            $table->date('month');
            $table->date('pay_date')->nullable();
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->unsignedInteger('employees_count')->default(0);
            $table->decimal('earnings_total', 14, 2)->default(0);
            $table->decimal('deductions_total', 14, 2)->default(0);
            $table->decimal('net_total', 14, 2)->default(0);
            $table->decimal('paid_total', 14, 2)->default(0);
            $table->string('note')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['shop_id', 'month']);
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->string('employee_code', 30)->nullable();
            $table->string('employee_name');
            $table->string('designation')->nullable();
            $table->decimal('salary', 12, 2)->default(0);
            $table->decimal('basic', 12, 2)->default(0);
            $table->unsignedTinyInteger('days_in_month')->default(30);
            $table->unsignedTinyInteger('payable_days')->default(30);
            $table->unsignedTinyInteger('present_days')->default(0);
            $table->unsignedTinyInteger('absent_days')->default(0);
            $table->unsignedTinyInteger('paid_leave_days')->default(0);
            $table->unsignedTinyInteger('unpaid_leave_days')->default(0);
            $table->unsignedTinyInteger('late_days')->default(0);
            $table->unsignedInteger('overtime_minutes')->default(0);
            $table->decimal('other_addition', 12, 2)->default(0);
            $table->decimal('other_deduction', 12, 2)->default(0);
            $table->string('adjustment_note')->nullable();
            $table->decimal('pf_employee', 12, 2)->default(0);
            $table->decimal('pf_employer', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('earnings_total', 12, 2)->default(0);
            $table->decimal('deductions_total', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['payroll_run_id', 'employee_id']);
        });

        Schema::create('payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['earning', 'deduction']);
            $table->string('code', 20);
            $table->string('name');
            $table->decimal('amount', 12, 2);
            $table->foreignId('employee_loan_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::create('final_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->enum('separation_type', ['resignation', 'termination', 'dismissal', 'retirement', 'death', 'contract_end']);
            $table->date('separation_date');
            $table->unsignedInteger('service_days')->default(0);
            $table->decimal('last_salary', 12, 2)->default(0);
            $table->decimal('last_basic', 12, 2)->default(0);
            $table->decimal('pf_forfeited', 12, 2)->default(0);
            $table->decimal('earnings_total', 12, 2)->default(0);
            $table->decimal('deductions_total', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->string('note')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('final_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('final_settlement_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['earning', 'deduction']);
            $table->string('code', 20);
            $table->string('name');
            $table->decimal('amount', 12, 2);
            $table->foreignId('employee_loan_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::create('employee_loan_recoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('recovered_on');
            $table->nullableMorphs('source');
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payroll_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->morphs('payable');
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('paid_on');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['payroll_payments', 'employee_loan_recoveries', 'final_settlement_items', 'final_settlements', 'payslip_items', 'payslips', 'payroll_runs', 'employee_loans'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('tax_category');
        });

        foreach (['tax_years', 'salary_revisions', 'employee_salary_items', 'salary_components', 'payroll_settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
