<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = $this->queryProducts(
            $request->string('q')->trim()->toString(),
            $request->string('sort', 'default')->toString(),
        )->get();

        return view('shop', [
            'products' => $products,
            'wishlistIds' => $this->wishlistIds($request->user()),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:default,price-asc,price-desc,name-asc,name-desc'],
        ]);
        $products = $this->queryProducts(
            trim((string) ($validated['q'] ?? '')),
            (string) ($validated['sort'] ?? 'default'),
        )->get();
        $wishlistIds = $this->wishlistIds($request->user());

        return response()->json([
            'count' => $products->count(),
            'message' => $products->isEmpty() ? 'No products found.' : null,
            'html' => view('partials.product-grid', compact('products', 'wishlistIds'))->render(),
            'suggestions' => $products->take(6)->map(fn (Product $product): array => [
                'name' => $product->name,
                'category' => $product->category,
                'price' => $product->price,
                'image' => $product->image,
                'url' => route('products.show', $product),
            ])->values(),
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless($product->isAvailableToCustomers(), 404);

        return view('product', [
            'product' => $product,
            'isWished' => in_array($product->id, $this->wishlistIds($request->user()), true),
        ]);
    }

    private function queryProducts(string $query, string $sort): Builder
    {
        $products = Product::query()->active()->when($query !== '', function (Builder $builder) use ($query): void {
            $builder->where(function (Builder $search) use ($query): void {
                $search->where('name', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        });

        return match ($sort) {
            'price-asc' => $products->orderBy('price')->orderBy('id'),
            'price-desc' => $products->orderByDesc('price')->orderBy('id'),
            'name-asc' => $products->orderBy('name')->orderBy('id'),
            'name-desc' => $products->orderByDesc('name')->orderBy('id'),
            default => $products->orderBy('id'),
        };
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
