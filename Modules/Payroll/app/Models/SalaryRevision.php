<?php

namespace Modules\Payroll\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Core\Observers\AuditObserver;
use Modules\Employee\Models\Designation;
use Modules\Employee\Models\Employee;

/**
 * A change of an employee's gross salary (joining, increment, promotion...).
 */
class SalaryRevision extends Model
{
    use BelongsToCompany;

    public const TYPES = ['joining', 'increment', 'promotion', 'adjustment', 'decrement'];

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = ['company_id', 'employee_id', 'effective_from', 'previous_salary', 'new_salary', 'type', 'designation_id', 'previous_designation_id', 'note', 'created_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['effective_from' => 'date', 'previous_salary' => 'decimal:2', 'new_salary' => 'decimal:2'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function previousDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'previous_designation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
