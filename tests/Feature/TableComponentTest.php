<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class TableComponentTest extends TestCase
{
    public function test_simple_table_renders_successfully(): void
    {
        $rendered = Blade::render('
            <x-table title="গ্রাহক তালিকা" subtitle="সর্বমোট ৫০ জন">
                <x-slot:header>
                    <x-table.th>নাম</x-table.th>
                    <x-table.th align="right">ব্যালেন্স</x-table.th>
                </x-slot:header>
                <x-table.tr>
                    <x-table.td bold>রহিম উদ্দিন</x-table.td>
                    <x-table.td align="right">৳ ৫,২০০</x-table.td>
                </x-table.tr>
            </x-table>
        ');

        $this->assertStringContainsString('table-container', $rendered);
        $this->assertStringContainsString('গ্রাহক তালিকা', $rendered);
        $this->assertStringContainsString('রহিম উদ্দিন', $rendered);
        $this->assertStringContainsString('৳ ৫,২০০', $rendered);
    }

    public function test_table_variants_and_sizes_render_classes(): void
    {
        $rendered = Blade::render('
            <x-table variant="striped" size="sm" color="gold" hoverable bordered sticky-header max-height="300px">
                <x-slot:header>
                    <x-table.th sortable direction="asc">আইটেম</x-table.th>
                    <x-table.th checkbox />
                </x-slot:header>
                <x-table.tr selected>
                    <x-table.td truncate>স্যামসাং গ্যালাক্সি মোবাইল ফোন</x-table.td>
                    <x-table.td checkbox value="101" />
                </x-table.tr>
            </x-table>
        ');

        $this->assertStringContainsString('app-table-striped', $rendered);
        $this->assertStringContainsString('app-table-bordered', $rendered);
        $this->assertStringContainsString('table-container-sm', $rendered);
        $this->assertStringContainsString('table-gold', $rendered);
        $this->assertStringContainsString('table-sticky-header', $rendered);
        $this->assertStringContainsString('sorted-asc', $rendered);
        $this->assertStringContainsString('data-table-select-all', $rendered);
        $this->assertStringContainsString('data-table-select-row', $rendered);
        $this->assertStringContainsString('is-selected', $rendered);
    }

    public function test_empty_table_state_renders(): void
    {
        $rendered = Blade::render('
            <x-table empty empty-title="কোনো ডাটা পাওয়া যায়নি" empty-description="দয়া করে ফিল্টার পরিবর্তন করুন">
                <x-slot:header>
                    <x-table.th>কলাম</x-table.th>
                </x-slot:header>
            </x-table>
        ');

        $this->assertStringContainsString('table-empty', $rendered);
        $this->assertStringContainsString('কোনো ডাটা পাওয়া যায়নি', $rendered);
        $this->assertStringContainsString('দয়া করে ফিল্টার পরিবর্তন করুন', $rendered);
    }

    public function test_core_namespaced_table_components_render(): void
    {
        $rendered = Blade::render('
            <x-core::table id="pos-items-table" datatable ajax-url="/api/pos/items" searchable>
                <x-slot:header>
                    <x-core::table.th icon="box">পণ্য</x-core::table.th>
                    <x-core::table.th align="right">অ্যাকশন</x-core::table.th>
                </x-slot:header>
                <x-core::table.tr>
                    <x-core::table.td>ল্যাপটপ</x-core::table.td>
                    <x-core::table.td actions>
                        <x-core::button size="xs" icon="edit" variant="soft">Edit</x-core::button>
                    </x-core::table.td>
                </x-core::table.tr>
            </x-core::table>
        ');

        $this->assertStringContainsString('id="pos-items-table"', $rendered);
        $this->assertStringContainsString('data-datatable="true"', $rendered);
        $this->assertStringContainsString('data-ajax-url="/api/pos/items"', $rendered);
        $this->assertStringContainsString('table-quick-search', $rendered);
        $this->assertStringContainsString('table-cell-actions', $rendered);
    }
}
