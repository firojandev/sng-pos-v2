<?php

namespace Modules\Payroll\Services;

use Illuminate\Support\Carbon;
use Modules\Payroll\Models\PayrollSetting;
use Modules\Payroll\Models\SalaryComponent;
use Modules\Payroll\Models\TaxYear;

/**
 * A company's payroll defaults (all editable): the settings, the usual
 * Bangladesh salary structure and the income tax years (seeded from the
 * payroll config, then kept and edited in the database).
 */
class PayrollSetup
{
    /**
     * [code, name, calculation, value, is_basic]
     *
     * @var list<array{0: string, 1: string, 2: string, 3: float, 4: bool}>
     */
    private const COMPONENTS = [
        ['BASIC', 'মূল বেতন (Basic)', 'percent_of_gross', 60, true],
        ['HRA', 'বাড়ি ভাড়া (House Rent)', 'percent_of_gross', 30, false],
        ['MED', 'চিকিৎসা ভাতা (Medical)', 'percent_of_gross', 5, false],
        ['CONV', 'যাতায়াত ভাতা (Conveyance)', 'percent_of_gross', 5, false],
    ];

    public function ensureFor(int $companyId): PayrollSetting
    {
        $settings = PayrollSetting::forCompany($companyId);

        if (! SalaryComponent::withoutGlobalScopes()->where('company_id', $companyId)->exists()) {
            foreach (self::COMPONENTS as $index => [$code, $name, $calculation, $value, $isBasic]) {
                SalaryComponent::withoutGlobalScopes()->create([
                    'company_id' => $companyId,
                    'code' => $code,
                    'name' => $name,
                    'type' => 'earning',
                    'calculation' => $calculation,
                    'value' => $value,
                    'is_basic' => $isBasic,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        if (! TaxYear::withoutGlobalScopes()->where('company_id', $companyId)->exists()) {
            foreach (config('payroll.tax_years', []) as $name => $rules) {
                $startYear = (int) substr((string) $name, 0, 4);

                TaxYear::withoutGlobalScopes()->create([
                    'company_id' => $companyId,
                    'name' => $name,
                    'starts_on' => Carbon::create($startYear, 7, 1)->toDateString(),
                    'ends_on' => Carbon::create($startYear + 1, 6, 30)->toDateString(),
                    ...$rules,
                ]);
            }
        }

        return $settings;
    }

    /**
     * The income year a date falls in: July to June.
     */
    public static function incomeYearName(Carbon $date): string
    {
        $startYear = $date->month >= 7 ? $date->year : $date->year - 1;

        return $startYear.'-'.substr((string) ($startYear + 1), 2);
    }
}
