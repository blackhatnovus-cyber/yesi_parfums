<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_and_update_unique_category_with_product_text_synchronization(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Mineral Floral',
            'description' => 'Cool stones and pale flowers.',
        ])->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category created successfully.');

        $category = Category::query()->where('name', 'Mineral Floral')->firstOrFail();
        $product = Product::factory()->create([
            'category' => $category->name,
            'category_id' => $category->id,
        ]);

        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Mineral Iris',
            'description' => 'A refined category note.',
        ])->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category updated successfully.');

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Mineral Iris']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'category' => 'Mineral Iris']);

        Category::factory()->create(['name' => 'Reserved Name']);
        $this->actingAs($admin)->put(route('admin.categories.update', $category), [
            'name' => 'Reserved Name',
        ])->assertSessionHasErrors('name');
    }

    public function test_linked_category_cannot_be_deleted_even_when_product_is_soft_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category' => $category->name,
            'category_id' => $category->id,
        ]);
        $product->delete();

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))
            ->assertRedirect()
            ->assertSessionHasErrors([
                'category' => 'This category cannot be deleted while products are linked to it.',
            ]);

        $this->assertModelExists($category);
    }

    public function test_unlinked_category_can_be_deleted_after_confirmation_request_reaches_server(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status', 'Category deleted successfully.');

        $this->assertModelMissing($category);
    }
}
