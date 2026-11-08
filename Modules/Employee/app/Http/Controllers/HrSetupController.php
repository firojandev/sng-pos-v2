<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Models\Department;
use Modules\Employee\Models\Designation;
use Modules\Employee\Models\Holiday;
use Modules\Employee\Models\LeaveType;
use Modules\Employee\Models\Shift;
use Modules\Employee\Services\HrSetup;

/**
 * Departments, designations, shifts, holidays and leave types.
 */
class HrSetupController extends Controller
{
    public function index(HrSetup $setup): View
    {
        $setup->ensureFor($this->companyId());

        return view('employee::hr-setup.index', [
            'departments' => Department::withCount('employees')->orderBy('name')->get(),
            'designations' => Designation::withCount('employees')->orderBy('name')->get(),
            'shifts' => Shift::orderByDesc('is_default')->orderBy('name')->get(),
            'holidays' => Holiday::whereYear('date', '>=', now()->year)->orderBy('date')->get(),
            'leaveTypes' => LeaveType::orderBy('code')->get(),
        ]);
    }

    public function storeNamed(Request $request, string $kind): RedirectResponse
    {
        $model = $this->namedModel($kind);
        $table = (new $model)->getTable();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique($table, 'name')->where('company_id', $this->companyId())],
        ]);

        $model::create(['company_id' => $this->companyId(), 'name' => $validated['name']]);

        return back()->with('status', 'সংরক্ষণ করা হয়েছে');
    }

    public function destroyNamed(string $kind, int $id): RedirectResponse
    {
        $record = $this->namedModel($kind)::findOrFail($id);

        abort_if($record->employees()->exists(), 422, 'কর্মচারী যুক্ত থাকায় মুছে ফেলা যাবে না (It is in use by employees)।');

        $record->delete();

        return back()->with('status', 'মুছে ফেলা হয়েছে');
    }

    public function storeShift(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'grace_minutes' => ['required', 'integer', 'between:0,180'],
            'weekend_days' => ['nullable', 'array'],
            'weekend_days.*' => ['integer', 'between:1,7'],
        ]);

        Shift::create([...$validated, 'company_id' => $this->companyId(), 'weekend_days' => array_map('intval', $validated['weekend_days'] ?? [])]);

        return back()->with('status', 'শিফট যোগ করা হয়েছে');
    }

    public function makeDefaultShift(Shift $shift): RedirectResponse
    {
        Shift::query()->update(['is_default' => false]);
        $shift->update(['is_default' => true]);

        return back()->with('status', 'ডিফল্ট শিফট নির্ধারণ করা হয়েছে');
    }

    public function destroyShift(Shift $shift): RedirectResponse
    {
        abort_if($shift->is_default, 422, 'ডিফল্ট শিফট মুছে ফেলা যাবে না (The default shift can’t be deleted)।');

        $shift->delete();

        return back()->with('status', 'শিফট মুছে ফেলা হয়েছে');
    }

    public function storeHoliday(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date', Rule::unique('holidays', 'date')->where('company_id', $this->companyId())],
            'to_date' => ['nullable', 'date', 'after_or_equal:date'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:festival,public,company'],
        ]);

        $day = Carbon::parse($validated['date']);
        $until = Carbon::parse($validated['to_date'] ?? $validated['date']);

        for (; $day->lte($until); $day->addDay()) {
            Holiday::firstOrCreate(
                ['company_id' => $this->companyId(), 'date' => $day->toDateString()],
                ['name' => $validated['name'], 'type' => $validated['type']],
            );
        }

        return back()->with('status', 'ছুটির দিন যোগ করা হয়েছে');
    }

    public function destroyHoliday(Holiday $holiday): RedirectResponse
    {
        $holiday->delete();

        return back()->with('status', 'ছুটির দিন মুছে ফেলা হয়েছে');
    }

    public function updateLeaveType(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $validated = $request->validate([
            'days_per_year' => ['nullable', 'integer', 'between:0,366'],
            'earn_one_day_per' => ['nullable', 'integer', 'between:1,365'],
            'is_paid' => ['boolean'],
            'is_active' => ['boolean'],
            'carry_forward' => ['boolean'],
            'max_carry_forward' => ['nullable', 'integer', 'between:0,999'],
            'carry_forward_expires' => ['boolean'],
            'carry_forward_expiry_months' => ['nullable', 'integer', 'between:1,120', 'required_if:carry_forward_expires,1'],
            'is_encashable' => ['boolean'],
        ]);

        $leaveType->update([
            ...$validated,
            'is_paid' => $request->boolean('is_paid'),
            'is_active' => $request->boolean('is_active'),
            'carry_forward' => $request->boolean('carry_forward'),
            'carry_forward_expires' => $request->boolean('carry_forward_expires'),
            'is_encashable' => $request->boolean('is_encashable'),
        ]);

        return back()->with('status', 'ছুটির ধরন হালনাগাদ করা হয়েছে');
    }

    /**
     * @return class-string<Department|Designation>
     */
    private function namedModel(string $kind): string
    {
        return match ($kind) {
            'departments' => Department::class,
            'designations' => Designation::class,
            default => abort(404),
        };
    }

    private function companyId(): int
    {
        return (int) app(TenantContext::class)->companyId();
    }
}
