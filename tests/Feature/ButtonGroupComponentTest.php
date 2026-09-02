<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ButtonGroupComponentTest extends TestCase
{
    public function test_renders_default_horizontal_button_group(): void
    {
        $rendered = Blade::render('
            <x-button-group>
                <x-button variant="outline">Left</x-button>
                <x-button variant="outline">Right</x-button>
            </x-button-group>
        ');

        $this->assertStringContainsString('class="btn-group"', $rendered);
        $this->assertStringContainsString('role="group"', $rendered);
        $this->assertStringContainsString('Left', $rendered);
        $this->assertStringContainsString('Right', $rendered);
    }

    public function test_renders_vertical_button_group(): void
    {
        $rendered = Blade::render('
            <x-button-group orientation="vertical">
                <x-button>Top</x-button>
                <x-button>Bottom</x-button>
            </x-button-group>
        ');

        $this->assertStringContainsString('btn-group', $rendered);
        $this->assertStringContainsString('btn-group-vertical', $rendered);
    }

    public function test_renders_sizes_and_radiuses(): void
    {
        $rendered = Blade::render('
            <x-button-group size="sm" rounded="pill" color="teal">
                <x-button>First</x-button>
                <x-button>Second</x-button>
            </x-button-group>
        ');

        $this->assertStringContainsString('btn-group-sm', $rendered);
        $this->assertStringContainsString('btn-group-pill', $rendered);
        $this->assertStringContainsString('btn-group-teal', $rendered);
    }

    public function test_renders_segmented_variant(): void
    {
        $rendered = Blade::render('
            <x-button-group variant="segmented" size="sm" rounded="pill">
                <x-button class="active">Daily</x-button>
                <x-button>Weekly</x-button>
            </x-button-group>
        ');

        $this->assertStringContainsString('btn-group-segmented', $rendered);
        $this->assertStringContainsString('btn-group-pill', $rendered);
    }

    public function test_renders_toolbar_mode(): void
    {
        $rendered = Blade::render('
            <x-button-group toolbar aria-label="Formatting toolbar">
                <x-button-group size="sm">
                    <x-button>B</x-button>
                    <x-button>I</x-button>
                </x-button-group>
            </x-button-group>
        ');

        $this->assertStringContainsString('btn-toolbar', $rendered);
        $this->assertStringContainsString('role="toolbar"', $rendered);
        $this->assertStringContainsString('aria-label="Formatting toolbar"', $rendered);
    }

    public function test_renders_spaced_mode(): void
    {
        $rendered = Blade::render('
            <x-button-group :attached="false" gap="2">
                <x-button>One</x-button>
                <x-button>Two</x-button>
            </x-button-group>
        ');

        $this->assertStringContainsString('btn-group-spaced', $rendered);
        $this->assertStringContainsString('gap-2', $rendered);
    }

    public function test_renders_block_full_width(): void
    {
        $rendered = Blade::render('
            <x-button-group block>
                <x-button>Full 1</x-button>
                <x-button>Full 2</x-button>
            </x-button-group>
        ');

        $this->assertStringContainsString('btn-group-block', $rendered);
    }

    public function test_renders_data_driven_options(): void
    {
        $options = [
            'grid' => 'Grid View',
            'list' => 'List View',
        ];

        $rendered = Blade::render('
            <x-button-group :options="$options" value="grid" size="sm" />
        ', ['options' => $options]);

        $this->assertStringContainsString('Grid View', $rendered);
        $this->assertStringContainsString('List View', $rendered);
        $this->assertStringContainsString('active', $rendered);
        $this->assertStringContainsString('data-value="grid"', $rendered);
    }

    public function test_renders_radio_options_with_name(): void
    {
        $options = [
            ['value' => 'daily', 'label' => 'Daily', 'icon' => 'calendar'],
            ['value' => 'weekly', 'label' => 'Weekly', 'icon' => 'calendar'],
        ];

        $rendered = Blade::render('
            <x-button-group name="period" :options="$options" value="weekly" size="sm" />
        ', ['options' => $options]);

        $this->assertStringContainsString('type="radio"', $rendered);
        $this->assertStringContainsString('name="period"', $rendered);
        $this->assertStringContainsString('value="weekly"', $rendered);
        $this->assertStringContainsString('checked', $rendered);
    }

    public function test_renders_core_namespace_button_group(): void
    {
        $rendered = Blade::render('
            <x-core::button-group size="md" color="teal">
                <x-core::button>Core 1</x-core::button>
                <x-core::button>Core 2</x-core::button>
            </x-core::button-group>
        ');

        $this->assertStringContainsString('btn-group', $rendered);
        $this->assertStringContainsString('btn-group-md', $rendered);
        $this->assertStringContainsString('btn-group-teal', $rendered);
        $this->assertStringContainsString('Core 1', $rendered);
        $this->assertStringContainsString('Core 2', $rendered);
    }
}
