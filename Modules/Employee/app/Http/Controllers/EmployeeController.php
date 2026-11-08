<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Employee\DataTables\EmployeesDataTable;
use Modules\Employee\Http\Requests\StoreEmployeeRequest;
use Modules\Employee\Http\Requests\UpdateEmployeeRequest;
use Modules\Employee\Models\Department;
use Modules\Employee\Models\Designation;
use Modules\Employee\Models\Employee;
use Modules\Employee\Services\EmployeeLogin;

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

        $nextEmployeeCode = $this->nextEmployeeCode();

        return $dataTable->render('employee::index', [...compact('metrics', 'departments', 'designations', 'users', 'nextEmployeeCode'), ...$this->formOptions()]);
    }

    public function create(): View
    {
        $shopId = auth()->user()->shop_id;
        $users = $this->linkableUsers();

        return view('employee::create', [
            'employee' => new Employee(['employee_code' => $this->nextEmployeeCode()]),
            'users' => $users,
            ...$this->formOptions(),
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->employeeData();
        $data['shop_id'] = auth()->user()->shop_id;

        [$employee, $loginNote] = $this->saveWithLogin($request, fn () => Employee::create($data));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'কর্মচারী সফলভাবে যোগ করা হয়েছে'.$loginNote,
                'employee' => $employee->load('user'),
                'next_employee_code' => $this->nextEmployeeCode(),
            ]);
        }

        return redirect()
            ->route('employees.index')
            ->with('status', 'কর্মচারী সফলভাবে যোগ করা হয়েছে'.$loginNote);
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
                    'employee_code' => $employee->employee_code,
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
                    'designation_id' => $employee->designation_id,
                    'department_id' => $employee->department_id,
                ],
                'users' => $users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email]),
                'update_url' => route('employees.update', $employee),
            ]);
        }

        return view('employee::edit', [...compact('employee', 'users'), ...$this->formOptions()]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse|JsonResponse
    {
        $this->ensureNotOwnRecord($employee);

        [$employee, $loginNote] = $this->saveWithLogin($request, fn () => tap($employee)->update($request->employeeData()));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'কর্মচারীর তথ্য সফলভাবে হালনাগাদ করা হয়েছে'.$loginNote,
                'employee' => $employee->load('user'),
            ]);
        }

        return redirect()
            ->route('employees.index')
            ->with('status', 'কর্মচারীর তথ্য হালনাগাদ করা হয়েছে'.$loginNote);
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
     * Save the employee and, when asked, create their login with it. The
     * set-password email goes out once both are saved.
     *
     * @param  callable(): Employee  $save
     * @return array{0: Employee, 1: string} the employee and a note about the login for the status message
     */
    private function saveWithLogin(StoreEmployeeRequest|UpdateEmployeeRequest $request, callable $save): array
    {
        if (! $request->boolean('create_user')) {
            return [$save(), ''];
        }

        $login = app(EmployeeLogin::class);
        $password = $request->validated('user_password');
        $roleId = $request->validated('user_role') ? (int) $request->validated('user_role') : null;

        [$employee, $user] = DB::transaction(function () use ($save, $login, $roleId, $password) {
            $employee = $save();

            return [$employee, $login->create($employee, $roleId, $password)];
        });

        if ($password) {
            return [$employee, '; লগইন অ্যাকাউন্ট তৈরি হয়েছে (login account created)'];
        }

        return [$employee, $login->invite($user)
            ? "; পাসওয়ার্ড সেট করার লিংক {$user->email}-এ পাঠানো হয়েছে (set-password link emailed)"
            : '; লগইন তৈরি হয়েছে কিন্তু ইমেইল পাঠানো যায়নি (login created, but the email could not be sent)'];
    }

    /**
     * The dropdowns of the add/edit forms, and whether the user may create
     * a login with the employee.
     *
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        $login = app(EmployeeLogin::class);
        $canCreateLogin = $login->allowed();

        return [
            'designationOptions' => Designation::orderBy('name')->pluck('name', 'id')->all(),
            'departmentOptions' => Department::orderBy('name')->pluck('name', 'id')->all(),
            'canCreateLogin' => $canCreateLogin,
            'loginRoleOptions' => $canCreateLogin ? $login->roleOptions() : [],
            'loginRoleRequired' => $login->roleRequired(),
        ];
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

    /**
     * The code a new employee gets unless another is entered.
     */
    private function nextEmployeeCode(): ?string
    {
        $companyId = app(TenantContext::class)->companyId();

        return $companyId ? Employee::nextCode($companyId) : null;
    }

    private function ensureNotOwnRecord(Employee $employee): void
    {
        abort_if($employee->isRecordOf(auth()->user()), 403, 'নিজের কর্মচারী রেকর্ড নিজে পরিবর্তন বা মুছে ফেলা যায় না (You can\'t change or delete your own employee record)।');
    }
}
