<?php

namespace Modules\Employee\Services;

use App\Models\User;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Models\LeaveType;

/**
 * Employee self-service: what a user linked to an employee sees on their
 * dashboard (clock in/out, today's attendance, leave balances and their
 * own leave applications).
 */
class SelfService
{
    public function __construct(private AttendanceService $attendance, private LeaveService $leave, private HrSetup $setup) {}

    /**
     * The active employee record linked to the user in the current company.
     */
    public function employeeFor(?User $user): ?Employee
    {
        $companyId = app(TenantContext::class)->companyId();

        if (! $user || ! $companyId) {
            return null;
        }

        return Employee::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function dashboard(?User $user): ?array
    {
        $employee = $this->employeeFor($user);

        if (! $employee) {
            return null;
        }

        $this->setup->ensureFor($employee->company_id);
        $clock = $this->attendance->clockState($employee);
        $leaveTypes = LeaveType::withoutGlobalScopes()
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('gender')->orWhere('gender', $employee->gender))
            ->orderBy('code')
            ->get();

        return [
            'employee' => $employee,
            'clock' => $clock,
            'today' => Attendance::withoutGlobalScopes()->where('employee_id', $employee->id)->whereDate('date', $clock['shift_date']->toDateString())->first(),
            'monthPresent' => Attendance::withoutGlobalScopes()->where('employee_id', $employee->id)
                ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->toDateString().' 23:59:59'])
                ->whereIn('status', ['present', 'late', 'half_day'])->count(),
            'leaveTypes' => $leaveTypes,
            'balances' => $leaveTypes->map(fn (LeaveType $type) => ['type' => $type, ...$this->leave->balance($employee, $type)]),
            'leaveRequests' => LeaveRequest::withoutGlobalScopes()->where('employee_id', $employee->id)->with(['leaveType' => fn ($query) => $query->withoutGlobalScopes()])->latest('from_date')->latest('id')->limit(10)->get(),
        ];
    }
}
