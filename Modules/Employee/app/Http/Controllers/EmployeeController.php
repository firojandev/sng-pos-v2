<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Employee\DataTables\EmployeesDataTable;
use Modules\Employee\Http\Requests\StoreEmployeeRequest;
use Modules\Employee\Http\Requests\UpdateEmployeeRequest;
use Modules\Employee\Models\Employee;

class EmployeeController extends Controller
{
    public function index(EmployeesDataTable $dataTable): mixed
    {
        $shopId = auth()->user()->shop_id;

        $totalEmployees = Employee::where('shop_id', $shopId)->count();
        $activeEmployees = Employee::where('shop_id', $shopId)->where('status', 'active')->count();
        $totalSalary = (float) Employee::where('shop_id', $shopId)->where('status', 'active')->sum('salary');
        $departmentsCount = Employee::where('shop_id', $shopId)->whereNotNull('department')->where('department', '!=', '')->distinct()->count('department');

        $metrics = [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'totalSalary' => $totalSalary,
            'departmentsCount' => $departmentsCount,
        ];

        $departments = Employee::where('shop_id', $shopId)
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        $designations = Employee::where('shop_id', $shopId)
            ->whereNotNull('designation')
            ->where('designation', '!=', '')
            ->distinct()
            ->orderBy('designation')
            ->pluck('designation');

        $users = User::where('shop_id', $shopId)->orderBy('name')->get();

        return $dataTable->render('employee::index', compact('metrics', 'departments', 'designations', 'users'));
    }

    public function create(): View
    {
        $shopId = auth()->user()->shop_id;
        $users = User::where('shop_id', $shopId)->orderBy('name')->get();

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
        $shopId = auth()->user()->shop_id;
        $users = User::where('shop_id', $shopId)->orderBy('name')->get();

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
}
