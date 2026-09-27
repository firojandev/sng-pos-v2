<?php

namespace Modules\Employee\Services;

use Modules\Employee\Models\LeaveType;
use Modules\Employee\Models\Shift;

/**
 * A company's HR defaults: a general shift and the leave types of the
 * Bangladesh Labour Act 2006 (all editable). Festival holidays are entered
 * in the holiday calendar.
 */
class HrSetup
{
    /**
     * [code, name, days per year, one day per N days worked, carry forward, max carried, encashable, gender]
     *
     * @var list<array{0: string, 1: string, 2: ?int, 3: ?int, 4: bool, 5: ?int, 6: bool, 7: ?string}>
     */
    private const LEAVE_TYPES = [
        ['CL', 'নৈমিত্তিক ছুটি (Casual Leave)', 10, null, false, null, false, null],
        ['SL', 'অসুস্থতা ছুটি (Sick Leave)', 14, null, false, null, false, null],
        ['EL', 'অর্জিত ছুটি (Earned Leave)', null, 18, true, 60, true, null],
        ['ML', 'মাতৃত্বকালীন ছুটি (Maternity Leave)', 112, null, false, null, false, 'female'],
    ];

    public function ensureFor(int $companyId): void
    {
        if (! Shift::withoutGlobalScopes()->where('company_id', $companyId)->exists()) {
            Shift::withoutGlobalScopes()->create([
                'company_id' => $companyId,
                'name' => 'সাধারণ (General)',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'grace_minutes' => 10,
                'weekend_days' => [5],
                'is_default' => true,
            ]);
        }

        foreach (self::LEAVE_TYPES as [$code, $name, $daysPerYear, $earnPer, $carryForward, $maxCarried, $encashable, $gender]) {
            LeaveType::withoutGlobalScopes()->firstOrCreate(
                ['company_id' => $companyId, 'code' => $code],
                [
                    'name' => $name,
                    'days_per_year' => $daysPerYear,
                    'earn_one_day_per' => $earnPer,
                    'carry_forward' => $carryForward,
                    'max_carry_forward' => $maxCarried,
                    'is_encashable' => $encashable,
                    'gender' => $gender,
                    'is_paid' => true,
                ],
            );
        }
    }
}
