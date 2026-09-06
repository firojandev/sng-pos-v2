<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_profile_page(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'মোঃ সাকিব আল হাসান',
            'username' => 'sakib75',
            'email' => 'sakib@example.com',
            'phone' => '01711223344',
            'support_pin' => '456789',
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('প্রোফাইল পরিচালনা');
        $response->assertSee('Manage Profile');
        $response->assertSee('মোঃ সাকিব আল হাসান');
        $response->assertSee('sakib75');
        $response->assertSee('sakib@example.com');
        $response->assertSee('01711223344');
        $response->assertSee('456789');
    }

    public function test_topbar_renders_user_dropdown_menu(): void
    {
        $user = User::factory()->create([
            'name' => 'তানভীর আহমেদ',
            'username' => 'tanvir01',
            'email' => 'tanvir@example.com',
            'support_pin' => '123456',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('id="userMenuBtn"', false);
        $response->assertSee('id="userMenuDropdown"', false);
        $response->assertSee('তানভীর আহমেদ');
        $response->assertSee('tanvir01');
        $response->assertSee('#123456');
        $response->assertSee(route('profile.edit'));
        $response->assertSee(route('settings.index'));
        $response->assertSee(route('logout'));
        $response->assertSee('id="userMenuLogoutBtn"', false);
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'name' => 'পুরোনো নাম',
            'username' => 'old_user',
            'email' => 'old@example.com',
            'phone' => '01700000000',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'নতুন নাম',
            'username' => 'new_user',
            'email' => 'new@example.com',
            'phone' => '01899999999',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertSame('নতুন নাম', $user->name);
        $this->assertSame('new_user', $user->username);
        $this->assertSame('new@example.com', $user->email);
        $this->assertSame('01899999999', $user->phone);
    }

    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'old-password-123',
            'password' => 'new-secure-pass-456',
            'password_confirmation' => 'new-secure-pass-456',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-pass-456', $user->password));
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password-123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

        $response->assertSessionHasErrors('current_password');

        $user->refresh();
        $this->assertTrue(Hash::check('correct-password-123', $user->password));
    }

    public function test_user_can_update_pin_and_regenerate_support_pin(): void
    {
        $user = User::factory()->create([
            'support_pin' => '111222',
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'pin' => '9876',
            'regenerate_support_pin' => '1',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('9876', $user->pin));
        $this->assertNotSame('111222', $user->support_pin);
        $this->assertSame(6, strlen($user->support_pin));
    }
}
