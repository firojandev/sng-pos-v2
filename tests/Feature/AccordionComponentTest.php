<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AccordionComponentTest extends TestCase
{
    public function test_renders_basic_accordion(): void
    {
        $rendered = Blade::render('
            <x-core::accordion title="সাধারণ তথ্য" title-en="General Info" icon="info">
                <p>Accordion content goes here</p>
            </x-core::accordion>
        ');

        $this->assertStringContainsString('data-accordion', $rendered);
        $this->assertStringContainsString('data-accordion-trigger', $rendered);
        $this->assertStringContainsString('data-accordion-content', $rendered);
        $this->assertStringContainsString('সাধারণ তথ্য', $rendered);
        $this->assertStringContainsString('General Info', $rendered);
        $this->assertStringContainsString('Accordion content goes here', $rendered);
        $this->assertStringContainsString('display:none;', $rendered);
    }

    public function test_renders_checkbox_accordion(): void
    {
        $rendered = Blade::render('
            <x-core::accordion
                id="vat-box"
                name="is_vat"
                value="1"
                :checked="true"
                title="এই পণ্যে ভ্যাট প্রযোজ্য (VAT Applicable)"
                title-en="VAT Applicable on this product"
            >
                <input type="number" name="vat_percentage" value="15" />
            </x-core::accordion>
        ');

        $this->assertStringContainsString('id="vat-box"', $rendered);
        $this->assertStringContainsString('name="is_vat"', $rendered);
        $this->assertStringContainsString('value="1"', $rendered);
        $this->assertStringContainsString('checked', $rendered);
        $this->assertStringContainsString('is-open active', $rendered);
        $this->assertStringContainsString('data-accordion-checkbox', $rendered);
        $this->assertStringContainsString('এই পণ্যে ভ্যাট প্রযোজ্য (VAT Applicable)', $rendered);
        $this->assertStringContainsString('VAT Applicable on this product', $rendered);
        $this->assertStringContainsString('name="vat_percentage"', $rendered);
        $this->assertStringContainsString('data-accordion-content', $rendered);
        $this->assertStringContainsString('style=""', $rendered);
    }

    public function test_renders_accordion_with_toggle_type(): void
    {
        $rendered = Blade::render('
            <x-core::accordion
                type="toggle"
                name="enable_feature"
                title="ফিচার সক্রিয় করুন"
                title-en="Enable Feature"
                color="gold"
                :checked="false"
            >
                <div>Toggle content</div>
            </x-core::accordion>
        ');

        $this->assertStringContainsString('form-toggle-wrap', $rendered);
        $this->assertStringContainsString('name="enable_feature"', $rendered);
        $this->assertStringContainsString('form-gold', $rendered);
        $this->assertStringContainsString('ফিচার সক্রিয় করুন', $rendered);
        $this->assertStringContainsString('Enable Feature', $rendered);
        $this->assertStringContainsString('display:none;', $rendered);
    }

    public function test_renders_accordion_with_badge_and_group(): void
    {
        $rendered = Blade::render('
            <x-core::accordion
                title="প্যাকেজ অপশন"
                title-en="Package Options"
                badge="নতুন"
                badge-en="New"
                badge-color="teal"
                group="faq-group"
                :open="true"
            >
                <div>Package options body</div>
            </x-core::accordion>
        ');

        $this->assertStringContainsString('data-accordion-group="faq-group"', $rendered);
        $this->assertStringContainsString('badge-teal', $rendered);
        $this->assertStringContainsString('নতুন', $rendered);
        $this->assertStringContainsString('New', $rendered);
        $this->assertStringContainsString('is-open active', $rendered);
    }

    public function test_renders_root_namespaced_accordion(): void
    {
        $rendered = Blade::render('
            <x-accordion name="is_discount" :checked="true" title="ছাড় সুবিধা" title-en="Discount">
                <span>Discount inputs</span>
            </x-accordion>
        ');

        $this->assertStringContainsString('data-accordion', $rendered);
        $this->assertStringContainsString('name="is_discount"', $rendered);
        $this->assertStringContainsString('ছাড় সুবিধা', $rendered);
        $this->assertStringContainsString('Discount', $rendered);
        $this->assertStringContainsString('Discount inputs', $rendered);
    }
}
