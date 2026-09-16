<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WhatsAppServerSetupCommandTest extends TestCase
{
    public function test_whatsapp_setup_command_executes_successfully_with_skip_flags(): void
    {
        $this->artisan('whatsapp:setup', [
            '--skip-install' => true,
            '--skip-migrate' => true,
        ])
            ->expectsOutputToContain('SNG POS - WhatsApp Server Setup Assistant')
            ->expectsOutputToContain('Checking System Prerequisites')
            ->expectsOutputToContain('Configuring Environment Variables')
            ->expectsOutputToContain('Generating Production Service Configuration Templates')
            ->assertSuccessful();

        $servicesDir = storage_path('whatsapp-services');
        $this->assertTrue(File::isDirectory($servicesDir));
        $this->assertTrue(File::exists("{$servicesDir}/ecosystem.config.js"));
        $this->assertTrue(File::exists("{$servicesDir}/whatsapp-sidecar.conf"));
        $this->assertTrue(File::exists("{$servicesDir}/whatsapp-sidecar.service"));

        $pm2Content = File::get("{$servicesDir}/ecosystem.config.js");
        $this->assertStringContainsString('sng-whatsapp-sidecar', $pm2Content);
        $this->assertStringContainsString('PORT: 3000', $pm2Content);

        $supervisorContent = File::get("{$servicesDir}/whatsapp-sidecar.conf");
        $this->assertStringContainsString('[program:sng-whatsapp-sidecar]', $supervisorContent);
        $this->assertStringContainsString('AUTO_START_SESSIONS="true"', $supervisorContent);
    }
}
