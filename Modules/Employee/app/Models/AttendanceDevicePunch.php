<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * A punch as received from a device or an imported file.
 */
class AttendanceDevicePunch extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'shop_id', 'attendance_device_id', 'device_user_id', 'punched_at',
        'source', 'employee_id', 'attendance_log_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['punched_at' => 'datetime'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(AttendanceDevice::class, 'attendance_device_id');
    }
}
