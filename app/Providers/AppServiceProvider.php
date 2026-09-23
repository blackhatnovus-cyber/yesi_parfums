<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\ContactMessage;
use App\Models\Wishlist;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(AuthFactory $auth): void
    {
        Model::preventLazyLoading(! $this->app->environment('production'));

        View::composer('layouts.app', function ($view) use ($auth): void {
            $cartCount = 0;
            $wishlistCount = 0;
            $guard = $auth->guard();

            if ($guard->check()) {
                $userId = (int) $guard->id();
                $cartCount = (int) Cart::query()
                    ->where('user_id', $userId)
                    ->join('cart_items', 'carts.id', '=', 'cart_items.cart_id')
                    ->join('products', 'cart_items.product_id', '=', 'products.id')
                    ->where('products.status', 'active')
                    ->whereNull('products.deleted_at')
                    ->sum('cart_items.quantity');
                $wishlistCount = (int) Wishlist::query()
                    ->where('user_id', $userId)
                    ->join('wishlist_items', 'wishlists.id', '=', 'wishlist_items.wishlist_id')
                    ->join('products', 'wishlist_items.product_id', '=', 'products.id')
                    ->where('products.status', 'active')
                    ->whereNull('products.deleted_at')
                    ->count('wishlist_items.id');
            }

            $view->with(compact('cartCount', 'wishlistCount'));
        });

        View::composer('layouts.admin', function ($view): void {
            $view->with(
                'unreadMessageCount',
                ContactMessage::query()->where('status', 'unread')->count(),
            );
        });
    }
}
