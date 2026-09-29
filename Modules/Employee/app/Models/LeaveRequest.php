<?php

namespace Modules\Employee\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Concerns\BelongsToCompany;

class LeaveRequest extends Model
{
    use BelongsToCompany;

    public const ATTACHMENT_DISK = 'local';

    protected static function booted(): void
    {
        static::deleted(function (LeaveRequest $request) {
            if ($request->attachment_path) {
                Storage::disk(self::ATTACHMENT_DISK)->delete($request->attachment_path);
            }
        });
    }

    protected $fillable = [
        'company_id', 'employee_id', 'leave_type_id', 'from_date', 'to_date', 'days', 'reason', 'attachment_path', 'attachment_name', 'is_self_applied',
        'status', 'decided_by', 'decided_at', 'decision_note', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['from_date' => 'date', 'to_date' => 'date', 'decided_at' => 'datetime', 'days' => 'integer', 'is_self_applied' => 'boolean'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
