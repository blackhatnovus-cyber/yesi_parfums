<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CatalogueFeatureTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_home_and_shop_render_database_products_and_required_controls(): void
    {
        $product = Product::factory()->create(['name' => 'Rain Study', 'stock' => 5]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Rain Study')
            ->assertSee('Shop Now')
            ->assertSee('Search scents');

        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('Rain Study')
            ->assertSee('Default Sorting')
            ->assertSee('Add to Cart');
    }

    public function test_search_matches_name_category_and_description_and_reports_empty_results(): void
    {
        Product::factory()->create([
            'name' => 'Rose Archive',
            'category' => 'Floral',
            'description' => 'A vivid peppered flower.',
        ]);
        Product::factory()->create([
            'name' => 'Forest Study',
            'category' => 'Woody',
            'description' => 'Cedar after rain.',
        ]);

        $this->getJson(route('products.search', ['q' => 'peppered']))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('suggestions.0.name', 'Rose Archive');

        $this->getJson(route('products.search', ['q' => 'missing-note']))
            ->assertOk()
            ->assertJsonPath('count', 0)
            ->assertJsonPath('message', 'No products found.');
    }

    public function test_sorting_uses_an_allow_list_and_orders_products_immediately_available_to_ajax(): void
    {
        Product::factory()->create(['name' => 'Costly', 'price' => 5000]);
        Product::factory()->create(['name' => 'Accessible', 'price' => 2000]);

        $this->getJson(route('products.search', ['sort' => 'price-asc']))
            ->assertOk()
            ->assertSeeInOrder(['Accessible', 'Costly']);

        $this->getJson(route('products.search', ['sort' => 'price;DROP TABLE products']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_product_details_render_and_invalid_product_returns_404(): void
    {
        $product = Product::factory()->create(['name' => 'Detail Study', 'stock' => 4]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Detail Study')
            ->assertSee('4 bottles in atelier')
            ->assertSee('Back to Shop');

        $this->get('/products/999999')->assertNotFound();
    }

    public function test_seeded_portfolio_and_key_course_content_render(): void
    {
        $this->seed();

        $this->get(route('portfolio'))
            ->assertOk()
            ->assertSee('Rain / Air')
            ->assertSee('Petal / Dusk')
            ->assertSee('Wood / Ember')
            ->assertSee('Issey A. Cabangon');
    }
}
