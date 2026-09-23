<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminCustomerManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_customer_search_uses_database_and_never_renders_password_hashes(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create([
            'name' => 'Mina Customer',
            'username' => 'mina-customer',
            'email' => 'mina@example.test',
            'password' => 'visible-marker-password',
        ]);
        User::factory()->admin()->create(['name' => 'Mina Administrator']);
        $passwordHash = $customer->password;

        $this->actingAs($admin)->getJson(route('admin.customers.index', ['q' => 'Mina']))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertSee('mina-customer')
            ->assertDontSee($passwordHash, false);

        $this->actingAs($admin)->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('mina@example.test')
            ->assertDontSee($passwordHash, false)
            ->assertDontSee('visible-marker-password', false);
    }

    public function test_admin_account_cannot_be_opened_through_customer_detail_route(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.customers.show', $otherAdmin))->assertNotFound();
    }
}
