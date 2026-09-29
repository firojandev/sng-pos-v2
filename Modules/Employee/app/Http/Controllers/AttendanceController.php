<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Http\Requests\SaveAttendanceSheetRequest;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\Employee\Services\AttendanceService;
use Modules\Employee\Services\HrSetup;

/**
 * The daily attendance sheet of the shop's staff and the monthly summary.
 */
class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance) {}

    public function sheet(Request $request, HrSetup $setup): View
    {
        $setup->ensureFor((int) app(TenantContext::class)->companyId());
        $date = $request->filled('date') ? Carbon::parse($request->input('date')) : now();

        $employees = Employee::workingAtShop()->where('status', 'active')->orderBy('name')->get();
        $records = Attendance::whereIn('employee_id', $employees->pluck('id'))->whereDate('date', $date->toDateString())->get()->keyBy('employee_id');

        $clockStates = $date->isToday()
            ? $employees->mapWithKeys(fn (Employee $employee) => [$employee->id => $this->attendance->clockState($employee)['state']])->all()
            : [];

        return view('employee::attendance.sheet', compact('date', 'employees', 'records', 'clockStates'));
    }

    public function save(SaveAttendanceSheetRequest $request): RedirectResponse
    {
        $date = Carbon::parse($request->validated('date'));

        foreach ($request->validated('rows') as $row) {
            $hasEntry = ! empty($row['check_in']) || ! empty($row['check_out']) || ! empty($row['status']);

            if (! $hasEntry) {
                continue;
            }

            $this->attendance->recordManual(
                Employee::findOrFail($row['employee_id']),
                $date,
                $row['check_in'] ?? null,
                $row['check_out'] ?? null,
                $row['status'] ?? null,
                $row['note'] ?? null,
            );
        }

        return redirect()->route('attendance.sheet', ['date' => $date->toDateString()])->with('status', 'হাজিরা সংরক্ষণ করা হয়েছে');
    }

    /**
     * Clock an employee in or out now (from the daily sheet).
     */
    public function clock(Request $request, Employee $employee, string $direction): RedirectResponse
    {
        $this->attendance->clock($employee, $direction, $request->ip());

        return redirect()->route('attendance.sheet')->with('status', $employee->name.' — '.($direction === 'in' ? 'ক্লক ইন' : 'ক্লক আউট').' '.now()->format('h:i A'));
    }

    /**
     * Fill in the days without an entry (absent, weekend, holiday or leave).
     */
    public function process(Request $request): RedirectResponse
    {
        $validated = $request->validate(['date' => ['required', 'date', 'before_or_equal:today']]);
        $date = Carbon::parse($validated['date']);

        $this->attendance->processRange((int) app(TenantContext::class)->companyId(), $date, $date, app(TenantContext::class)->shopId());

        return back()->with('status', 'বাকি হাজিরা হিসাব করা হয়েছে');
    }

    public function monthly(Request $request): View
    {
        $month = $request->filled('month') ? Carbon::parse($request->input('month').'-01') : now()->startOfMonth();

        $employees = Employee::workingAtShop()->where('status', 'active')->orderBy('name')->get();
        $summary = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->get()
            ->groupBy('employee_id')
            ->map(fn ($days) => [
                'counts' => $days->countBy('status'),
                'late_minutes' => $days->sum('late_minutes'),
                'overtime_minutes' => $days->sum('overtime_minutes'),
            ]);

        return view('employee::attendance.monthly', compact('month', 'employees', 'summary'));
    }
}
