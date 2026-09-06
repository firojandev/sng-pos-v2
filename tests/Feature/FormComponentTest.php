<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class FormComponentTest extends TestCase
{
    public function test_renders_input_component(): void
    {
        $rendered = Blade::render('<x-input name="user_name" placeholder="Enter name" icon="user" required />');

        $this->assertStringContainsString('name="user_name"', $rendered);
        $this->assertStringContainsString('placeholder="Enter name"', $rendered);
        $this->assertStringContainsString('form-control', $rendered);
        $this->assertStringContainsString('form-input-group', $rendered);
        $this->assertStringContainsString('required', $rendered);
    }

    public function test_renders_input_with_addons_and_icons(): void
    {
        $rendered = Blade::render('<x-input name="amount" prefix="৳" suffix=".00" clearable />');

        $this->assertStringContainsString('form-input-addon-left', $rendered);
        $this->assertStringContainsString('৳', $rendered);
        $this->assertStringContainsString('.00', $rendered);
        $this->assertStringContainsString('form-input-btn', $rendered);
    }

    public function test_renders_number_input_with_modern_stepper(): void
    {
        $rendered = Blade::render('<x-core::input type="number" name="quantity" value="5" min="1" max="100" />');

        $this->assertStringContainsString('type="number"', $rendered);
        $this->assertStringContainsString('has-stepper', $rendered);
        $this->assertStringContainsString('form-input-stepper', $rendered);
        $this->assertStringContainsString('form-stepper-up', $rendered);
        $this->assertStringContainsString('form-stepper-down', $rendered);
    }

    public function test_renders_select_component(): void
    {
        $opts = ['active' => 'Active', 'inactive' => 'Inactive'];
        $rendered = Blade::render('<x-select name="status" :options="$opts" placeholder="Choose status" />', ['opts' => $opts]);

        $this->assertStringContainsString('name="status"', $rendered);
        $this->assertStringContainsString('Choose status', $rendered);
        $this->assertStringContainsString('value="active"', $rendered);
        $this->assertStringContainsString('Active', $rendered);
    }

    public function test_select_component_parses_bilingual_options_without_displaying_dual_text(): void
    {
        $opts = [
            '' => 'সকল ধরন (All Types)',
            'cash' => 'নগদ (Cash)',
            'bank' => 'ব্যাংক (Bank)',
        ];
        $rendered = Blade::render('<x-core::select name="type" :options="$opts" placeholder="-- নির্বাচন করুন (Select Type) --" />', ['opts' => $opts]);

        $this->assertStringContainsString('data-text-bn="সকল ধরন"', $rendered);
        $this->assertStringContainsString('data-text-en="All Types"', $rendered);
        $this->assertStringContainsString('data-text-bn="নগদ"', $rendered);
        $this->assertStringContainsString('data-text-en="Cash"', $rendered);
        $this->assertStringContainsString('data-text-bn="-- নির্বাচন করুন --"', $rendered);
        $this->assertStringContainsString('data-text-en="-- Select Type --"', $rendered);

        // Crucial: Option text itself should not show the English parenthetical simultaneously
        $this->assertStringNotContainsString('>সকল ধরন (All Types)<', $rendered);
        $this->assertStringNotContainsString('>নগদ (Cash)<', $rendered);
        $this->assertStringContainsString('>সকল ধরন<', $rendered);
        $this->assertStringContainsString('>নগদ<', $rendered);
    }

    public function test_renders_textarea_component(): void
    {
        $rendered = Blade::render('<x-textarea name="address" placeholder="Address..." rows="4" show-count max-length="200">Dhaka, Bangladesh</x-textarea>');

        $this->assertStringContainsString('name="address"', $rendered);
        $this->assertStringContainsString('rows="4"', $rendered);
        $this->assertStringContainsString('form-textarea-count', $rendered);
        $this->assertStringContainsString('Dhaka, Bangladesh', $rendered);
    }

    public function test_renders_checkbox_component(): void
    {
        $rendered = Blade::render('<x-checkbox name="terms" value="yes" label="I accept terms" color="gold" checked />');

        $this->assertStringContainsString('type="checkbox"', $rendered);
        $this->assertStringContainsString('value="yes"', $rendered);
        $this->assertStringContainsString('I accept terms', $rendered);
        $this->assertStringContainsString('form-check', $rendered);
        $this->assertStringContainsString('checked', $rendered);
    }

    public function test_renders_toggle_component(): void
    {
        $rendered = Blade::render('<x-toggle name="notify" label="Enable Alerts" color="teal" checked />');

        $this->assertStringContainsString('form-toggle-wrap', $rendered);
        $this->assertStringContainsString('form-toggle-track', $rendered);
        $this->assertStringContainsString('form-toggle-thumb', $rendered);
        $this->assertStringContainsString('Enable Alerts', $rendered);
        $this->assertStringContainsString('checked', $rendered);
    }

    public function test_renders_toggle_with_dynamic_on_off_labels_and_icons(): void
    {
        $rendered = Blade::render('<x-toggle id="theme-toggle" icon-on="moon" icon-off="sun" label-off="লাইট" label-off-en="Light" label-on="ডার্ক" label-on-en="Dark" color="gold" />');

        $this->assertStringContainsString('toggle-label-off', $rendered);
        $this->assertStringContainsString('toggle-label-on', $rendered);
        $this->assertStringContainsString('toggle-icon-off', $rendered);
        $this->assertStringContainsString('toggle-icon-on', $rendered);
        $this->assertStringContainsString('লাইট', $rendered);
        $this->assertStringContainsString('Light', $rendered);
        $this->assertStringContainsString('ডার্ক', $rendered);
        $this->assertStringContainsString('Dark', $rendered);
    }

    public function test_renders_radio_component(): void
    {
        $rendered = Blade::render('<x-radio name="gender" value="female" label="Female" color="blue" checked />');

        $this->assertStringContainsString('type="radio"', $rendered);
        $this->assertStringContainsString('value="female"', $rendered);
        $this->assertStringContainsString('Female', $rendered);
        $this->assertStringContainsString('form-radio-wrap', $rendered);
    }

    public function test_renders_radio_card_component(): void
    {
        $rendered = Blade::render('<x-radio-card name="pay" value="cash" title="Cash On Delivery" icon="cash" badge="Popular" checked />');

        $this->assertStringContainsString('form-radio-card', $rendered);
        $this->assertStringContainsString('Cash On Delivery', $rendered);
        $this->assertStringContainsString('card-badge', $rendered);
        $this->assertStringContainsString('Popular', $rendered);
    }

    public function test_renders_core_namespaced_form_components(): void
    {
        $rendered = Blade::render('<x-core::input name="sku" label="Product SKU" icon="barcode" color="teal" />');

        $this->assertStringContainsString('Product SKU', $rendered);
        $this->assertStringContainsString('name="sku"', $rendered);
        $this->assertStringContainsString('form-label', $rendered);
        $this->assertStringContainsString('form-control', $rendered);
    }

    public function test_styleguide_view_renders_with_form_showcase(): void
    {
        $rendered = view('core::pages.styleguide')->render();

        $this->assertStringContainsString('UI কম্পোনেন্ট ও ফর্ম স্টাইল গাইড', $rendered);
        $this->assertStringContainsString('sg-sidebar', $rendered);
        $this->assertStringContainsString('ইন্টারেক্টিভ ইনপুট প্লে-গ্রাউন্ড', $rendered);
        $this->assertStringContainsString('সিলেক্ট ড্রপডাউন প্লে-গ্রাউন্ড', $rendered);
        $this->assertStringContainsString('কাস্টম চেকবক্স প্লে-গ্রাউন্ড', $rendered);
        $this->assertStringContainsString('সুইচ ও টগল প্লে-গ্রাউন্ড', $rendered);
        $this->assertStringContainsString('রেডিও বাটন ও সিলেকশন কার্ড প্লে-গ্রাউন্ড', $rendered);
    }

    public function test_topbar_renders_with_theme_and_lang_toggles(): void
    {
        $rendered = Blade::render('<x-core::topbar title="ড্যাশবোর্ড" title-en="Dashboard" />');

        $this->assertStringContainsString('topbar-switchers', $rendered);
        $this->assertStringContainsString('id="theme-toggle"', $rendered);
        $this->assertStringContainsString('id="lang-toggle"', $rendered);
        $this->assertStringContainsString('ড্যাশবোর্ড', $rendered);
        $this->assertStringContainsString('Dashboard', $rendered);
    }

    public function test_renders_theme_switcher_with_left_light_center_toggle_and_right_dark(): void
    {
        $rendered = Blade::render('<x-core::theme-switcher />');

        $this->assertStringContainsString('theme-segmented-switcher', $rendered);
        $this->assertStringContainsString('switch-opt-light', $rendered);
        $this->assertStringContainsString('id="theme-toggle"', $rendered);
        $this->assertStringContainsString('switch-opt-dark', $rendered);
        $this->assertStringContainsString('লাইট', $rendered);
        $this->assertStringContainsString('ডার্ক', $rendered);
    }

    public function test_renders_lang_switcher_with_left_bn_center_toggle_and_right_en(): void
    {
        $rendered = Blade::render('<x-core::lang-switcher />');

        $this->assertStringContainsString('lang-segmented-switcher', $rendered);
        $this->assertStringContainsString('switch-opt-bn', $rendered);
        $this->assertStringContainsString('id="lang-toggle"', $rendered);
        $this->assertStringContainsString('switch-opt-en', $rendered);
        $this->assertStringContainsString('বাং', $rendered);
        $this->assertStringContainsString('EN', $rendered);
    }

    public function test_renders_status_toggle_component_active(): void
    {
        $rendered = Blade::render('<x-core::status-toggle name="status" value="active" />');

        $this->assertStringContainsString('status-segmented-switcher', $rendered);
        $this->assertStringContainsString('data-status-switcher', $rendered);
        $this->assertStringContainsString('switch-opt-active active', $rendered);
        $this->assertStringContainsString('data-status-opt="active"', $rendered);
        $this->assertStringContainsString('data-status-opt="inactive"', $rendered);
        $this->assertStringContainsString('name="status"', $rendered);
        $this->assertStringContainsString('value="active"', $rendered);
        $this->assertStringContainsString('is-active', $rendered);
    }

    public function test_renders_status_toggle_component_inactive(): void
    {
        $rendered = Blade::render('<x-status-toggle name="status" value="inactive" active-label="সক্রিয়" inactive-label="নিষ্ক্রিয়" />');

        $this->assertStringContainsString('status-segmented-switcher', $rendered);
        $this->assertStringContainsString('switch-opt-inactive active', $rendered);
        $this->assertStringContainsString('checked', $rendered);
        $this->assertStringContainsString('value="inactive"', $rendered);
        $this->assertStringContainsString('is-inactive', $rendered);
    }
}
