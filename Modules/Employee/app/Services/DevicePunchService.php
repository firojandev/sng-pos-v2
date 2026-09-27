<?php

namespace Modules\Employee\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\AttendanceDevice;
use Modules\Employee\Models\AttendanceDevicePunch;
use Modules\Employee\Models\AttendanceLog;
use Modules\Employee\Models\Employee;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

/**
 * Takes in punches from attendance machines or files: stores each once,
 * matches it to the employee with that device user ID, turns it into an
 * attendance punch, and works out the affected days again.
 */
class DevicePunchService
{
    public function __construct(private AttendanceService $attendance) {}

    /**
     * @param  list<array{0: string, 1: Carbon}>  $punches  [device user ID, time]
     * @return array{received: int, new: int, matched: int}
     */
    public function ingest(int $companyId, ?int $shopId, array $punches, string $source, ?AttendanceDevice $device = null): array
    {
        $new = 0;
        $matched = 0;
        $affected = [];

        DB::transaction(function () use ($companyId, $shopId, $punches, $source, $device, &$new, &$matched, &$affected) {
            foreach ($punches as [$deviceUserId, $punchedAt]) {
                $inserted = AttendanceDevicePunch::withoutGlobalScopes()->insertOrIgnore([
                    'company_id' => $companyId,
                    'shop_id' => $shopId,
                    'attendance_device_id' => $device?->id,
                    'device_user_id' => $deviceUserId,
                    'punched_at' => $punchedAt->toDateTimeString(),
                    'source' => $source,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (! $inserted) {
                    continue;
                }

                $new++;
                $punch = AttendanceDevicePunch::withoutGlobalScopes()
                    ->where('company_id', $companyId)
                    ->where('device_user_id', $deviceUserId)
                    ->where('punched_at', $punchedAt->toDateTimeString())
                    ->first();

                if ($employee = $this->match($punch)) {
                    $matched++;
                    $shiftDate = $this->attendance->shiftDateFor($employee, $punchedAt);
                    $affected[$employee->id.'|'.$shiftDate->toDateString()] = [$employee, $shiftDate];
                }
            }
        });

        $this->recalculate($affected);

        return ['received' => count($punches), 'new' => $new, 'matched' => $matched];
    }

    /**
     * Match stored punches that had no employee (e.g. after an employee's
     * device user ID is filled in).
     */
    public function matchUnmatched(int $companyId): int
    {
        $affected = [];

        AttendanceDevicePunch::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->whereNull('employee_id')
            ->orderBy('id')
            ->each(function (AttendanceDevicePunch $punch) use (&$affected) {
                if ($employee = $this->match($punch)) {
                    $shiftDate = $this->attendance->shiftDateFor($employee, $punch->punched_at);
                    $affected[$employee->id.'|'.$shiftDate->toDateString()] = [$employee, $shiftDate];
                }
            });

        $this->recalculate($affected);

        return count($affected);
    }

    /**
     * Punch lines the device uploads (ATTLOG): user ID, "Y-m-d H:i:s", then
     * status and verification fields, separated by tabs.
     *
     * @return list<array{0: string, 1: Carbon}>
     */
    public function parseDeviceLog(string $body): array
    {
        $punches = [];

        foreach (preg_split('/\r\n|\r|\n/', trim($body)) as $line) {
            $fields = preg_split('/\t+/', trim($line));

            if (count($fields) >= 2 && ($punch = $this->punch($fields[0], $fields[1]))) {
                $punches[] = $punch;
            }
        }

        return $punches;
    }

    /**
     * Rows of an imported file: a device export (attlog .dat/.txt), CSV or
     * Excel with the device user ID and the time (or a date and a time
     * column). Header and unreadable rows are skipped.
     *
     * @return list<array{0: string, 1: Carbon}>
     */
    public function parseImportFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['xlsx', 'xls'], true)) {
            $rows = IOFactory::load($file->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
        } else {
            $rows = [];
            foreach (preg_split('/\r\n|\r|\n/', trim((string) file_get_contents($file->getRealPath()))) as $line) {
                $rows[] = str_contains($line, "\t") ? preg_split('/\t+/', trim($line)) : str_getcsv($line);
            }
        }

        $punches = [];

        foreach ($rows as $row) {
            $row = array_values(array_map(fn ($cell) => trim((string) $cell), $row));

            if (count($row) < 2) {
                continue;
            }

            $punch = $this->punch($row[0], $row[1]) ?? (isset($row[2]) ? $this->punch($row[0], $row[1].' '.$row[2]) : null);

            if ($punch) {
                $punches[] = $punch;
            }
        }

        return $punches;
    }

    /**
     * @return array{0: string, 1: Carbon}|null
     */
    private function punch(string $deviceUserId, string $time): ?array
    {
        $deviceUserId = trim($deviceUserId);

        if ($deviceUserId === '' || ! preg_match('/\d{1,4}[-\/.]\d{1,2}[-\/.]\d{1,4}/', $time)) {
            return null;
        }

        try {
            // Slash dates are day/month/year, as written in Bangladesh.
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})\s+(\d{1,2}:\d{2}(?::\d{2})?)#', trim($time), $parts)) {
                return [$deviceUserId, Carbon::parse("{$parts[3]}-{$parts[2]}-{$parts[1]} {$parts[4]}")];
            }

            return [$deviceUserId, Carbon::parse($time)];
        } catch (Throwable) {
            return null;
        }
    }

    private function match(AttendanceDevicePunch $punch): ?Employee
    {
        $employee = Employee::withoutGlobalScopes()
            ->where('company_id', $punch->company_id)
            ->where('device_user_id', $punch->device_user_id)
            ->first();

        if (! $employee) {
            return null;
        }

        $log = AttendanceLog::withoutGlobalScopes()->create([
            'company_id' => $punch->company_id,
            'employee_id' => $employee->id,
            'shop_id' => $punch->shop_id ?? $employee->shop_id,
            'punched_at' => $punch->punched_at,
            'source' => $punch->source,
            'device_serial' => $punch->device?->serial_number,
        ]);

        $punch->update(['employee_id' => $employee->id, 'attendance_log_id' => $log->id]);

        return $employee;
    }

    /**
     * @param  array<string, array{0: Employee, 1: Carbon}>  $affected
     */
    private function recalculate(array $affected): void
    {
        foreach ($affected as [$employee, $day]) {
            $this->attendance->process($employee, $day);
        }
    }
}
