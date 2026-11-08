<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * An employee's attendance for one day, worked out from punches, shift,
 * holidays and leave (or set by hand).
 */
class Attendance extends Model
{
    use BelongsToCompany;

    public const STATUSES = ['present', 'late', 'half_day', 'absent', 'leave', 'holiday', 'weekend'];

    protected $fillable = [
        'company_id', 'employee_id', 'shop_id', 'shift_id', 'date', 'check_in', 'check_out',
        'worked_minutes', 'late_minutes', 'early_leave_minutes', 'overtime_minutes', 'status', 'is_manual', 'note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime',
            'check_out' => 'datetime',
            'is_manual' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Bengali/English labels of the statuses.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function statusLabels(): array
    {
        return [
            'present' => ['bn' => 'উপস্থিত', 'en' => 'Present'],
            'late' => ['bn' => 'দেরিতে', 'en' => 'Late'],
            'half_day' => ['bn' => 'অর্ধদিবস', 'en' => 'Half Day'],
            'absent' => ['bn' => 'অনুপস্থিত', 'en' => 'Absent'],
            'leave' => ['bn' => 'ছুটি', 'en' => 'Leave'],
            'holiday' => ['bn' => 'সরকারি ছুটি', 'en' => 'Holiday'],
            'weekend' => ['bn' => 'সাপ্তাহিক ছুটি', 'en' => 'Weekend'],
        ];
    }
}
