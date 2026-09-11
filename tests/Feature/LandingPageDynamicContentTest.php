<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Support\LandingPageContent;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LandingPageDynamicContentTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web', 'shop_id' => null]);
        $this->superAdmin = User::factory()->create(['email' => 'admin@pos.test']);
        $this->superAdmin->assignRole($superAdminRole);
    }

    public function test_super_admin_can_update_hero_section_and_it_reflects_on_landing_page(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('system-settings.update'), [
                'active_tab' => 'hero',
                'hero_badge_bn' => '🔥 বিশেষ অফার চলমান',
                'hero_title_bn' => 'আপনার ব্যবসার সেরা সহকারী',
                'hero_title_gradient_bn' => 'সুপার পিওএস',
                'hero_subtitle_bn' => 'সহজ হিসাব ও দ্রুত বিক্রি করুন সফটওয়্যার দিয়ে।',
                'hero_btn_primary_text_bn' => 'ফ্রি ডেমো দেখুন',
            ]);

        $response->assertRedirect(route('system-settings.index', ['tab' => 'hero']));

        $this->assertEquals('🔥 বিশেষ অফার চলমান', LandingPageContent::get('hero_badge_bn'));
        $this->assertEquals('আপনার ব্যবসার সেরা সহকারী', LandingPageContent::get('hero_title_bn'));

        // Visit public landing page
        $pageResponse = $this->get('/');
        $pageResponse->assertOk();
        $pageResponse->assertSee('🔥 বিশেষ অফার চলমান');
        $pageResponse->assertSee('আপনার ব্যবসার সেরা সহকারী');
        $pageResponse->assertSee('সুপার পিওএস');
        $pageResponse->assertSee('ফ্রি ডেমো দেখুন');
    }

    public function test_super_admin_can_update_stats_and_faqs_dynamically(): void
    {
        $customFaqs = [
            [
                'question_bn' => 'সফটওয়্যারটির মাসিক খরচ কত?',
                'question_en' => 'What is the monthly subscription price?',
                'answer_bn' => 'আমাদের স্ট্যান্ডার্ড প্ল্যান মাত্র ৯৯৯ টাকা থেকে শুরু।',
                'answer_en' => 'Our standard package begins at only 999 BDT/month.',
            ],
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('system-settings.update'), [
                'active_tab' => 'faqs',
                'stat_1_number' => '৯৯.৯৯%',
                'stat_1_label_bn' => 'ক্লাউড নির্ভরযোগ্যতা',
                'faqs_list' => $customFaqs,
            ]);

        $response->assertRedirect(route('system-settings.index', ['tab' => 'faqs']));

        $this->assertEquals('৯৯.৯৯%', LandingPageContent::get('stat_1_number'));

        // Visit landing page and assert updated stats and FAQs render
        $pageResponse = $this->get('/');
        $pageResponse->assertOk();
        $pageResponse->assertSee('৯৯.৯৯%');
        $pageResponse->assertSee('ক্লাউড নির্ভরযোগ্যতা');
        $pageResponse->assertSee('সফটওয়্যারটির মাসিক খরচ কত?');
        $pageResponse->assertSee('আমাদের স্ট্যান্ডার্ড প্ল্যান মাত্র ৯৯৯ টাকা থেকে শুরু।');
    }

    public function test_super_admin_can_update_reviews_and_cta(): void
    {
        $customReviews = [
            [
                'author' => 'কাজী আশরাফুল আলম',
                'shop' => 'আশরাফ জেনারেল স্টোর',
                'city' => 'বগুড়া',
                'quote_bn' => 'এসএনজিপস ব্যবহারে আমাদের প্রতিদিন ২ ঘণ্টা সময় বাঁচছে।',
                'quote_en' => 'SNGPOS saves us 2 hours daily.',
                'rating' => 5,
            ],
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('system-settings.update'), [
                'active_tab' => 'reviews',
                'cta_title_bn' => 'আজই বদলে ফেলুন আপনার ব্যবসার ভবিষ্যৎ',
                'cta_btn_text_bn' => 'এক্ষুনি জয়েন করুন',
                'reviews_list' => $customReviews,
            ]);

        $response->assertRedirect(route('system-settings.index', ['tab' => 'reviews']));

        $pageResponse = $this->get('/');
        $pageResponse->assertOk();
        $pageResponse->assertSee('কাজী আশরাফুল আলম');
        $pageResponse->assertSee('আশরাফ জেনারেল স্টোর');
        $pageResponse->assertSee('এসএনজিপস ব্যবহারে আমাদের প্রতিদিন ২ ঘণ্টা সময় বাঁচছে।');
        $pageResponse->assertSee('আজই বদলে ফেলুন আপনার ব্যবসার ভবিষ্যৎ');
        $pageResponse->assertSee('এক্ষুনি জয়েন করুন');
    }

    public function test_super_admin_can_update_stats_value_en_and_verticals_tag_and_desc_en(): void
    {
        $settingsView = $this->actingAs($this->superAdmin)
            ->get(route('system-settings.index', ['tab' => 'stats']));
        $settingsView->assertOk();
        $settingsView->assertSee('stat_1_number_en');
        $settingsView->assertSee('stat_2_number_en');
        $settingsView->assertSee('stat_3_number_en');
        $settingsView->assertSee('stat_4_number_en');

        $customVerticals = [
            [
                'icon' => 'shopping-cart',
                'name_bn' => 'মুদি দোকান',
                'name_en' => 'Grocery Shop',
                'tag_bn' => 'দ্রুত ক্যাশ মেমো',
                'tag_en' => 'Fast Checkout',
                'desc_bn' => 'মুদি দোকানের জন্য সম্পূর্ণ পিওএস সিস্টেম।',
                'desc_en' => 'Complete POS system tailored for grocery shops.',
            ],
        ];

        $response = $this->actingAs($this->superAdmin)
            ->post(route('system-settings.update'), [
                'active_tab' => 'stats',
                'stat_1_number' => '৯৯.৯%',
                'stat_1_number_en' => '99.9%',
                'stat_2_number' => '৫০,০০০+',
                'stat_2_number_en' => '50,000+',
                'stat_3_number' => '৩ সেকেন্ড',
                'stat_3_number_en' => '3s',
                'stat_4_number' => '২৪/৭',
                'stat_4_number_en' => '24/7',
                'verticals_list' => $customVerticals,
            ]);

        $response->assertRedirect(route('system-settings.index', ['tab' => 'stats']));

        $this->assertEquals('99.9%', LandingPageContent::get('stat_1_number_en'));
        $this->assertEquals('50,000+', LandingPageContent::get('stat_2_number_en'));
        $this->assertEquals('3s', LandingPageContent::get('stat_3_number_en'));
        $this->assertEquals('24/7', LandingPageContent::get('stat_4_number_en'));

        $vertList = LandingPageContent::get('verticals_list');
        $this->assertEquals('Fast Checkout', $vertList[0]['tag_en']);
        $this->assertEquals('Complete POS system tailored for grocery shops.', $vertList[0]['desc_en']);

        // Check verticals tab in settings page
        $vertView = $this->actingAs($this->superAdmin)
            ->get(route('system-settings.index', ['tab' => 'verticals']));
        $vertView->assertOk();
        $vertView->assertSee('Fast Checkout');
        $vertView->assertSee('Complete POS system tailored for grocery shops.');

        // Landing page in Bengali & English
        $pageResponse = $this->get('/');
        $pageResponse->assertOk();
        $pageResponse->assertSee('৯৯.৯%');
        $pageResponse->assertSee('99.9%');
        $pageResponse->assertSee('২৪/৭');
        $pageResponse->assertSee('24/7');
        $pageResponse->assertSee('দ্রুত ক্যাশ মেমো');
        $pageResponse->assertSee('Fast Checkout');
        $pageResponse->assertSee('মুদি দোকানের জন্য সম্পূর্ণ পিওএস সিস্টেম।');
        $pageResponse->assertSee('Complete POS system tailored for grocery shops.');
    }
}
