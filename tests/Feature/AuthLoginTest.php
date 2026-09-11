<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('এসএনজিপস-এ লগইন করুন');
        $response->assertSee('Sign in to SNGPOS');
        $response->assertSee('ইউজারনেম');
        $response->assertSee('Username');
        $response->assertSee('পাসওয়ার্ড');
        $response->assertSee('Password');
        $response->assertSee('মনে রাখুন');
        $response->assertSee('Remember me');
        $response->assertSee('লগইন করুন');
        $response->assertSee('Sign In');
        $response->assertSee('placeholder="••••••••"', false);
        $response->assertDontSee('&bull;&bull;&bull;&bull;', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="remember"', false);
        $response->assertSee('type="submit"', false);
        $response->assertSee('btn-primary', false);
        $response->assertSee('id="theme-toggle"', false);
        $response->assertSee('id="lang-toggle"', false);
        $response->assertSee('form-toggle-wrap', false);
    }

    public function test_renders_auth_layout_component(): void
    {
        $rendered = Blade::render('<x-core::auth-layout card-title="Welcome Back"><p>Test Content</p></x-core::auth-layout>');

        $this->assertStringContainsString('auth-shell', $rendered);
        $this->assertStringContainsString('auth-card', $rendered);
        $this->assertStringContainsString('Welcome Back', $rendered);
        $this->assertStringContainsString('Test Content', $rendered);
    }

    public function test_login_validation_errors_render_properly(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_user_can_login_with_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => 'secret1234',
        ]);

        $response = $this->post('/login', [
            'email' => 'testuser@example.com',
            'password' => 'secret1234',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_login_with_username_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'sakib_pos',
            'email' => 'sakib@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => 'sakib_pos',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_phone_and_password(): void
    {
        $user = User::factory()->create([
            'phone' => '01712345678',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'email' => '01712345678',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_4_digit_pin(): void
    {
        $user = User::factory()->create([
            'username' => 'counter_operator',
            'pin' => '4321',
        ]);

        $response = $this->post('/login', [
            'email' => 'counter_operator',
            'password' => '4321',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_support_team_can_login_with_6_digit_support_pin(): void
    {
        $user = User::factory()->create([
            'email' => 'merchant@shop.com',
            'support_pin' => '987654',
        ]);

        $response = $this->post('/login', [
            'email' => 'merchant@shop.com',
            'password' => '987654',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(session('is_support_login'));
    }

    public function test_user_creation_auto_generates_6_digit_support_pin(): void
    {
        $user = User::create([
            'name' => 'Auto Support Pin User',
            'email' => 'autopin@example.com',
            'password' => 'password123',
        ]);

        $this->assertNotNull($user->support_pin);
        $this->assertEquals(6, strlen($user->support_pin));
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $user->support_pin);
    }

    public function test_user_pin_and_support_pin_are_totally_different(): void
    {
        $user = User::create([
            'name' => 'Dual Pin User',
            'email' => 'dualpin@example.com',
            'password' => 'password123',
            'pin' => '1234',
            'support_pin' => '654321',
        ]);

        // Secret of 4 digits matches user pin only
        $this->assertEquals('pin', $user->verifySecret('1234'));
        $this->assertFalse($user->verifySecret('6543'));

        // Secret of 6 digits matches support pin only
        $this->assertEquals('support_pin', $user->verifySecret('654321'));
        $this->assertFalse($user->verifySecret('123456'));
    }
}
