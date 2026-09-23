<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_product_with_validated_public_image_upload(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Rain Floral']);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Rain Ledger',
            'category_id' => $category->id,
            'description' => 'Iris and mineral rain arranged in a quiet composition.',
            'price' => '4200.50',
            'stock' => 9,
            'status' => 'active',
            'image' => UploadedFile::fake()->image('rain-ledger.webp'),
        ]);

        $response->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', 'Product created successfully.');
        $product = Product::query()->where('name', 'Rain Ledger')->firstOrFail();
        $this->assertSame('Rain Floral', $product->category);
        $this->assertSame($category->id, $product->category_id);
        $this->assertStringStartsWith('/storage/product-images/', $product->image);
        Storage::disk('public')->assertExists(Str::after($product->image, '/storage/'));
    }

    public function test_product_validation_rejects_invalid_fields_without_database_write(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => '',
            'category_id' => 999999,
            'description' => '',
            'price' => -1,
            'stock' => -1,
            'status' => 'hidden',
        ])->assertSessionHasErrors(['name', 'category_id', 'description', 'price', 'stock', 'status']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_update_synchronizes_category_preserves_image_and_inactive_products_are_hidden_everywhere(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $oldCategory = Category::factory()->create(['name' => 'Old Family']);
        $newCategory = Category::factory()->create(['name' => 'New Family']);
        $product = Product::factory()->create([
            'name' => 'Hidden Formula',
            'category' => $oldCategory->name,
            'category_id' => $oldCategory->id,
            'image' => '/images/noir-essence.jpg',
        ]);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Hidden Formula',
            'category_id' => $newCategory->id,
            'description' => 'An updated description for this private formula.',
            'price' => 5600,
            'stock' => 3,
            'status' => 'inactive',
        ])->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', 'Product updated successfully.');

        $product->refresh();
        $this->assertSame('New Family', $product->category);
        $this->assertSame('/images/noir-essence.jpg', $product->image);
        $this->get(route('home'))->assertDontSee('Hidden Formula');
        $this->get(route('shop'))->assertDontSee('Hidden Formula');
        $this->getJson(route('products.search', ['q' => 'Hidden Formula']))->assertJsonPath('count', 0);
        $this->get(route('products.show', $product))->assertNotFound();
        $this->actingAs($customer)->postJson(route('cart.items.store', $product))->assertNotFound();
        $this->actingAs($customer)->postJson(route('wishlist.toggle', $product))->assertNotFound();
    }

    public function test_admin_search_filter_and_status_endpoint_use_database_results_and_allow_lists(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create(['name' => 'Woody']);
        $product = Product::factory()->create([
            'name' => 'Cedar Archive',
            'category' => $category->name,
            'category_id' => $category->id,
            'status' => 'active',
        ]);
        Product::factory()->inactive()->create(['name' => 'Rose Archive']);

        $this->actingAs($admin)->getJson(route('admin.products.index', [
            'q' => 'Cedar',
            'category' => $category->id,
            'status' => 'active',
        ]))->assertOk()->assertJsonPath('count', 1)->assertSee('Cedar Archive');

        $this->actingAs($admin)->patchJson(route('admin.products.status', $product), ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('message', 'Product status updated successfully.');
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'inactive']);

        $this->actingAs($admin)->patchJson(route('admin.products.status', $product), ['status' => 'published'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_soft_delete_removes_transient_references_and_preserves_order_history(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Archive Bottle']);
        $cart = Cart::factory()->for($customer)->create();
        $wishlist = Wishlist::factory()->for($customer)->create();
        CartItem::factory()->for($cart)->for($product)->create();
        WishlistItem::factory()->for($wishlist)->for($product)->create();
        $order = Order::factory()->for($customer)->create();
        $orderItem = OrderItem::factory()->for($order)->for($product)->create([
            'product_name' => 'Archive Bottle',
        ]);

        $this->actingAs($admin)->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status', 'Product deleted successfully.');

        $this->assertSoftDeleted($product);
        $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
        $this->assertDatabaseMissing('wishlist_items', ['product_id' => $product->id]);
        $this->assertModelExists($orderItem);
        $this->assertDatabaseHas('order_items', ['id' => $orderItem->id, 'product_name' => 'Archive Bottle']);
    }
}
