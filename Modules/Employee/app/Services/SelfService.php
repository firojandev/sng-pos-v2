<?php

namespace Modules\Employee\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
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
     * A company-level user (owner, admin, employee) always has one: it is
     * created (or found by phone and linked) the first time it's needed.
     */
    public function employeeFor(?User $user): ?Employee
    {
        $companyId = app(TenantContext::class)->companyId();

        if (! $user || ! $companyId) {
            return null;
        }

        $employee = Employee::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $employee && ! $user->shop_id && (int) $user->companyLevelCompany()?->id === (int) $companyId) {
            $employee = $this->ensureEmployeeRecord($user, $companyId);
        }

        return $employee;
    }

    /**
     * Link the company user to the company's employee with their phone, or
     * create their employee record (no shop: they work at company level).
     */
    public function ensureEmployeeRecord(User $user, int $companyId): Employee
    {
        $existing = Employee::withoutGlobalScopes()->where('company_id', $companyId)->where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        $byPhone = $user->phone ? Employee::withoutGlobalScopes()->where('company_id', $companyId)->where('phone', $user->phone)->whereNull('user_id')->first() : null;
        if ($byPhone) {
            $byPhone->forceFill(['user_id' => $user->id])->save();

            return $byPhone;
        }

        $role = DB::table('company_user')->where('company_id', $companyId)->where('user_id', $user->id)->first(['role', 'is_owner']);
        $phoneTaken = ! $user->phone || Employee::withoutGlobalScopes()->where('phone', $user->phone)->exists();
        $emailTaken = ! $user->email || Employee::withoutGlobalScopes()->where('email', $user->email)->exists();

        return Employee::withoutGlobalScopes()->create([
            'company_id' => $companyId,
            'shop_id' => null,
            'user_id' => $user->id,
            'name' => $user->name,
            'phone' => $phoneTaken ? 'U-'.$user->id : $user->phone,
            'email' => $emailTaken ? null : $user->email,
            'designation' => match (true) {
                (bool) $role?->is_owner => 'মালিক (Owner)',
                $role?->role === 'Admin' => 'কোম্পানি এডমিন (Company Admin)',
                default => 'কোম্পানি কর্মচারী (Company Employee)',
            },
            'salary' => 0,
            'status' => 'active',
            'joining_date' => now()->toDateString(),
        ]);
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
