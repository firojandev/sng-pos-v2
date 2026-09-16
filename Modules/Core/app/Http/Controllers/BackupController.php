<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\DataTables\BackupDataTable;
use Modules\Core\Models\AuditLog;
use Modules\Core\Services\DatabaseBackupService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(protected DatabaseBackupService $backupService) {}

    /**
     * Display a listing of database backups with DataTable.
     */
    public function index(BackupDataTable $dataTable)
    {
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'অনুমোদন নেই / Unauthorized');
        }

        $backups = $this->backupService->getBackups();
        $totalBytes = $backups->sum('size');
        $latest = $backups->first();

        $pdo = DB::connection()->getPdo();
        $driver = DB::connection()->getDriverName();
        $serverVersion = @$pdo->getAttribute(\PDO::ATTR_SERVER_VERSION) ?: '1.0';
        $shortVersion = explode('-', (string) $serverVersion)[0] ?? $serverVersion;
        $driverName = match ($driver) {
            'sqlite' => 'SQLite',
            'pgsql' => 'PostgreSQL',
            default => 'MySQL',
        };

        $metrics = [
            'totalBackups' => $backups->count(),
            'totalSize' => DatabaseBackupService::formatSize($totalBytes),
            'latestBackupAge' => $latest ? $latest['age'] : '—',
            'latestBackupDate' => $latest ? $latest['created_at_formatted'] : 'কোনো ব্যাকআপ নেই / No backup',
            'dbEngine' => "{$driverName} {$shortVersion}",
            'dbName' => DB::connection()->getDatabaseName(),
        ];

        return $dataTable->render('core::backup.index', compact('metrics'));
    }

    /**
     * Trigger a new database backup manually.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'শুধুমাত্র সুপার এডমিন ডাটাবেজ ব্যাকআপ নিতে পারবেন (Only Super Admin can create database backup)',
            ], 403);
        }

        try {
            $backup = $this->backupService->createBackup('sngpos');

            // Log activity in AuditLog
            AuditLog::create([
                'shop_id' => $user->shop_id,
                'user_id' => $user->id,
                'auditable_type' => 'DatabaseBackup',
                'auditable_id' => 0,
                'action' => 'created',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'filename' => $backup['filename'],
                    'size' => $backup['size_formatted'],
                    'path' => $backup['path'],
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ডাটাবেজ ব্যাকআপ সফলভাবে সম্পন্ন হয়েছে (Database backup created successfully)',
                'backup' => $backup,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ডাটাবেজ ব্যাকআপ ব্যর্থ হয়েছে: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Inspect a backup package to get its metadata and contained shops.
     */
    public function inspect(string $filename): JsonResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'অনুমোদন নেই (Unauthorized)',
            ], 403);
        }

        try {
            $info = $this->backupService->inspectBackup($filename);

            return response()->json([
                'success' => true,
                'data' => $info,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Restore database in full or for specific shop(s).
     */
    public function restore(Request $request, string $filename): JsonResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'শুধুমাত্র সুপার এডমিন ডাটাবেজ পুনরুদ্ধার করতে পারবেন (Only Super Admin can restore database)',
            ], 403);
        }

        $confirmText = (string) $request->input('confirm_text', '');
        $isPasswordValid = false;
        if (! empty($user->password)) {
            $isPasswordValid = Hash::check($confirmText, $user->password);
        }

        if ($confirmText !== 'RESTORE' && ! $isPasswordValid) {
            return response()->json([
                'success' => false,
                'message' => 'নিরাপত্তা নিশ্চিতকরণ সঠিক নয়! "RESTORE" লিখুন অথবা পাসওয়ার্ড দিন। (Invalid security confirmation)',
            ], 422);
        }

        $mode = $request->input('mode', 'full');

        try {
            if ($mode === 'shops') {
                $shopIds = array_filter(array_map('intval', (array) $request->input('shop_ids', [])));
                if (empty($shopIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'অনুগ্রহ করে অন্তত একটি দোকান নির্বাচন করুন (Please select at least one shop)',
                    ], 422);
                }

                $res = $this->backupService->restoreShops($filename, $shopIds);

                AuditLog::create([
                    'shop_id' => $user->shop_id,
                    'user_id' => $user->id,
                    'auditable_type' => 'DatabaseBackup',
                    'auditable_id' => 0,
                    'action' => 'restored',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'new_values' => [
                        'filename' => $filename,
                        'mode' => 'shops',
                        'shop_ids' => $shopIds,
                    ],
                ]);

                return response()->json($res);
            }

            // Full database restore
            $res = $this->backupService->restoreFull($filename);

            AuditLog::create([
                'shop_id' => $user->shop_id,
                'user_id' => $user->id,
                'auditable_type' => 'DatabaseBackup',
                'auditable_id' => 0,
                'action' => 'restored',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'filename' => $filename,
                    'mode' => 'full',
                ],
            ]);

            return response()->json($res);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'ডাটাবেজ পুনরুদ্ধার ব্যর্থ হয়েছে: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download an existing database backup file.
     */
    public function download(string $filename): BinaryFileResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'অনুমোদন নেই / Unauthorized');
        }

        $backup = $this->backupService->findBackup($filename);

        if (! $backup) {
            abort(404, 'ব্যাকআপ ফাইলটি পাওয়া যায়নি (Backup file not found)');
        }

        // Log download activity
        AuditLog::create([
            'shop_id' => $user->shop_id,
            'user_id' => $user->id,
            'auditable_type' => 'DatabaseBackup',
            'auditable_id' => 0,
            'action' => 'updated',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'new_values' => [
                'action' => 'downloaded',
                'filename' => $backup['filename'],
                'size' => $backup['size_formatted'],
            ],
        ]);

        return response()->download($backup['path'], $backup['filename'], [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Delete an existing backup file.
     */
    public function destroy(Request $request, string $filename): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'শুধুমাত্র সুপার এডমিন ব্যাকআপ ফাইল মুছে ফেলতে পারবেন (Only Super Admin can delete backup)',
                ], 403);
            }
            abort(403, 'অনুমোদন নেই / Unauthorized');
        }

        $backup = $this->backupService->findBackup($filename);

        if (! $backup) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ব্যাকআপ ফাইলটি পাওয়া যায়নি (Backup file not found)',
                ], 404);
            }

            return redirect()->route('backup.index')->with('error', 'ব্যাকআপ ফাইলটি পাওয়া যায়নি');
        }

        $this->backupService->deleteBackup($filename);

        // Log deletion activity
        AuditLog::create([
            'shop_id' => $user->shop_id,
            'user_id' => $user->id,
            'auditable_type' => 'DatabaseBackup',
            'auditable_id' => 0,
            'action' => 'deleted',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => [
                'filename' => $backup['filename'],
                'size' => $backup['size_formatted'],
            ],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'ব্যাকআপ ফাইলটি সফলভাবে মুছে ফেলা হয়েছে (Backup file deleted successfully)',
            ]);
        }

        return redirect()->route('backup.index')->with('status', 'ব্যাকআপ ফাইলটি মুছে ফেলা হয়েছে');
    }
}
