<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Middleware\ConvertBengaliNumbers;
use Modules\Core\Support\BanglaNumber;
use Tests\TestCase;

class EnglishNumberInputTest extends TestCase
{
    public function test_bangla_number_to_en_converts_bengali_digits_to_english(): void
    {
        $this->assertSame('0123456789', BanglaNumber::toEn('০১২৩৪৫৬৭৮৯'));
        $this->assertSame('1250.75', BanglaNumber::toEn('১২৫০.৭৫'));
        $this->assertSame('1250.75', BanglaNumber::toEn('১২৫০।৭৫'));
        $this->assertSame('+8801712345678', BanglaNumber::toEn('+৮৮০১৭১২৩৪৫৬৭৮'));
        $this->assertSame('', BanglaNumber::toEn(''));
        $this->assertSame('', BanglaNumber::toEn(null));
        $this->assertSame('100', BanglaNumber::toEn(100));
    }

    public function test_input_component_sets_lang_and_dir_and_converts_number_values_to_english(): void
    {
        $rendered = Blade::render('<x-core::input type="number" name="price" value="১২৫০.৫০" placeholder="০.০০" />');

        $this->assertStringContainsString('type="number"', $rendered);
        $this->assertStringContainsString('value="1250.50"', $rendered);
        $this->assertStringContainsString('placeholder="0.00"', $rendered);
        $this->assertStringContainsString('lang="en"', $rendered);
        $this->assertStringContainsString('dir="ltr"', $rendered);
    }

    public function test_input_component_converts_tel_values_to_english(): void
    {
        $rendered = Blade::render('<x-core::input type="tel" name="phone" value="০১৭১২৩৪৫৬৭৮" />');

        $this->assertStringContainsString('type="tel"', $rendered);
        $this->assertStringContainsString('value="01712345678"', $rendered);
        $this->assertStringContainsString('lang="en"', $rendered);
        $this->assertStringContainsString('dir="ltr"', $rendered);
    }

    public function test_middleware_converts_bengali_numbers_in_post_requests(): void
    {
        Route::post('/test-number-conversion', function (Request $request) {
            return response()->json($request->all());
        })->middleware(ConvertBengaliNumbers::class);

        $response = $this->postJson('/test-number-conversion', [
            'purchase_price' => '১২৫০.৭৫',
            'alert_qty' => '১০',
            'customer_phone' => '০১৭১২৩৪৫৬৭৮',
            'discount' => '৫.০০',
            'items' => [
                ['quantity' => '৩', 'price' => '৪৫০.০০'],
                ['quantity' => '২', 'price' => '২০০.০০'],
            ],
            'notes' => 'চালান নম্বর ১২৩৪ বিবরণ',
            'password' => 'secret১২৩৪',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('1250.75', $data['purchase_price']);
        $this->assertSame('10', $data['alert_qty']);
        $this->assertSame('01712345678', $data['customer_phone']);
        $this->assertSame('5.00', $data['discount']);
        $this->assertSame('3', $data['items'][0]['quantity']);
        $this->assertSame('450.00', $data['items'][0]['price']);
        $this->assertSame('2', $data['items'][1]['quantity']);
        $this->assertSame('200.00', $data['items'][1]['price']);
        // Plain text with mixed words preserves Bengali
        $this->assertSame('চালান নম্বর ১২৩৪ বিবরণ', $data['notes']);
        // Password is never modified
        $this->assertSame('secret১২৩৪', $data['password']);
    }
}
