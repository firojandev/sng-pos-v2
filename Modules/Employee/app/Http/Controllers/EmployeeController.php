<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Employee\DataTables\EmployeesDataTable;
use Modules\Employee\Http\Requests\StoreEmployeeRequest;
use Modules\Employee\Http\Requests\UpdateEmployeeRequest;
use Modules\Employee\Models\Employee;

class EmployeeController extends Controller
{
    public function index(EmployeesDataTable $dataTable): mixed
    {
        $shopId = auth()->user()->shop_id;

        $totalEmployees = Employee::workingAtShop()->count();
        $activeEmployees = Employee::workingAtShop()->where('status', 'active')->count();
        $totalSalary = (float) Employee::workingAtShop()->where('status', 'active')->sum('salary');
        $departmentsCount = Employee::workingAtShop()->whereNotNull('department')->where('department', '!=', '')->distinct()->count('department');

        $metrics = [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'totalSalary' => $totalSalary,
            'departmentsCount' => $departmentsCount,
        ];

        $departments = Employee::workingAtShop()
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $designations = Employee::workingAtShop()
            ->whereNotNull('designation')
            ->where('designation', '!=', '')
            ->distinct()
            ->orderBy('designation')
            ->pluck('designation');

        $users = $this->linkableUsers();

        return $dataTable->render('employee::index', compact('metrics', 'departments', 'designations', 'users'));
    }

    public function create(): View
    {
        $shopId = auth()->user()->shop_id;
        $users = $this->linkableUsers();

        return view('employee::create', [
            'employee' => new Employee,
            'users' => $users,
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['shop_id'] = auth()->user()->shop_id;

        $employee = Employee::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'কর্মচারী সফলভাবে যোগ করা হয়েছে',
                'employee' => $employee->load('user'),
            ]);
        }

        return redirect()
            ->route('employees.index')
            ->with('status', 'কর্মচারী সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, Employee $employee): View|JsonResponse
    {
        $this->ensureNotOwnRecord($employee);

        $shopId = auth()->user()->shop_id;
        $users = $this->linkableUsers();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'phone' => $employee->phone,
                    'email' => $employee->email,
                    'designation' => $employee->designation,
                    'department' => $employee->department,
                    'salary' => $employee->salary,
                    'joining_date' => optional($employee->joining_date)->format('Y-m-d'),
                    'address' => $employee->address,
                    'status' => $employee->status,
                    'user_id' => $employee->user_id,
                ],
                'users' => $users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name]),
                'update_url' => route('employees.update', $employee),
            ]);
        }

        return view('employee::edit', compact('employee', 'users'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse|JsonResponse
    {
        $this->ensureNotOwnRecord($employee);

        $employee->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'কর্মচারীর তথ্য সফলভাবে হালনাগাদ করা হয়েছে',
                'employee' => $employee->load('user'),
            ]);
        }

        return redirect()
            ->route('employees.index')
            ->with('status', 'কর্মচারীর তথ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, Employee $employee): RedirectResponse|JsonResponse
    {
        $this->ensureNotOwnRecord($employee);

        if ($employee->hasPayrollHistory()) {
            $message = 'এই কর্মচারীর বেতন/অগ্রিমের রেকর্ড আছে, মুছে ফেলা যাবে না; অবস্থা "নিষ্ক্রিয়" করুন (The employee has payroll records; set them inactive instead)।';

            return $request->ajax() || $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->withErrors(['employee' => $message]);
        }

        $employee->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'কর্মচারী সফলভাবে মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()
            ->route('employees.index')
            ->with('status', 'কর্মচারী মুছে ফেলা হয়েছে');
    }

    /**
     * Login users that can be linked to an employee: the shop's users, or in
     * the company workspace the company's users and its shops' users.
     *
     * @return Collection<int, User>
     */
    private function linkableUsers(): Collection
    {
        $shopId = auth()->user()->shop_id;

        if ($shopId) {
            return User::where('shop_id', $shopId)->orderBy('name')->get();
        }

        $companyId = app(TenantContext::class)->companyId();
        $shopIds = app(TenantContext::class)->visibleShopIds();

        return User::query()
            ->where(fn ($query) => $query->whereIn('shop_id', $shopIds ?: [0])->orWhereHas('companies', fn ($companies) => $companies->where('companies.id', $companyId ?? 0)))
            ->orderBy('name')
            ->get();
    }

    private function ensureNotOwnRecord(Employee $employee): void
    {
        abort_if($employee->isRecordOf(auth()->user()), 403, 'নিজের কর্মচারী রেকর্ড নিজে পরিবর্তন বা মুছে ফেলা যায় না (You can\'t change or delete your own employee record)।');
    }
}
