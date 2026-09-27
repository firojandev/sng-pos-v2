<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Http\Requests\StoreLeaveRequestRequest;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\LeaveRequest;
use Modules\Employee\Models\LeaveType;
use Modules\Employee\Services\HrSetup;
use Modules\Employee\Services\LeaveService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Leave requests of the shop's staff: apply, approve, reject, cancel.
 */
class LeaveRequestController extends Controller
{
    public function __construct(private LeaveService $leave) {}

    public function index(Request $request, HrSetup $setup): View
    {
        $setup->ensureFor((int) app(TenantContext::class)->companyId());
        $employees = Employee::workingAtShop()->where('status', 'active')->orderBy('name')->get();

        $requests = LeaveRequest::query()
            ->whereIn('employee_id', Employee::workingAtShop()->pluck('id'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->with(['employee:id,name,employee_code', 'leaveType:id,name,code', 'decider:id,name'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('employee::leave.index', [
            'requests' => $requests,
            'employees' => $employees,
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('code')->get(),
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $employee = Employee::findOrFail($request->validated('employee_id'));
        $this->leave->apply($employee, $request->validated(), $request->file('attachment'));

        return redirect()->route('leave-requests.index')->with('status', 'ছুটির আবেদন জমা হয়েছে');
    }

    /**
     * The supporting document of an application: for HR, or the employee
     * who applied.
     */
    public function attachment(LeaveRequest $leaveRequest): StreamedResponse
    {
        $user = auth()->user();
        $isOwn = (int) Employee::withoutGlobalScopes()->whereKey($leaveRequest->employee_id)->value('user_id') === (int) $user->id;

        abort_unless($leaveRequest->attachment_path && ($isOwn || $user->isShopAdmin() || $user->can('leave.view')), 404);

        return Storage::disk(LeaveRequest::ATTACHMENT_DISK)->download($leaveRequest->attachment_path, $leaveRequest->attachment_name);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->leave->approve($leaveRequest, $request->input('note'));

        return back()->with('status', 'ছুটি অনুমোদন করা হয়েছে');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->leave->reject($leaveRequest, $request->input('note'));

        return back()->with('status', 'ছুটির আবেদন বাতিল করা হয়েছে');
    }

    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->leave->cancel($leaveRequest);

        return back()->with('status', 'ছুটি বাতিল করা হয়েছে');
    }
}
