<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WishlistFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_wishlist_page_and_ajax_actions_require_authentication(): void
    {
        $product = Product::factory()->create();

        $this->get(route('wishlist.index'))->assertRedirect(route('login'));
        $this->postJson(route('wishlist.toggle', $product))
            ->assertUnauthorized()
            ->assertJsonPath('login_url', route('login'));
    }

    public function test_product_toggle_adds_then_removes_one_wishlist_record(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)->postJson(route('wishlist.toggle', $product))
            ->assertOk()
            ->assertJsonPath('active', true)
            ->assertJsonPath('wishlist_count', 1);
        $this->assertDatabaseHas('wishlist_items', ['product_id' => $product->id]);

        $this->actingAs($user)->postJson(route('wishlist.toggle', $product))
            ->assertOk()
            ->assertJsonPath('active', false)
            ->assertJsonPath('wishlist_count', 0);
        $this->assertDatabaseMissing('wishlist_items', ['product_id' => $product->id]);
    }

    public function test_owner_can_remove_wishlist_item_and_another_user_gets_404(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create();
        $wishlist = Wishlist::factory()->for($owner)->create();
        $item = WishlistItem::factory()->for($wishlist)->for($product)->create();

        $this->actingAs($otherUser)->deleteJson(route('wishlist.items.destroy', $item))->assertNotFound();
        $this->assertModelExists($item);

        $this->actingAs($owner)->deleteJson(route('wishlist.items.destroy', $item))
            ->assertOk()
            ->assertJsonPath('wishlist_count', 0);
        $this->assertModelMissing($item);
    }
}
