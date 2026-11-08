<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Models\AttendanceDevice;
use Modules\Employee\Models\AttendanceDevicePunch;
use Modules\Employee\Services\DevicePunchService;
use Modules\Shop\Models\Shop;

/**
 * Attendance machines, file imports of punches and unmatched punches.
 */
class AttendanceDeviceController extends Controller
{
    public function __construct(private DevicePunchService $punches) {}

    public function index(Request $request): View
    {
        $companyId = $this->companyId();

        return view('employee::attendance.devices', [
            'devices' => AttendanceDevice::with('shop:id,name')->withCount(['punches as punches_today' => fn ($query) => $query->whereDate('punched_at', now()->toDateString())])->orderBy('name')->get(),
            'shops' => Shop::where('company_id', $companyId)->orderBy('name')->pluck('name', 'id'),
            'unmatched' => AttendanceDevicePunch::whereNull('employee_id')
                ->selectRaw('device_user_id, COUNT(*) as punches, MAX(punched_at) as last_punch')
                ->groupBy('device_user_id')
                ->orderBy('device_user_id')
                ->get(),
            'serverUrl' => $request->getSchemeAndHttpHost(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['required', 'string', 'max:100', Rule::unique('attendance_devices', 'serial_number')],
            'shop_id' => ['required', 'integer', Rule::in(Shop::where('company_id', $this->companyId())->pluck('id')->all())],
        ]);

        AttendanceDevice::create([...$validated, 'company_id' => $this->companyId()]);

        return back()->with('status', 'হাজিরা মেশিন যোগ করা হয়েছে');
    }

    public function toggle(AttendanceDevice $device): RedirectResponse
    {
        $device->update(['is_active' => ! $device->is_active]);

        return back()->with('status', $device->is_active ? 'মেশিন চালু করা হয়েছে' : 'মেশিন বন্ধ করা হয়েছে');
    }

    public function destroy(AttendanceDevice $device): RedirectResponse
    {
        $device->delete();

        return back()->with('status', 'মেশিন মুছে ফেলা হয়েছে');
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:5120', 'extensions:csv,txt,dat,xlsx,xls'],
            'shop_id' => ['required', 'integer', Rule::in(Shop::where('company_id', $this->companyId())->pluck('id')->all())],
        ]);

        $rows = $this->punches->parseImportFile($request->file('file'));

        if ($rows === []) {
            return back()->withErrors(['file' => 'ফাইলে কোনো পড়ার মতো পাঞ্চ পাওয়া যায়নি (No readable punches found in the file)।']);
        }

        $result = $this->punches->ingest($this->companyId(), (int) $validated['shop_id'], $rows, 'import');

        return back()->with('status', "{$result['received']}টি পাঞ্চ পড়া হয়েছে: নতুন {$result['new']}, কর্মচারীর সাথে মিলেছে {$result['matched']}");
    }

    public function matchUnmatched(): RedirectResponse
    {
        $days = $this->punches->matchUnmatched($this->companyId());

        return back()->with('status', "{$days}টি হাজিরা-দিন হালনাগাদ হয়েছে");
    }

    private function companyId(): int
    {
        return (int) app(TenantContext::class)->companyId();
    }
}
