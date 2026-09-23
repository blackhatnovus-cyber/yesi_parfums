<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_from_account_pages(): void
    {
        $this->get(route('account.dashboard'))->assertRedirect(route('login'));
        $this->get(route('account.orders'))->assertRedirect(route('login'));
    }

    public function test_storefront_links_customer_to_account_and_account_provides_logout(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('shop'))
            ->assertSee('My Account')
            ->assertSee('href="'.route('account.dashboard').'"', false);

        $this->actingAs($customer)->get(route('account.dashboard'))
            ->assertSee('action="'.route('logout').'"', false);
    }

    public function test_storefront_links_admin_to_admin_dashboard_instead_of_customer_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('shop'))
            ->assertSee('Admin Dashboard')
            ->assertDontSee('My Account');
    }

    public function test_dashboard_shows_only_the_customers_orders_and_active_saved_fragrances(): void
    {
        $customer = User::factory()->create([
            'name' => 'Mina <script>alert(1)</script>',
            'username' => 'mina',
            'email' => 'mina@example.test',
        ]);
        $otherCustomer = User::factory()->create();
        $olderOrder = Order::factory()->for($customer)->create([
            'order_number' => 'IP-MINA-OLDER',
            'created_at' => now()->subDays(2),
        ]);
        $newerOrder = Order::factory()->for($customer)->create([
            'order_number' => 'IP-MINA-NEWER',
            'created_at' => now()->subDay(),
        ]);
        Order::factory()->for($otherCustomer)->create(['order_number' => 'IP-OTHER-ORDER']);

        $wishlist = Wishlist::factory()->for($customer)->create();
        $savedProduct = Product::factory()->create(['name' => 'Rain Study', 'status' => 'active']);
        $inactiveProduct = Product::factory()->create(['name' => 'Hidden Study', 'status' => 'inactive']);
        WishlistItem::factory()->for($wishlist)->for($savedProduct)->create();
        WishlistItem::factory()->for($wishlist)->for($inactiveProduct)->create();

        $response = $this->actingAs($customer)->get(route('account.dashboard'));

        $response->assertOk()
            ->assertSee('IP-MINA-NEWER')
            ->assertSee('IP-MINA-OLDER')
            ->assertSee('Rain Study')
            ->assertDontSee('IP-OTHER-ORDER')
            ->assertDontSee('Hidden Study')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertViewHas('recentOrders', fn ($orders): bool => $orders->pluck('id')->all() === [
                $newerOrder->id,
                $olderOrder->id,
            ])
            ->assertViewHas('orderCount', 2)
            ->assertViewHas('wishlistCount', 1);
    }

    public function test_new_customer_can_open_an_empty_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('account.dashboard'))
            ->assertOk()
            ->assertViewHas('orderCount', 0)
            ->assertViewHas('wishlistCount', 0);
    }

    public function test_order_history_is_paginated_newest_first_and_excludes_other_customers_orders(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        for ($number = 1; $number <= 9; $number++) {
            Order::factory()->for($customer)->create([
                'order_number' => sprintf('IP-MINA-%02d', $number),
                'created_at' => now()->subDays(10 - $number),
            ]);
        }

        Order::factory()->for($otherCustomer)->create(['order_number' => 'IP-OTHER-ORDER']);

        $this->actingAs($customer)->get(route('account.orders'))
            ->assertOk()
            ->assertSee('IP-MINA-09')
            ->assertDontSee('IP-MINA-01')
            ->assertDontSee('IP-OTHER-ORDER')
            ->assertViewHas('orders', fn ($orders): bool => $orders->total() === 9
                && $orders->pluck('order_number')->first() === 'IP-MINA-09');

        $this->actingAs($customer)->get(route('account.orders', ['page' => 2]))
            ->assertOk()
            ->assertSee('IP-MINA-01')
            ->assertDontSee('IP-MINA-09');
    }

    public function test_revisiting_an_order_shows_details_and_a_return_to_history(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create(['order_number' => 'IP-MINA-DETAIL']);

        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Order details')
            ->assertSee('Back to Order History')
            ->assertDontSee('Thank you,');
    }
}
