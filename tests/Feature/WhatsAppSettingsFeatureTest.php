<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kstmostofa\LaravelWhatsApp\Web\Resources\MessagesResource;
use Kstmostofa\LaravelWhatsApp\Web\WebClient;
use Kstmostofa\LaravelWhatsApp\Web\WebSession;
use Mockery;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Database\Seeders\SubscriptionifySeeder;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WhatsAppSettingsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Shop $shop;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        (new SubscriptionifySeeder)->run();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->shop = Shop::create([
            'name' => 'মদিনা ট্রেডার্স',
            'slug' => 'madina-traders',
            'phone' => '01712345678',
            'address' => 'ঢাকা, বাংলাদেশ',
            'status' => 'active',
        ]);

        $plan = Plan::where('slug', 'enterprise')->first() ?? Plan::first();
        if ($plan) {
            $this->shop->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addYear(),
                'trial_ends_at' => null,
                'features' => Features::all(),
            ]);
        }

        $branch = Branch::create([
            'shop_id' => $this->shop->id,
            'name' => 'Main Branch',
            'status' => 'active',
            'is_main' => true,
        ]);

        Warehouse::create([
            'shop_id' => $this->shop->id,
            'branch_id' => $branch->id,
            'name' => 'Main Warehouse',
            'status' => 'active',
            'is_default' => true,
        ]);

        $this->user = User::factory()->create([
            'shop_id' => $this->shop->id,
            'email' => 'admin@madina.com',
        ]);
        $this->user->assignRole($adminRole);
    }

    public function test_whatsapp_settings_page_is_accessible(): void
    {
        $response = $this->actingAs($this->user)->get(route('whatsapp-settings.index'));

        $response->assertOk();
        $response->assertSee('হোয়াটসঅ্যাপ সেটিংস');
        $response->assertSee('WhatsApp Settings');
        $response->assertSee('ব্যক্তিগত হোয়াটসঅ্যাপ অ্যাকাউন্ট');
        $response->assertSee('Personal WhatsApp Account');
        $response->assertSee('মোবাইল থেকে কিভাবে স্ক্যান করবেন?');
        $response->assertSee('How to scan from mobile?');
        $response->assertSee('Linked Devices');
    }

    public function test_sidebar_contains_whatsapp_settings_link(): void
    {
        $response = $this->actingAs($this->user)->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee(route('whatsapp-settings.index'));
        $response->assertSee('হোয়াটসঅ্যাপ সেটিংস');
    }

    public function test_whatsapp_status_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('whatsapp-settings.status'));

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'is_running',
            'status',
        ]);
    }

    public function test_whatsapp_test_message_validates_phone_number(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('whatsapp-settings.test'), [
            'phone' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_whatsapp_test_message_sends_and_stores_in_database(): void
    {
        $mockMessagesResource = Mockery::mock(MessagesResource::class);
        $mockMessagesResource->shouldReceive('sendText')
            ->once()
            ->with('+8801712345678', Mockery::pattern('/SNG POS/'))
            ->andReturn(['id' => 'test-wa-msg-999']);

        $mockWebSession = Mockery::mock(WebSession::class);
        $mockWebSession->shouldReceive('state')
            ->once()
            ->andReturn(['status' => 'ready']);
        $mockWebSession->shouldReceive('messages')
            ->once()
            ->andReturn($mockMessagesResource);

        $mockWebClient = Mockery::mock(WebClient::class);
        $mockWebClient->shouldReceive('session')
            ->withAnyArgs()
            ->andReturn($mockWebSession);

        $this->app->instance(WebClient::class, $mockWebClient);

        $response = $this->actingAs($this->user)->postJson(route('whatsapp-settings.test'), [
            'phone' => '01712345678',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('wa_messages', [
            'shop_id' => $this->shop->id,
            'direction' => 'outbound',
            'to_id' => '+8801712345678',
            'wa_message_id' => 'test-wa-msg-999',
            'status' => 'sent',
        ]);
    }

    public function test_whatsapp_disconnect_destroys_session(): void
    {
        $mockWebSession = Mockery::mock(WebSession::class);
        $mockWebSession->shouldReceive('destroy')
            ->once()
            ->andReturnNull();

        $mockWebClient = Mockery::mock(WebClient::class);
        $mockWebClient->shouldReceive('session')
            ->withAnyArgs()
            ->andReturn($mockWebSession);

        $this->app->instance(WebClient::class, $mockWebClient);

        $response = $this->actingAs($this->user)->postJson(route('whatsapp-settings.disconnect'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
    }
}
