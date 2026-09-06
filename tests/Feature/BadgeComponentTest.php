<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BadgeComponentTest extends TestCase
{
    public function test_renders_default_badge(): void
    {
        $rendered = Blade::render('<x-core::badge>Default Badge</x-core::badge>');

        $this->assertStringContainsString('badge', $rendered);
        $this->assertStringContainsString('b-teal', $rendered);
        $this->assertStringContainsString('badge-pill', $rendered);
        $this->assertStringContainsString('Default Badge', $rendered);
    }

    public function test_renders_badge_colors_and_variants(): void
    {
        $green = Blade::render('<x-core::badge color="green" size="xs">Active</x-core::badge>');
        $this->assertStringContainsString('b-green', $green);
        $this->assertStringContainsString('badge-xs', $green);

        $solidRed = Blade::render('<x-core::badge color="red" variant="solid" size="md">Danger</x-core::badge>');
        $this->assertStringContainsString('badge-solid-red', $solidRed);
        $this->assertStringContainsString('badge-md', $solidRed);

        $outline = Blade::render('<x-core::badge color="gold" variant="outline">Warning</x-core::badge>');
        $this->assertStringContainsString('badge-outline', $outline);
        $this->assertStringContainsString('badge-gold', $outline);
    }

    public function test_renders_badge_with_dot_and_icon(): void
    {
        $rendered = Blade::render('<x-core::badge color="teal" :dot="true" icon="check">Verified</x-core::badge>');

        $this->assertStringContainsString('badge-dot', $rendered);
        $this->assertStringContainsString('app-icon', $rendered);
        $this->assertStringContainsString('Verified', $rendered);
    }

    public function test_renders_bilingual_labels(): void
    {
        $rendered = Blade::render('<x-core::badge color="green" label="সক্রিয়" label-en="Active" />');

        $this->assertStringContainsString('class="bn">সক্রিয়</span>', $rendered);
        $this->assertStringContainsString('class="en" style="display:none;">Active</span>', $rendered);
    }

    public function test_renders_root_component_alias(): void
    {
        $rendered = Blade::render('<x-badge color="blue" size="sm">Root Alias</x-badge>');

        $this->assertStringContainsString('badge', $rendered);
        $this->assertStringContainsString('b-blue', $rendered);
        $this->assertStringContainsString('badge-sm', $rendered);
        $this->assertStringContainsString('Root Alias', $rendered);
    }
}
