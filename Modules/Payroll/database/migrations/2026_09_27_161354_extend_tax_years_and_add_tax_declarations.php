<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tax years keep their thresholds per category and the minimum tax per
 * location as data, plus the investment rebate rules. Each employee gets a
 * yearly tax declaration (investments, rebate, tax deducted, year-end
 * adjustment), and each payslip records its taxable income.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tax_years', function (Blueprint $table) {
            $table->string('assessment_year', 20)->nullable()->after('name');
            $table->json('thresholds')->nullable()->after('ends_on');
            $table->json('minimum_taxes')->nullable()->after('exemption_cap');
            $table->decimal('rebate_income_percent', 5, 2)->default(3)->after('minimum_taxes');
            $table->decimal('rebate_investment_percent', 5, 2)->default(15)->after('rebate_income_percent');
            $table->decimal('rebate_cap', 14, 2)->default(1000000)->after('rebate_investment_percent');
        });

        foreach (DB::table('tax_years')->get() as $year) {
            $startYear = (int) substr((string) $year->name, 0, 4);

            DB::table('tax_years')->where('id', $year->id)->update([
                'assessment_year' => $startYear ? ($startYear + 1).'-'.substr((string) ($startYear + 2), 2) : null,
                'thresholds' => json_encode([
                    'general' => (float) $year->threshold_general,
                    'female_senior' => (float) $year->threshold_female_senior,
                    'disabled' => (float) $year->threshold_disabled,
                    'freedom_fighter' => (float) $year->threshold_freedom_fighter,
                    'july_fighter' => (float) $year->threshold_freedom_fighter,
                ]),
                'minimum_taxes' => json_encode(['dhaka_chattogram' => (float) $year->minimum_tax, 'other_city' => (float) $year->minimum_tax, 'elsewhere' => (float) $year->minimum_tax]),
            ]);
        }

        Schema::table('tax_years', function (Blueprint $table) {
            $table->dropColumn(['threshold_general', 'threshold_female_senior', 'threshold_disabled', 'threshold_freedom_fighter', 'minimum_tax']);
        });

        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->string('default_tax_location', 30)->default('dhaka_chattogram')->after('tax_enabled');
            $table->unsignedTinyInteger('festival_bonuses_per_year')->default(2)->after('festival_bonus_min_months');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('tax_location', 30)->nullable()->after('tax_category');
        });

        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('taxable_income', 12, 2)->default(0)->after('tax');
        });
        DB::table('payslips')->update(['taxable_income' => DB::raw('earnings_total')]);

        Schema::create('employee_tax_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_year_id')->constrained()->cascadeOnDelete();
            $table->decimal('declared_investment', 14, 2)->default(0);
            $table->decimal('eligible_investment', 14, 2)->default(0);
            $table->decimal('calculated_rebate', 14, 2)->default(0);
            $table->decimal('taxable_income', 14, 2)->default(0);
            $table->decimal('annual_tax', 14, 2)->default(0);
            $table->decimal('tax_deducted', 14, 2)->default(0);
            $table->decimal('year_end_adjustment', 14, 2)->nullable();
            $table->string('note')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['employee_id', 'tax_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_tax_declarations');

        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn('taxable_income');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('tax_location');
        });
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn(['default_tax_location', 'festival_bonuses_per_year']);
        });

        Schema::table('tax_years', function (Blueprint $table) {
            $table->decimal('threshold_general', 14, 2)->default(0);
            $table->decimal('threshold_female_senior', 14, 2)->default(0);
            $table->decimal('threshold_disabled', 14, 2)->default(0);
            $table->decimal('threshold_freedom_fighter', 14, 2)->default(0);
            $table->decimal('minimum_tax', 10, 2)->default(0);
        });

        foreach (DB::table('tax_years')->get() as $year) {
            $thresholds = json_decode((string) $year->thresholds, true) ?: [];
            $minimum = json_decode((string) $year->minimum_taxes, true) ?: [];

            DB::table('tax_years')->where('id', $year->id)->update([
                'threshold_general' => $thresholds['general'] ?? 0,
                'threshold_female_senior' => $thresholds['female_senior'] ?? 0,
                'threshold_disabled' => $thresholds['disabled'] ?? 0,
                'threshold_freedom_fighter' => $thresholds['freedom_fighter'] ?? 0,
                'minimum_tax' => $minimum['dhaka_chattogram'] ?? 0,
            ]);
        }

        Schema::table('tax_years', function (Blueprint $table) {
            $table->dropColumn(['assessment_year', 'thresholds', 'minimum_taxes', 'rebate_income_percent', 'rebate_investment_percent', 'rebate_cap']);
        });
    }
};
