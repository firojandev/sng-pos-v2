<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * One raw punch, entered by hand, received from a device or imported.
 */
class AttendanceLog extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'employee_id', 'shop_id', 'punched_at', 'shift_date', 'source', 'device_serial', 'ip_address', 'note', 'created_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['punched_at' => 'datetime', 'shift_date' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
