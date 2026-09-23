<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CheckoutFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_checkout_creates_price_snapshots_decrements_stock_and_clears_cart(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Checkout Study',
            'price' => '4250.75',
            'stock' => 12,
            'status' => 'active',
        ]);
        $cart = Cart::factory()->for($customer)->create();
        CartItem::factory()->for($cart)->for($product)->create(['quantity' => 2]);

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_name' => 'Mina Customer',
            'shipping_email' => 'mina@example.test',
            'shipping_address' => '123 Fragrance Lane, Makati City',
        ]);

        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('status', 'Order placed successfully. No payment was processed.');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $customer->id,
            'shipping_email' => 'mina@example.test',
            'total' => '8501.50',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Checkout Study',
            'unit_price' => '4250.75',
            'quantity' => 2,
            'subtotal' => '8501.50',
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 10]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_rejects_empty_out_of_stock_and_disabled_carts_without_orders(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_name' => 'Mina Customer',
            'shipping_email' => 'mina@example.test',
            'shipping_address' => '123 Fragrance Lane, Makati City',
        ])->assertSessionHasErrors('cart');

        $outOfStockProduct = Product::factory()->create(['stock' => 1]);
        $cart = Cart::factory()->for($customer)->create();
        $item = CartItem::factory()->for($cart)->for($outOfStockProduct)->create(['quantity' => 2]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_name' => 'Mina Customer',
            'shipping_email' => 'mina@example.test',
            'shipping_address' => '123 Fragrance Lane, Makati City',
        ])->assertSessionHasErrors('cart');

        $item->update(['quantity' => 1]);
        $outOfStockProduct->update(['status' => 'inactive']);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'shipping_name' => 'Mina Customer',
            'shipping_email' => 'mina@example.test',
            'shipping_address' => '123 Fragrance Lane, Makati City',
        ])->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('products', ['id' => $outOfStockProduct->id, 'stock' => 1]);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $order = Order::factory()->for($owner)->create();

        $this->actingAs($otherCustomer)->get(route('orders.show', $order))->assertNotFound();
        $this->actingAs($owner)->get(route('orders.show', $order))->assertOk();
    }
}
