<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Employee\Models\AttendanceDevice;
use Modules\Employee\Services\DevicePunchService;

/**
 * The ADMS "push" protocol spoken by ZKTeco (and compatible) attendance
 * machines. A machine set to this server calls these endpoints on its own;
 * it's identified by its serial number (SN) and must be registered and
 * active, otherwise what it sends is ignored.
 */
class IclockController extends Controller
{
    public function __construct(private DevicePunchService $punches) {}

    /**
     * Handshake: the machine asks how it should send its data.
     */
    public function handshake(Request $request): Response
    {
        $serial = (string) $request->query('SN', '');
        $this->device($request);

        return $this->text(implode("\n", [
            "GET OPTION FROM: {$serial}",
            'ATTLOGStamp=None',
            'OPERLOGStamp=9999',
            'ATTPHOTOStamp=None',
            'ErrorDelay=30',
            'Delay=10',
            'TransTimes=00:00;14:05',
            'TransInterval=1',
            'TransFlag=TransData AttLog',
            'TimeZone=6',
            'Realtime=1',
            'Encrypt=None',
        ]));
    }

    /**
     * The machine uploads data. Attendance punches come in the ATTLOG table;
     * other tables (operation logs, photos, users) are acknowledged only.
     */
    public function upload(Request $request): Response
    {
        $device = $this->device($request);

        if (! $device || strtoupper((string) $request->query('table')) !== 'ATTLOG') {
            return $this->text('OK');
        }

        $result = $this->punches->ingest(
            $device->company_id,
            $device->shop_id,
            $this->punches->parseDeviceLog($request->getContent()),
            'device',
            $device,
        );

        return $this->text('OK: '.$result['received']);
    }

    /**
     * The machine polls for commands; there are none to send.
     */
    public function commands(Request $request): Response
    {
        $this->device($request);

        return $this->text('OK');
    }

    private function device(Request $request): ?AttendanceDevice
    {
        $device = AttendanceDevice::withoutGlobalScopes()
            ->where('serial_number', (string) $request->query('SN', ''))
            ->where('is_active', true)
            ->first();

        $device?->update(['last_seen_at' => now(), 'last_ip' => $request->ip()]);

        return $device;
    }

    private function text(string $body): Response
    {
        return response($body, 200, ['Content-Type' => 'text/plain']);
    }
}
