<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_metrics_derive_from_current_database_records(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        Product::factory()->create(['stock' => 5, 'status' => 'active']);
        Product::factory()->create(['stock' => 2, 'status' => 'inactive']);
        $deletedProduct = Product::factory()->create(['stock' => 1, 'status' => 'active']);
        $deletedProduct->delete();
        Order::factory()->for($customer)->create(['total' => 1234.50, 'status' => 'completed']);
        Order::factory()->for($customer)->create(['total' => 999.00, 'status' => 'pending']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()->assertViewHas('metrics', function (array $metrics): bool {
            return $metrics['products'] === 2
                && $metrics['orders'] === 2
                && $metrics['customers'] === 1
                && $metrics['sales'] === 1234.5
                && $metrics['lowStock'] === 1;
        });
    }
}
