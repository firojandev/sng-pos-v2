<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('মাস্টারপস-এ লগইন করুন');
        $response->assertSee('Sign in to MasterPOS');
        $response->assertSee('ইমেইল');
        $response->assertSee('Email');
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
}
