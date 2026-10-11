<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportPinVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_correct_support_pin_is_verified(): void
    {
        $user = User::factory()->create(['support_pin' => '482913']);

        $response = $this->actingAs($user)->postJson(route('support-pin.verify'), [
            'pin' => '482913',
        ]);

        $response->assertOk();
        $response->assertJson(['valid' => true]);
    }

    public function test_incorrect_support_pin_is_rejected(): void
    {
        $user = User::factory()->create(['support_pin' => '482913']);

        $response = $this->actingAs($user)->postJson(route('support-pin.verify'), [
            'pin' => '000000',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['valid' => false]);
    }

    public function test_another_users_support_pin_does_not_verify(): void
    {
        $owner = User::factory()->create(['support_pin' => '111111']);
        $viewer = User::factory()->create(['support_pin' => '222222']);

        $response = $this->actingAs($viewer)->postJson(route('support-pin.verify'), [
            'pin' => $owner->support_pin,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['valid' => false]);
    }

    public function test_guests_cannot_verify_a_support_pin(): void
    {
        $response = $this->postJson(route('support-pin.verify'), [
            'pin' => '482913',
        ]);

        $response->assertStatus(401);
    }

    public function test_pin_is_required(): void
    {
        $user = User::factory()->create(['support_pin' => '482913']);

        $response = $this->actingAs($user)->postJson(route('support-pin.verify'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pin']);
    }
}
