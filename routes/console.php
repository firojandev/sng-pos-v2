<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Models\Setting;
use Modules\Core\Services\DatabaseBackupService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('landing:toggle {state? : on, off, or toggle}', function (?string $state = null) {
    $current = Setting::isLandingPageEnabled();
    $newState = match (strtolower((string) $state)) {
        'on', '1', 'enable', 'enabled', 'true' => true,
        'off', '0', 'disable', 'disabled', 'false' => false,
        default => ! $current,
    };

    Setting::setLandingPageEnabled($newState);

    $status = $newState ? 'ENABLED (সক্রিয়)' : 'DISABLED (নিষ্ক্রিয়)';
    $this->info("Landing page is now {$status}.");
})->purpose('Enable, disable, or toggle the public landing page');

Artisan::command('landing:status', function () {
    $current = Setting::isLandingPageEnabled();
    $status = $current ? 'ENABLED (সক্রিয়)' : 'DISABLED (নিষ্ক্রিয়)';
    $this->info("Landing page is currently {$status}.");
})->purpose('Check the current status of the public landing page');

Artisan::command('db:backup {--prefix=sngpos : Prefix for the backup file name}', function (DatabaseBackupService $service) {
    $this->info('Starting database backup...');
    $prefix = $this->option('prefix') ?: 'sngpos';
    $result = $service->createBackup($prefix);
    $this->info("Backup created successfully: {$result['filename']} ({$result['size_formatted']})");
    $this->line("Saved to: {$result['path']}");
})->purpose('Create a full MySQL database backup');
