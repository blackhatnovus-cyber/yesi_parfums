<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_search_and_view_complete_order_snapshot(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['name' => 'Order Customer']);
        $product = Product::factory()->create();
        $order = Order::factory()->for($customer)->create([
            'order_number' => 'IP-SEARCH-001',
            'shipping_name' => 'Shipping Person',
            'shipping_email' => 'ship@example.test',
            'shipping_address' => '123 Atelier Street, Makati City',
            'total' => 8500,
            'status' => 'pending',
        ]);
        OrderItem::factory()->for($order)->for($product)->create([
            'product_name' => 'Snapshot Scent',
            'unit_price' => 4250,
            'quantity' => 2,
            'subtotal' => 8500,
        ]);

        $this->actingAs($admin)->getJson(route('admin.orders.index', ['q' => 'SEARCH-001']))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertSee('IP-SEARCH-001');

        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Snapshot Scent')
            ->assertSee('ship@example.test')
            ->assertSee('123 Atelier Street, Makati City');
    }

    public function test_order_status_change_persists_only_allow_listed_values(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => 'pending']);

        $this->actingAs($admin)->patchJson(route('admin.orders.update', $order), ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('message', 'Order status updated successfully.')
            ->assertJsonPath('status', 'completed');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);

        $this->actingAs($admin)->patchJson(route('admin.orders.update', $order), ['status' => 'refunded'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }
}
