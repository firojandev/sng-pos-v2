<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Support\TenantTables;
use PDO;
use ZipArchive;

class DatabaseBackupService
{
    /**
     * Get the absolute path to the backups directory.
     */
    public function getBackupDirectory(): string
    {
        $dir = storage_path('app/private/backups');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        return $dir;
    }

    /**
     * Create a full database backup package with full SQL dump and per-shop scoped dumps.
     *
     * @return array{filename: string, path: string, size: int, size_formatted: string, created_at: Carbon, shops_count: int}
     *
     * @throws Exception
     */
    public function createBackup(?string $prefix = null): array
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $dir = $this->getBackupDirectory();
        $cleanPrefix = $prefix ? preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix) : 'sngerp';
        $timestamp = now()->format('Y-m-d_H-i-s');
        $zipFilename = "{$cleanPrefix}_backup_{$timestamp}.zip";
        $zipPath = $dir.DIRECTORY_SEPARATOR.$zipFilename;

        // Temporary directory for dumping files before zipping
        $tempDir = $dir.DIRECTORY_SEPARATOR."temp_{$timestamp}_".uniqid();
        File::makeDirectory($tempDir, 0755, true, true);
        File::makeDirectory($tempDir.DIRECTORY_SEPARATOR.'shops', 0755, true, true);

        try {
            $pdo = DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $databaseName = (string) DB::connection()->getDatabaseName();
            $serverVersion = @$pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?: '1.0';
            $appVersion = config('app.version', '2.0');

            // 1. Generate full_database.sql
            $fullSqlPath = $tempDir.DIRECTORY_SEPARATOR.'full_database.sql';
            $this->dumpFullDatabase($pdo, $driver, $databaseName, $serverVersion, $appVersion, $fullSqlPath);

            // 2. Fetch all shops for scoped shop dumps
            $shops = [];
            if ($this->tableExists('shops')) {
                $shops = DB::table('shops')
                    ->select(array_values(array_filter(
                        ['id', 'company_id', 'name', 'slug', 'phone'],
                        fn (string $column) => $column !== 'company_id' || Schema::hasColumn('shops', 'company_id'),
                    )))
                    ->get()
                    ->map(fn ($s) => (array) $s)
                    ->toArray();
            }

            // 3. Generate individual shop SQL dumps
            foreach ($shops as $shop) {
                $shopId = (int) $shop['id'];
                $shopSqlPath = $tempDir.DIRECTORY_SEPARATOR.'shops'.DIRECTORY_SEPARATOR."shop_{$shopId}.sql";
                $this->dumpShopData($pdo, $driver, $databaseName, $shop, $shopSqlPath);
            }

            // 4. Generate manifest.json
            $manifest = [
                'app' => 'SNG ERP',
                'version' => $appVersion,
                'created_at' => now()->toDateTimeString(),
                'database' => $databaseName,
                'driver' => $driver,
                'server_version' => $serverVersion,
                'filename' => $zipFilename,
                'shops' => $shops,
            ];
            File::put($tempDir.DIRECTORY_SEPARATOR.'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // 5. Pack into .zip archive
            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new Exception("Unable to create zip file at: {$zipPath}");
            }

            $zip->addFile($fullSqlPath, 'full_database.sql');
            $zip->addFile($tempDir.DIRECTORY_SEPARATOR.'manifest.json', 'manifest.json');

            foreach ($shops as $shop) {
                $shopId = (int) $shop['id'];
                $shopSqlPath = $tempDir.DIRECTORY_SEPARATOR.'shops'.DIRECTORY_SEPARATOR."shop_{$shopId}.sql";
                if (file_exists($shopSqlPath)) {
                    $zip->addFile($shopSqlPath, "shops/shop_{$shopId}.sql");
                }
            }

            $zip->close();

            // 6. Clean up temporary directory
            File::deleteDirectory($tempDir);

            $fileSize = (int) filesize($zipPath);

            return [
                'filename' => $zipFilename,
                'path' => $zipPath,
                'size' => $fileSize,
                'size_formatted' => static::formatSize($fileSize),
                'created_at' => now(),
                'shops_count' => count($shops),
            ];
        } catch (Exception $e) {
            if (File::isDirectory($tempDir)) {
                File::deleteDirectory($tempDir);
            }
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }
            Log::error('Database backup package creation failed: '.$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Dump entire database to SQL file.
     */
    protected function dumpFullDatabase(PDO $pdo, string $driver, string $databaseName, string $serverVersion, string $appVersion, string $filePath): void
    {
        $handle = fopen($filePath, 'w');
        if (! $handle) {
            throw new Exception("Unable to open full backup file for writing: {$filePath}");
        }

        // Header
        $header = "-- ========================================================\n"
            ."-- SNG ERP Full Database Backup\n"
            ."-- Database: `{$databaseName}`\n"
            .'-- Generated at: '.now()->toDateTimeString()."\n"
            ."-- Driver: {$driver} ({$serverVersion})\n"
            ."-- Application Version: {$appVersion}\n"
            ."-- ========================================================\n\n";

        if ($driver === 'sqlite') {
            $header .= "PRAGMA foreign_keys = OFF;\n\n";
        } else {
            $header .= "SET FOREIGN_KEY_CHECKS=0;\n"
                ."SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n"
                ."SET time_zone = \"+00:00\";\n"
                ."SET NAMES utf8mb4;\n\n";
        }

        fwrite($handle, $header);

        // Fetch tables
        $tables = [];
        $tableSqlMap = [];

        if ($driver === 'sqlite') {
            $tablesQuery = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name NOT LIKE 'temp_%'");
            while ($row = $tablesQuery->fetch(PDO::FETCH_ASSOC)) {
                $tables[] = $row['name'];
                $tableSqlMap[$row['name']] = $row['sql'];
            }
        } else {
            $tablesQuery = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            while ($row = $tablesQuery->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
        }

        foreach ($tables as $table) {
            fwrite($handle, "--\n-- Table structure for table `{$table}`\n--\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            if ($driver === 'sqlite') {
                $createSql = $tableSqlMap[$table] ?? '';
            } else {
                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_NUM);
                $createSql = $createStmt[1] ?? '';
            }
            fwrite($handle, $createSql.";\n\n");

            fwrite($handle, "--\n-- Dumping data for table `{$table}`\n--\n");

            $dataStmt = $pdo->query("SELECT * FROM `{$table}`", PDO::FETCH_ASSOC);
            $rowsBuffer = [];
            $batchSize = 200;

            while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                $escapedValues = [];
                foreach ($row as $col => $val) {
                    if (is_null($val)) {
                        $escapedValues[] = 'NULL';
                    } else {
                        $escapedValues[] = $pdo->quote((string) $val);
                    }
                }
                $rowsBuffer[] = '('.implode(', ', $escapedValues).')';

                if (count($rowsBuffer) >= $batchSize) {
                    $insertSql = "INSERT INTO `{$table}` VALUES \n".implode(",\n", $rowsBuffer).";\n";
                    fwrite($handle, $insertSql);
                    $rowsBuffer = [];
                }
            }

            if (! empty($rowsBuffer)) {
                $insertSql = "INSERT INTO `{$table}` VALUES \n".implode(",\n", $rowsBuffer).";\n";
                fwrite($handle, $insertSql);
            }

            fwrite($handle, "\n");
        }

        // Views
        if ($driver === 'sqlite') {
            $viewsQuery = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type='view'");
            while ($row = $viewsQuery->fetch(PDO::FETCH_ASSOC)) {
                $viewName = $row['name'];
                fwrite($handle, "--\n-- View structure for view `{$viewName}`\n--\n");
                fwrite($handle, "DROP VIEW IF EXISTS `{$viewName}`;\n");
                fwrite($handle, $row['sql'].";\n\n");
            }
        } else {
            $viewsQuery = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
            while ($row = $viewsQuery->fetch(PDO::FETCH_NUM)) {
                $viewName = $row[0];
                fwrite($handle, "--\n-- View structure for view `{$viewName}`\n--\n");
                fwrite($handle, "DROP VIEW IF EXISTS `{$viewName}`;\n");
                $createViewStmt = $pdo->query("SHOW CREATE VIEW `{$viewName}`")->fetch(PDO::FETCH_NUM);
                $createViewSql = $createViewStmt[1] ?? '';
                fwrite($handle, $createViewSql.";\n\n");
            }
        }

        // Footer
        if ($driver === 'sqlite') {
            $footer = "PRAGMA foreign_keys = ON;\n"
                .'-- Dump completed on: '.now()->toDateTimeString()."\n";
        } else {
            $footer = "SET FOREIGN_KEY_CHECKS=1;\n"
                .'-- Dump completed on: '.now()->toDateTimeString()."\n";
        }
        fwrite($handle, $footer);

        fclose($handle);
    }

    /**
     * Super admins are never part of a shop's data.
     *
     * @var array<string, string>
     */
    protected array $shopBackupExclusions = [
        'users' => "`email` NOT IN ('softngear@gmail.com')",
    ];

    /**
     * Dump a shop's data into a standalone SQL file: every tenant table the
     * schema links to the shop (see TenantTables), plus its company's data
     * when the company runs only this shop. A multi-shop company's shared
     * data (customers, catalogue, ledger, HR rules, billing) is left out, so
     * restoring one shop never rewrites the others'.
     *
     * @param  array{id: int|string, name: string, slug: string, company_id?: int|string|null}  $shop
     */
    protected function dumpShopData(PDO $pdo, string $driver, string $databaseName, array $shop, string $filePath): void
    {
        $handle = fopen($filePath, 'w');
        if (! $handle) {
            throw new Exception("Unable to open shop backup file for writing: {$filePath}");
        }

        $shopId = (int) $shop['id'];
        $shopName = $shop['name'];
        $companyId = (int) ($shop['company_id'] ?? 0) ?: null;
        $includeCompany = $companyId && $this->tableExists('companies') && DB::table('shops')->where('company_id', $companyId)->count() === 1;

        $plan = app(TenantTables::class)->plan($shopId, $companyId, (bool) $includeCompany, $this->shopBackupExclusions);

        $header = "-- ========================================================\n"
            ."-- SNG ERP Shop Scoped Backup\n"
            ."-- Shop: {$shopName} (ID: #{$shopId})\n"
            ."-- Database: `{$databaseName}`\n"
            .'-- Generated at: '.now()->toDateTimeString()."\n"
            .'-- Tables: '.count($plan).($includeCompany ? ' (with its company)' : ' (shop data only; company shared)')."\n"
            ."-- ========================================================\n\n";

        if ($driver === 'sqlite') {
            $header .= "PRAGMA foreign_keys = OFF;\n\n";
        } else {
            $header .= "SET FOREIGN_KEY_CHECKS=0;\n"
                ."SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n"
                ."SET time_zone = \"+00:00\";\n"
                ."SET NAMES utf8mb4;\n\n";
        }

        fwrite($handle, $header);

        // 1. Wipe the shop's current rows, children before parents (their
        //    conditions look up the parents).
        fwrite($handle, "--\n-- Step 1: Wipe existing records for Shop #{$shopId}\n--\n");
        foreach (array_reverse($plan, true) as $table => $whereClause) {
            fwrite($handle, "DELETE FROM `{$table}` WHERE {$whereClause};\n");
        }

        // 2. Insert the backed-up rows, parents before children.
        fwrite($handle, "\n--\n-- Step 2: Insert backup records for Shop #{$shopId}\n--\n");
        foreach ($plan as $table => $whereClause) {
            $this->dumpTableDataWhere($pdo, $handle, $table, $whereClause);
        }

        if ($driver === 'sqlite') {
            $footer = "PRAGMA foreign_keys = ON;\n"
                ."-- Shop #{$shopId} restore completed on: ".now()->toDateTimeString()."\n";
        } else {
            $footer = "SET FOREIGN_KEY_CHECKS=1;\n"
                ."-- Shop #{$shopId} restore completed on: ".now()->toDateTimeString()."\n";
        }
        fwrite($handle, $footer);

        fclose($handle);
    }

    /**
     * Dump data from table with a custom WHERE clause.
     */
    protected function dumpTableDataWhere(PDO $pdo, $handle, string $table, string $whereClause): void
    {
        try {
            $dataStmt = $pdo->query("SELECT * FROM `{$table}` WHERE {$whereClause}", PDO::FETCH_ASSOC);
            if (! $dataStmt) {
                return;
            }

            $rowsBuffer = [];
            $batchSize = 200;
            $hasRows = false;

            while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
                $hasRows = true;
                $escapedValues = [];
                foreach ($row as $col => $val) {
                    if (is_null($val)) {
                        $escapedValues[] = 'NULL';
                    } else {
                        $escapedValues[] = $pdo->quote((string) $val);
                    }
                }
                $rowsBuffer[] = '('.implode(', ', $escapedValues).')';

                if (count($rowsBuffer) >= $batchSize) {
                    $insertSql = "INSERT INTO `{$table}` VALUES \n".implode(",\n", $rowsBuffer).";\n";
                    fwrite($handle, $insertSql);
                    $rowsBuffer = [];
                }
            }

            if (! empty($rowsBuffer)) {
                $insertSql = "INSERT INTO `{$table}` VALUES \n".implode(",\n", $rowsBuffer).";\n";
                fwrite($handle, $insertSql);
            }

            if ($hasRows) {
                fwrite($handle, "\n");
            }
        } catch (Exception $e) {
            Log::warning("Could not dump rows from table {$table} with where {$whereClause}: ".$e->getMessage());
        }
    }

    /**
     * Inspect a backup file and return its metadata and contained shops.
     *
     * @return array{filename: string, size: int, size_formatted: string, created_at: string, type: 'zip'|'sql', can_restore_shops: bool, shops: array<int, array{id: int, name: string, slug: string}>}
     */
    public function inspectBackup(string $filename): array
    {
        $backup = $this->findBackup($filename);
        if (! $backup) {
            throw new Exception("Backup file not found: {$filename}");
        }

        $filePath = $backup['path'];
        $ext = $backup['extension'];

        if ($ext === 'zip') {
            $zip = new ZipArchive;
            if ($zip->open($filePath) === true) {
                $manifestContent = $zip->getFromName('manifest.json');
                $zip->close();

                if ($manifestContent) {
                    $manifest = json_decode($manifestContent, true) ?: [];
                    $shops = $manifest['shops'] ?? [];

                    return [
                        'filename' => $backup['filename'],
                        'size' => $backup['size'],
                        'size_formatted' => $backup['size_formatted'],
                        'created_at' => $backup['created_at_formatted'],
                        'type' => 'zip',
                        'can_restore_shops' => ! empty($shops),
                        'shops' => $shops,
                    ];
                }
            }
        }

        // Legacy .sql file
        return [
            'filename' => $backup['filename'],
            'size' => $backup['size'],
            'size_formatted' => $backup['size_formatted'],
            'created_at' => $backup['created_at_formatted'],
            'type' => 'sql',
            'can_restore_shops' => false,
            'shops' => [],
        ];
    }

    /**
     * Restore full database from backup.
     *
     * @return array{success: bool, message: string, filename: string}
     *
     * @throws Exception
     */
    public function restoreFull(string $filename): array
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $backup = $this->findBackup($filename);
        if (! $backup) {
            throw new Exception("Backup file not found: {$filename}");
        }

        $filePath = $backup['path'];
        $ext = $backup['extension'];

        if ($ext === 'zip') {
            $zip = new ZipArchive;
            if ($zip->open($filePath) !== true) {
                throw new Exception("Could not open zip archive: {$filePath}");
            }

            $sql = $zip->getFromName('full_database.sql');
            $zip->close();

            if (! $sql) {
                throw new Exception('Zip file does not contain full_database.sql');
            }

            $this->executeRawSql($sql);
        } else {
            $sql = File::get($filePath);
            $this->executeRawSql($sql);
        }

        return [
            'success' => true,
            'message' => 'সম্পূর্ণ ডাটাবেজ সফলভাবে পুনরুদ্ধার করা হয়েছে (Full database restored successfully)',
            'filename' => $filename,
        ];
    }

    /**
     * Restore specific shop(s) from backup.
     *
     * @param  int[]  $shopIds
     * @return array{success: bool, message: string, filename: string, restored_shops: int[]}
     *
     * @throws Exception
     */
    public function restoreShops(string $filename, array $shopIds): array
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $backup = $this->findBackup($filename);
        if (! $backup) {
            throw new Exception("Backup file not found: {$filename}");
        }

        if ($backup['extension'] !== 'zip') {
            throw new Exception('Shop-specific restore requires a .zip backup package.');
        }

        $zip = new ZipArchive;
        if ($zip->open($backup['path']) !== true) {
            throw new Exception("Could not open zip archive: {$backup['path']}");
        }

        $restored = [];

        foreach ($shopIds as $shopId) {
            $shopId = (int) $shopId;
            $entryName = "shops/shop_{$shopId}.sql";
            $sql = $zip->getFromName($entryName);

            if (! $sql) {
                Log::warning("Backup {$filename} does not contain {$entryName}");

                continue;
            }

            $this->executeRawSql($sql);
            $restored[] = $shopId;
        }

        $zip->close();

        if (empty($restored)) {
            throw new Exception('No shop SQL dumps were found or restored from this backup.');
        }

        $count = count($restored);

        return [
            'success' => true,
            'message' => "নির্বাচিত {$count}টি দোকানের ডাটা সফলভাবে পুনরুদ্ধার করা হয়েছে ({$count} shop(s) restored successfully)",
            'filename' => $filename,
            'restored_shops' => $restored,
        ];
    }

    /**
     * Execute SQL script statement by statement.
     */
    protected function executeRawSql(string $sql): void
    {
        $lines = explode("\n", $sql);
        $currentStmt = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $currentStmt .= $line."\n";

            if (str_ends_with($trimmed, ';')) {
                try {
                    DB::unprepared($currentStmt);
                } catch (Exception $e) {
                    Log::error('Restore statement execution error: '.$e->getMessage(), ['sql' => substr($currentStmt, 0, 300)]);
                    throw $e;
                }
                $currentStmt = '';
            }
        }

        if (trim($currentStmt) !== '') {
            DB::unprepared($currentStmt);
        }
    }

    /**
     * Get all backup files in the backup directory.
     *
     * @return Collection<int, array{id: string, filename: string, path: string, size: int, size_formatted: string, extension: string, created_at: Carbon, created_at_formatted: string, age: string}>
     */
    public function getBackups(): Collection
    {
        $dir = $this->getBackupDirectory();
        if (! File::isDirectory($dir)) {
            return collect();
        }

        $files = File::files($dir);
        $backups = [];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['sql', 'gz', 'zip'], true)) {
                continue;
            }

            $size = $file->getSize();
            $mtime = $file->getMTime();
            $createdAt = Carbon::createFromTimestamp($mtime);

            $backups[] = [
                'id' => md5($file->getFilename()),
                'filename' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $size,
                'size_formatted' => static::formatSize($size),
                'extension' => $ext,
                'created_at' => $createdAt,
                'created_at_formatted' => $createdAt->format('d M, Y h:i A'),
                'age' => $createdAt->diffForHumans(),
            ];
        }

        return collect($backups)->sortByDesc('created_at')->values();
    }

    /**
     * Find a backup by its filename.
     *
     * @return array{id: string, filename: string, path: string, size: int, size_formatted: string, extension: string, created_at: Carbon, created_at_formatted: string, age: string}|null
     */
    public function findBackup(string $filename): ?array
    {
        $clean = basename($filename);
        $filePath = $this->getBackupDirectory().DIRECTORY_SEPARATOR.$clean;

        if (! file_exists($filePath) || ! is_file($filePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (! in_array($ext, ['sql', 'gz', 'zip'], true)) {
            return null;
        }

        $size = (int) filesize($filePath);
        $createdAt = Carbon::createFromTimestamp(filemtime($filePath));

        return [
            'id' => md5($clean),
            'filename' => $clean,
            'path' => $filePath,
            'size' => $size,
            'size_formatted' => static::formatSize($size),
            'extension' => $ext,
            'created_at' => $createdAt,
            'created_at_formatted' => $createdAt->format('d M, Y h:i A'),
            'age' => $createdAt->diffForHumans(),
        ];
    }

    /**
     * Delete a backup file by filename.
     */
    public function deleteBackup(string $filename): bool
    {
        $clean = basename($filename);
        $filePath = $this->getBackupDirectory().DIRECTORY_SEPARATOR.$clean;

        if (file_exists($filePath) && is_file($filePath)) {
            return @unlink($filePath);
        }

        return false;
    }

    /**
     * Check if a table exists in the current connection.
     */
    protected function tableExists(string $table): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($table);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Format bytes into a human readable string.
     */
    public static function formatSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
