<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthenticationFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_valid_username_and_password_create_session_and_redirect_to_shop(): void
    {
        $user = User::factory()->create([
            'username' => 'issey',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'username' => 'issey',
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertRedirect(route('shop'));
        $response->assertCookie(Auth::guard()->getRecallerName());
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_are_rejected_with_exact_message(): void
    {
        User::factory()->create(['username' => 'issey', 'password' => 'password']);

        $this->post(route('login.store'), ['username' => 'issey', 'password' => 'wrong'])
            ->assertRedirect()
            ->assertSessionHasErrors(['username' => 'Invalid username or password.']);

        $this->assertGuest();
    }

    public function test_empty_credentials_show_required_validation_messages(): void
    {
        $this->post(route('login.store'), [])
            ->assertSessionHasErrors([
                'username' => 'Username is required.',
                'password' => 'Password is required.',
            ]);
    }

    public function test_logout_invalidates_authentication_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
