<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $metrics = [
            'products' => Product::query()->count(),
            'orders' => Order::query()->count(),
            'customers' => User::query()->where('role', 'customer')->count(),
            'sales' => (float) Order::query()->where('status', 'completed')->sum('total'),
            'lowStock' => Product::query()->active()->where('stock', '<=', 5)->count(),
        ];
        $recentOrders = Order::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->latest('id')
            ->limit(7)
            ->get();
        $lowStockProducts = Product::query()
            ->active()
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->orderBy('id')
            ->limit(7)
            ->get();

        return view('admin.dashboard', compact('metrics', 'recentOrders', 'lowStockProducts'));
    }
}
