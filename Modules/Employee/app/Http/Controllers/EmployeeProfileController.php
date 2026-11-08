<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Http\Requests\UpdateEmployeeProfileRequest;
use Modules\Employee\Models\Department;
use Modules\Employee\Models\Designation;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveType;
use Modules\Employee\Models\Shift;
use Modules\Employee\Services\HrSetup;
use Modules\Employee\Services\LeaveService;
use Modules\Shop\Models\Shop;

/**
 * An employee's full HR profile, with their leave balances.
 */
class EmployeeProfileController extends Controller
{
    public function edit(Employee $employee, HrSetup $setup, LeaveService $leave): View
    {
        $companyId = (int) app(TenantContext::class)->companyId();
        $setup->ensureFor($companyId);

        return view('employee::profile', [
            'employee' => $employee,
            'shops' => Shop::where('company_id', $companyId)->orderBy('name')->pluck('name', 'id'),
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
            'designations' => Designation::orderBy('name')->pluck('name', 'id'),
            'shifts' => Shift::orderBy('name')->pluck('name', 'id'),
            'users' => $this->linkableUsers($companyId, $employee)->pluck('name', 'id'),
            'balances' => LeaveType::where('is_active', true)->orderBy('code')->get()
                ->map(fn (LeaveType $type) => ['type' => $type, ...$leave->balance($employee, $type)]),
        ]);
    }

    public function update(UpdateEmployeeProfileRequest $request, Employee $employee): RedirectResponse
    {
        abort_if($employee->isRecordOf(auth()->user()), 403, 'নিজের কর্মচারী রেকর্ড নিজে পরিবর্তন বা মুছে ফেলা যায় না (You can\'t change or delete your own employee record)।');

        $employee->update($request->validated());

        return redirect()->route('employees.profile.edit', $employee)->with('status', 'কর্মচারীর তথ্য হালনাগাদ করা হয়েছে');
    }

    /**
     * Login users of the company's shops that aren't linked to another
     * employee of the company.
     *
     * @return Collection<int, User>
     */
    public static function linkableUsers(int $companyId, Employee $employee): Collection
    {
        $shopIds = Shop::where('company_id', $companyId)->pluck('id');
        $linkedElsewhere = Employee::withoutGlobalScopes()->where('company_id', $companyId)->whereKeyNot($employee->id)->whereNotNull('user_id')->pluck('user_id');

        return User::query()
            ->where(fn ($query) => $query->whereIn('shop_id', $shopIds)->orWhereHas('shops', fn ($shops) => $shops->whereIn('shops.id', $shopIds)))
            ->whereNotIn('id', $linkedElsewhere)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
