<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'featuredProducts' => Product::query()->active()->orderBy('id')->limit(4)->get(),
            'wishlistIds' => $this->wishlistIds(auth()->user()),
        ]);
    }

    public function portfolio(): View
    {
        return view('portfolio', [
            'products' => Product::query()->active()->orderBy('id')->limit(7)->get(),
        ]);
    }

    /** @return array<int, int> */
    private function wishlistIds(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        return $user->wishlist()
            ->join('wishlist_items', 'wishlists.id', '=', 'wishlist_items.wishlist_id')
            ->pluck('wishlist_items.product_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }
}
