<?php

namespace Modules\Payroll\Providers;

use Illuminate\Support\Facades\Auth;
use Modules\Employee\Models\Employee;
use Modules\Payroll\Models\SalaryRevision;
use Modules\Payroll\Services\SalaryStructure;
use Nwidart\Modules\Support\ModuleServiceProvider;

class PayrollServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Payroll';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'payroll';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        // A salary changed on the employee form is kept in the salary
        // history, so payroll for earlier months still uses the old one.
        Employee::updated(function (Employee $employee) {
            if (! $employee->wasChanged('salary')) {
                return;
            }

            $structure = app(SalaryStructure::class);
            $newSalary = (float) $employee->salary;

            if (abs($structure->salaryOn($employee, now()) - $newSalary) < 0.005) {
                return;
            }

            SalaryRevision::withoutGlobalScopes()->create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'effective_from' => now()->toDateString(),
                'previous_salary' => (float) $employee->getOriginal('salary'),
                'new_salary' => $newSalary,
                'type' => $newSalary > (float) $employee->getOriginal('salary') ? 'increment' : 'adjustment',
                'created_by' => Auth::id(),
            ]);
        });
    }
}
