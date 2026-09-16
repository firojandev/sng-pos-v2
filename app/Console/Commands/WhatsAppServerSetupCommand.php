<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Kstmostofa\LaravelWhatsApp\Web\SidecarManager;
use Symfony\Component\Process\Process;

class WhatsAppServerSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:setup
        {--skip-install : Skip npm install of sidecar dependencies}
        {--clean : Clean install sidecar node_modules and Puppeteer cache}
        {--skip-chromium : Skip Puppeteer browser download if system Chromium is used}
        {--skip-migrate : Skip running database migrations}
        {--start : Start the WhatsApp sidecar process immediately after setup}
        {--port=3000 : Port for the WhatsApp sidecar HTTP server}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All-in-one automated setup for WhatsApp Web sidecar on server (dependencies, environment, migrations, storage, and service files)';

    /**
     * Execute the console command.
     */
    public function handle(SidecarManager $manager): int
    {
        $this->displayBanner();

        $port = (int) $this->option('port');

        // Step 1: Check System Binaries (Node.js & npm)
        $this->info('1️⃣  Checking System Prerequisites (Node.js & npm)...');
        if (! $this->checkSystemPrerequisites()) {
            return self::FAILURE;
        }

        // Step 2: Configure Environment Variables (.env)
        $this->info('2️⃣  Configuring Environment Variables in .env...');
        $this->configureEnvironment($port);

        // Step 3: Setup Storage Directories and Permissions
        $this->info('3️⃣  Setting up Storage Directories & Permissions...');
        $this->setupStorageDirectories();

        // Step 4: Run Database Migrations
        if (! $this->option('skip-migrate')) {
            $this->info('4️⃣  Running WhatsApp Database Migrations...');
            $this->call('migrate', ['--force' => true]);
        } else {
            $this->line('  ⏩ Skipping migrations (--skip-migrate).');
        }

        // Step 5: Install Sidecar Node.js Dependencies
        if (! $this->option('skip-install')) {
            $this->info('5️⃣  Installing WhatsApp Sidecar Node.js Dependencies...');
            $installParams = [];
            if ($this->option('clean')) {
                $installParams['--clean'] = true;
            }
            if ($this->option('skip-chromium')) {
                $installParams['--skip-chromium'] = true;
            }

            $installResult = $this->call('whatsapp:sidecar:install', $installParams);
            if ($installResult !== self::SUCCESS) {
                $this->warn('  ⚠️ Sidecar installation reported warnings/errors. Please review output above.');
            }
        } else {
            $this->line('  ⏩ Skipping sidecar npm installation (--skip-install).');
        }

        // Step 6: Generate Production Service Config Files (PM2, Supervisor, Systemd)
        $this->info('6️⃣  Generating Production Service Configuration Templates...');
        $configsDir = $this->generateProductionConfigs($port);

        // Step 7: Optional Startup
        if ($this->option('start')) {
            $this->info('7️⃣  Starting WhatsApp Sidecar Background Process...');
            $this->call('whatsapp:sidecar:start');
        }

        // Step 8: Show Current Status
        $this->newLine();
        $this->info('📊 Current Sidecar Status:');
        $this->call('whatsapp:sidecar:status');

        // Final summary and guidance
        $this->displaySummary($configsDir);

        return self::SUCCESS;
    }

    /**
     * Display styled setup banner.
     */
    protected function displayBanner(): void
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>====================================================================</>');
        $this->line('<fg=green;options=bold>               SNG POS - WhatsApp Server Setup Assistant           </>');
        $this->line('<fg=cyan;options=bold>====================================================================</>');
        $this->newLine();
    }

    /**
     * Check if Node.js and npm are installed on the host.
     */
    protected function checkSystemPrerequisites(): bool
    {
        $nodeProcess = new Process(['node', '-v']);
        $nodeProcess->run();
        $nodeVersion = trim($nodeProcess->getOutput());

        $npmProcess = new Process(['npm', '-v']);
        $npmProcess->run();
        $npmVersion = trim($npmProcess->getOutput());

        if (! $nodeProcess->isSuccessful() || empty($nodeVersion)) {
            $this->error('  ❌ Node.js is not found or not in PATH.');
            $this->line('     Please install Node.js (v18 or v20 LTS recommended):');
            $this->line('     Ubuntu/Debian: curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - && sudo apt-get install -y nodejs');
            $this->newLine();

            return false;
        }

        if (! $npmProcess->isSuccessful() || empty($npmVersion)) {
            $this->error('  ❌ npm is not found in PATH.');

            return false;
        }

        $this->line("  ✅ Node.js found: <info>{$nodeVersion}</info>");
        $this->line("  ✅ npm found: <info>{$npmVersion}</info>");
        $this->newLine();

        return true;
    }

    /**
     * Configure necessary environment variables in .env.
     */
    protected function configureEnvironment(int $port): void
    {
        $keys = [
            'WHATSAPP_WEB_ENABLED' => 'true',
            'WHATSAPP_WEB_PORT' => (string) $port,
            'WHATSAPP_PERSIST_INCOMING' => 'true',
            'AUTO_START_SESSIONS' => 'true',
            'WHATSAPP_UI_ENABLED' => 'true',
            'WHATSAPP_UI_CSS_MODE' => 'standalone',
        ];

        foreach ($keys as $key => $value) {
            $this->setEnvValue($key, $value);
        }

        $this->line('  ✅ .env configured with recommended WhatsApp production settings.');
        $this->newLine();
    }

    /**
     * Ensure storage directories exist and are writable.
     */
    protected function setupStorageDirectories(): void
    {
        $dirs = [
            storage_path('app/whatsapp-sidecar'),
            storage_path('app/whatsapp-sidecar/sessions'),
            storage_path('whatsapp-services'),
            storage_path('logs'),
        ];

        foreach ($dirs as $dir) {
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0775, true, true);
            }
        }

        $this->line('  ✅ Storage directories ready: <comment>'.storage_path('app/whatsapp-sidecar/sessions').'</comment>');
        $this->newLine();
    }

    /**
     * Generate PM2, Supervisor, and Systemd configuration files.
     */
    protected function generateProductionConfigs(int $port): string
    {
        $baseDir = storage_path('whatsapp-services');
        if (! File::isDirectory($baseDir)) {
            File::makeDirectory($baseDir, 0775, true, true);
        }

        $basePath = base_path();
        $sidecarScript = base_path('vendor/kstmostofa/laravel-whatsapp/sidecar/index.js');
        $sessionDir = storage_path('app/whatsapp-sidecar/sessions');
        $logPath = storage_path('logs/whatsapp-sidecar.log');
        $errPath = storage_path('logs/whatsapp-sidecar.err');

        // 1. PM2 Ecosystem configuration
        $pm2Config = <<<JS
module.exports = {
  apps: [
    {
      name: 'sng-whatsapp-sidecar',
      script: 'vendor/kstmostofa/laravel-whatsapp/sidecar/index.js',
      cwd: '{$basePath}',
      watch: false,
      autorestart: true,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
        PORT: {$port},
        AUTO_START_SESSIONS: 'true',
        SESSION_DIR: '{$sessionDir}'
      }
    }
  ]
};
JS;
        File::put("{$baseDir}/ecosystem.config.js", $pm2Config);

        // 2. Supervisor Configuration
        $supervisorConfig = <<<INI
[program:sng-whatsapp-sidecar]
process_name=%(program_name)s
command=node vendor/kstmostofa/laravel-whatsapp/sidecar/index.js
directory={$basePath}
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile={$logPath}
environment=PORT="{$port}",AUTO_START_SESSIONS="true",SESSION_DIR="{$sessionDir}"
INI;
        File::put("{$baseDir}/whatsapp-sidecar.conf", $supervisorConfig);

        // 3. Systemd Unit File
        $systemdConfig = <<<INI
[Unit]
Description=SNG POS WhatsApp Web Sidecar
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory={$basePath}
ExecStart=/usr/bin/node {$sidecarScript}
Restart=always
RestartSec=5
Environment=PORT={$port}
Environment=AUTO_START_SESSIONS=true
Environment=SESSION_DIR={$sessionDir}
StandardOutput=append:{$logPath}
StandardError=append:{$errPath}

[Install]
WantedBy=multi-user.target
INI;
        File::put("{$baseDir}/whatsapp-sidecar.service", $systemdConfig);

        $this->line('  ✅ Configuration templates generated in: <comment>'.$baseDir.'</comment>');
        $this->line('     - <info>ecosystem.config.js</info> (PM2)');
        $this->line('     - <info>whatsapp-sidecar.conf</info> (Supervisor)');
        $this->line('     - <info>whatsapp-sidecar.service</info> (Systemd)');
        $this->newLine();

        return $baseDir;
    }

    /**
     * Display final completion summary and helpful deployment commands.
     */
    protected function displaySummary(string $configsDir): void
    {
        $this->newLine();
        $this->line('<fg=green;options=bold>====================================================================</>');
        $this->line('<fg=green;options=bold>                    WhatsApp Setup Complete! 🎉                   </>');
        $this->line('<fg=green;options=bold>====================================================================</>');
        $this->newLine();

        $this->line('<fg=yellow;options=bold>How to run in Production:</>');
        $this->newLine();

        $this->line('<fg=cyan;options=bold>Option A: Using PM2 (Recommended):</>');
        $this->line("  pm2 start {$configsDir}/ecosystem.config.js");
        $this->line('  pm2 save && pm2 startup');
        $this->newLine();

        $this->line('<fg=cyan;options=bold>Option B: Using Linux Supervisor:</>');
        $this->line("  sudo cp {$configsDir}/whatsapp-sidecar.conf /etc/supervisor/conf.d/");
        $this->line('  sudo supervisorctl reread && sudo supervisorctl update');
        $this->line('  sudo supervisorctl start sng-whatsapp-sidecar');
        $this->newLine();

        $this->line('<fg=cyan;options=bold>Option C: Development / Artisan CLI:</>');
        $this->line('  php artisan whatsapp:sidecar:start');
        $this->newLine();

        $this->line('<fg=yellow;options=bold>Next Steps in Web Dashboard:</>');
        $this->line('  1. Login to SNG POS and open: <info>/settings/whatsapp</info>');
        $this->line('  2. Scan the QR code using your WhatsApp mobile app (Linked Devices).');
        $this->line('  3. Invoices will now be sent directly from your linked personal WhatsApp number!');
        $this->newLine();
    }

    /**
     * Safely write or update a key=value pair in .env file.
     */
    protected function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            return;
        }

        $content = File::get($envPath);

        if (preg_match("/^{$key}=/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        } else {
            $content = rtrim($content)."\n{$key}={$value}\n";
        }

        File::put($envPath, $content);
    }
}
