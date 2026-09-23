<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AccountDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $customer */
        $customer = $request->user();
        $recentOrders = $customer->orders()
            ->withCount('items')
            ->latest('created_at')
            ->latest('id')
            ->limit(3)
            ->get();
        $orderCount = $customer->orders()->count();

        $wishlist = $customer->wishlist()->first();
        $wishlistCount = $wishlist?->items()
            ->whereHas('product', fn (Builder $query): Builder => $query->active())
            ->count() ?? 0;
        $wishlistItems = $wishlist?->items()
            ->whereHas('product', fn (Builder $query): Builder => $query->active())
            ->with('product')
            ->latest('id')
            ->limit(3)
            ->get() ?? collect();

        return view('account.dashboard', compact(
            'customer',
            'recentOrders',
            'orderCount',
            'wishlistItems',
            'wishlistCount',
        ));
    }
}
