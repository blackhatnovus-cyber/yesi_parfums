<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_open_registration_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee(route('register.store'));
    }

    public function test_valid_registration_creates_active_customer_then_requires_manual_login(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => '  Mika Santos  ',
            'username' => '  mika_santos  ',
            'email' => '  MIKA@example.test  ',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'role' => 'admin',
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Account created successfully. Please log in with your new username and password.');
        $this->assertDatabaseHas('users', [
            'name' => 'Mika Santos',
            'username' => 'mika_santos',
            'email' => 'mika@example.test',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $user = User::query()->where('username', 'mika_santos')->firstOrFail();
        $this->assertTrue(Hash::check('StrongPass123!', $user->password));

        $this->assertGuest();
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Account created successfully. Please log in with your new username and password.');

        $this->post(route('login.store'), [
            'username' => 'mika_santos',
            'password' => 'StrongPass123!',
        ])->assertRedirect(route('shop'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_registration_rejects_missing_fields_and_password_confirmation(): void
    {
        $this->post(route('register.store'), [])
            ->assertSessionHasErrors(['name', 'username', 'email', 'password']);

        $this->post(route('register.store'), [
            'name' => 'Mika Santos',
            'username' => 'mika_santos',
            'email' => 'mika@example.test',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'DifferentPass123!',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_registration_rejects_duplicate_username_and_email(): void
    {
        User::factory()->create([
            'username' => 'existing_user',
            'email' => 'existing@example.test',
        ]);

        $this->post(route('register.store'), [
            'name' => 'Another Customer',
            'username' => ' EXISTING_USER ',
            'email' => ' EXISTING@EXAMPLE.TEST ',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ])->assertSessionHasErrors(['username', 'email']);

        $this->assertDatabaseCount('users', 1);
        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_username_email_and_short_password(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Mika Santos',
            'username' => 'bad user name',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['username', 'email', 'password']);

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_open_or_submit_registration(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('register'))->assertRedirect(route('shop'));
        $this->actingAs($user)->post(route('register.store'), [
            'name' => 'Second Customer',
            'username' => 'second_customer',
            'email' => 'second@example.test',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ])->assertRedirect(route('shop'));

        $this->assertDatabaseCount('users', 1);
    }
}
