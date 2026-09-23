<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CartFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_cart_page_and_ajax_actions_require_authentication(): void
    {
        $product = Product::factory()->create();

        $this->get(route('cart.index'))->assertRedirect(route('login'));
        $this->postJson(route('cart.items.store', $product), ['quantity' => 1])
            ->assertUnauthorized()
            ->assertJsonPath('login_url', route('login'));
    }

    public function test_repeated_add_upserts_one_item_and_increases_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user)->postJson(route('cart.items.store', $product), ['quantity' => 1])
            ->assertOk()
            ->assertJsonPath('message', 'Product added to cart.')
            ->assertJsonPath('cart_count', 1);
        $this->actingAs($user)->postJson(route('cart.items.store', $product), ['quantity' => 1])
            ->assertOk()
            ->assertJsonPath('cart_count', 2);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_add_and_update_reject_quantities_above_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 1]);

        $this->actingAs($user)->postJson(route('cart.items.store', $product), ['quantity' => 2])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('quantity');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
    }

    public function test_owner_can_update_and_remove_item_but_another_user_gets_404(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create(['stock' => 8]);
        $cart = Cart::factory()->for($owner)->create();
        $item = CartItem::factory()->for($cart)->for($product)->create(['quantity' => 1]);

        $this->actingAs($otherUser)->patchJson(route('cart.items.update', $item), ['quantity' => 2])
            ->assertNotFound();

        $this->actingAs($owner)->patchJson(route('cart.items.update', $item), ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('cart_count', 3);
        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 3]);

        $this->actingAs($owner)->deleteJson(route('cart.items.destroy', $item))
            ->assertOk()
            ->assertJsonPath('cart_count', 0);
        $this->assertModelMissing($item);
    }
}
