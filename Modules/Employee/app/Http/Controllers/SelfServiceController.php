<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Services\AttendanceService;
use Modules\Employee\Services\LeaveService;
use Modules\Employee\Services\SelfService;

/**
 * What a user linked to an employee does for themself: clock in and out,
 * and apply for (or withdraw) leave.
 */
class SelfServiceController extends Controller
{
    public function __construct(private SelfService $selfService) {}

    /**
     * My Leave: the user's leave balance by type, their applications with
     * their status, and a new application.
     */
    public function leave(): View
    {
        $data = $this->selfService->dashboard(Auth::user());
        abort_unless($data, 403, 'আপনার ইউজার কোনো কর্মচারীর সাথে যুক্ত নয় (Your user is not linked to an employee)।');

        $data['leaveRequests'] = LeaveRequest::withoutGlobalScopes()
            ->where('employee_id', $data['employee']->id)
            ->with(['leaveType' => fn ($query) => $query->withoutGlobalScopes(), 'decider:id,name'])
            ->latest('from_date')
            ->latest('id')
            ->get();

        return view('employee::self-service.leave', ['selfService' => $data]);
    }

    public function clock(Request $request, AttendanceService $attendance): RedirectResponse
    {
        $validated = $request->validate(['direction' => ['required', Rule::in(['in', 'out'])]]);

        $attendance->clock($this->employee(), $validated['direction'], $request->ip());

        return back()->with('status', $validated['direction'] === 'in' ? 'ক্লক ইন হয়েছে '.now()->format('h:i A') : 'ক্লক আউট হয়েছে '.now()->format('h:i A'));
    }

    public function applyLeave(Request $request, LeaveService $leave): RedirectResponse
    {
        $validated = $request->validate([
            'leave_type_id' => ['required', 'integer'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:255'],
            'attachment' => ['nullable', 'file', 'max:5120', 'extensions:pdf,jpg,jpeg,png,webp,doc,docx'],
        ]);

        $leave->apply($this->employee(), $validated, $request->file('attachment'), selfApplied: true);

        return back()->with('status', 'ছুটির আবেদন জমা হয়েছে');
    }

    public function cancelLeave(LeaveRequest $leaveRequest): RedirectResponse
    {
        abort_unless((int) $leaveRequest->employee_id === (int) $this->employee()->id, 403);

        if ($leaveRequest->status !== 'pending') {
            return back()->withErrors(['leave' => 'শুধু অপেক্ষমাণ আবেদন প্রত্যাহার করা যায় (Only pending applications can be withdrawn)।']);
        }

        $leaveRequest->update(['status' => 'cancelled', 'decided_by' => Auth::id(), 'decided_at' => now(), 'decision_note' => 'Withdrawn by the employee']);

        return back()->with('status', 'ছুটির আবেদন প্রত্যাহার করা হয়েছে');
    }

    private function employee(): Employee
    {
        $employee = $this->selfService->employeeFor(Auth::user());
        abort_unless($employee, 403, 'আপনার ইউজার কোনো কর্মচারীর সাথে যুক্ত নয় (Your user is not linked to an employee)।');

        return $employee;
    }
}
