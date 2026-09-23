<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_are_redirected_and_customers_are_forbidden_from_admin_boundaries(): void
    {
        $category = Category::factory()->create();
        $payload = [
            'name' => 'Restricted Formula',
            'category_id' => $category->id,
            'description' => 'A product a customer must not be able to create.',
            'price' => 2000,
            'stock' => 4,
            'status' => 'active',
        ];

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->post(route('admin.products.store'), $payload)->assertRedirect(route('login'));

        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('Unauthorized.');
        $this->actingAs($customer)->post(route('admin.products.store'), $payload)->assertForbidden();
        $this->assertDatabaseMissing('products', ['name' => 'Restricted Formula']);
    }

    public function test_admin_can_access_dashboard_and_each_management_index(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store, private');
        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.customers.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.messages.index'))->assertOk();
    }

    public function test_login_redirects_by_role_and_rejects_inactive_accounts(): void
    {
        $admin = User::factory()->admin()->create(['username' => 'admin-user', 'password' => 'password']);
        $customer = User::factory()->create(['username' => 'customer-user', 'password' => 'password']);
        User::factory()->inactive()->create(['username' => 'inactive-user', 'password' => 'password']);

        $this->post(route('login.store'), ['username' => 'admin-user', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $this->post(route('logout'));

        $this->post(route('login.store'), ['username' => 'customer-user', 'password' => 'password'])
            ->assertRedirect(route('shop'));
        $this->assertAuthenticatedAs($customer);

        $this->post(route('logout'));

        $this->post(route('login.store'), ['username' => 'inactive-user', 'password' => 'password'])
            ->assertSessionHasErrors(['username' => 'Invalid username or password.']);

        $this->assertGuest();
    }

    public function test_admin_login_normalizes_username_clears_failed_attempts_and_persists_the_session(): void
    {
        $admin = User::factory()->admin()->create(['username' => 'admin-user', 'password' => 'password']);

        $this->post(route('login.store'), ['username' => ' admin-user ', 'password' => 'incorrect'])
            ->assertSessionHasErrors(['username' => 'Invalid username or password.']);

        $this->post(route('login.store'), ['username' => ' admin-user ', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
        $this->assertSame(0, RateLimiter::attempts('login:admin-user|127.0.0.1'));
    }

    public function test_repeated_failed_login_attempts_are_scoped_and_rate_limited(): void
    {
        User::factory()->admin()->create(['username' => 'limited-admin', 'password' => 'password']);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login.store'), ['username' => 'limited-admin', 'password' => 'incorrect'])
                ->assertSessionHasErrors(['username' => 'Invalid username or password.']);
        }

        $this->post(route('login.store'), ['username' => 'limited-admin', 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $this->assertStringStartsWith(
            'Too many login attempts. Try again in ',
            session('errors')->first('username'),
        );

        $this->assertGuest();
    }
}
