<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ButtonComponentTest extends TestCase
{
    public function test_renders_default_button(): void
    {
        $rendered = Blade::render('<x-button>Click Me</x-button>');

        $this->assertStringContainsString('class="btn btn-solid-gold btn-gold btn-md"', $rendered);
        $this->assertStringContainsString('type="button"', $rendered);
        $this->assertStringContainsString('Click Me', $rendered);
    }

    public function test_renders_anchor_tag_when_href_is_passed(): void
    {
        $rendered = Blade::render('<x-button href="/dashboard" color="teal" size="sm">Go to Dashboard</x-button>');

        $this->assertStringContainsString('<a', $rendered);
        $this->assertStringContainsString('href="/dashboard"', $rendered);
        $this->assertStringContainsString('btn-solid-teal', $rendered);
        $this->assertStringContainsString('btn-sm', $rendered);
    }

    public function test_renders_variants_and_colors(): void
    {
        $outline = Blade::render('<x-button variant="outline" color="red">Delete</x-button>');
        $this->assertStringContainsString('btn-outline-red', $outline);

        $soft = Blade::render('<x-button variant="soft" color="green">Approve</x-button>');
        $this->assertStringContainsString('btn-soft-green', $soft);

        $ghost = Blade::render('<x-button variant="ghost" color="dark">Cancel</x-button>');
        $this->assertStringContainsString('btn-ghost-dark', $ghost);

        $link = Blade::render('<x-button variant="link" color="blue">Learn more</x-button>');
        $this->assertStringContainsString('btn-link-blue', $link);
    }

    public function test_renders_icons(): void
    {
        $rendered = Blade::render('<x-button icon="plus" icon-right="arrow-right">Create</x-button>');

        $this->assertStringContainsString('app-icon', $rendered);
        $this->assertStringContainsString('d="M12 5v14M5 12h14"', $rendered);
        $this->assertStringContainsString('d="m12 5 7 7-7 7M19 12H5"', $rendered);
    }

    public function test_renders_loading_state(): void
    {
        $rendered = Blade::render('<x-button loading loading-text="Saving...">Save</x-button>');

        $this->assertStringContainsString('is-loading', $rendered);
        $this->assertStringContainsString('btn-spinner', $rendered);
        $this->assertStringContainsString('Saving...', $rendered);
        $this->assertStringContainsString('disabled', $rendered);
    }

    public function test_renders_icon_only_and_pill(): void
    {
        $rendered = Blade::render('<x-button icon="trash" color="red" variant="soft" icon-only rounded="pill" title="Delete" />');

        $this->assertStringContainsString('btn-icon-only', $rendered);
        $this->assertStringContainsString('btn-pill', $rendered);
        $this->assertStringContainsString('btn-soft-red', $rendered);
    }

    public function test_renders_core_namespace_button(): void
    {
        $rendered = Blade::render('<x-core::button icon="edit" color="teal" size="sm">Edit Item</x-core::button>');

        $this->assertStringContainsString('btn-solid-teal', $rendered);
        $this->assertStringContainsString('btn-sm', $rendered);
        $this->assertStringContainsString('Edit Item', $rendered);
    }

    public function test_styleguide_view_renders_successfully(): void
    {
        $rendered = view('core::pages.styleguide')->render();

        $this->assertStringContainsString('বাটন ও আইকন স্টাইল গাইড', $rendered);
        $this->assertStringContainsString('Button &amp; Icon Style Guide', $rendered);
        $this->assertStringContainsString('id="demo-btn"', $rendered);
        $this->assertStringContainsString('btn-solid-gold', $rendered);
    }
}
