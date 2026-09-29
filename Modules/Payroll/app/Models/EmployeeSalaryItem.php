<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An employee's own amount for a salary component, used instead of the
 * company's standard calculation.
 */
class EmployeeSalaryItem extends Model
{
    protected $fillable = ['employee_id', 'salary_component_id', 'amount'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['amount' => 'float'];
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }
}
