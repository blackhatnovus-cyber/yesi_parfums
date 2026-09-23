<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        return view('wishlist', $this->wishlistData($request->user()));
    }

    public function toggle(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->isAvailableToCustomers(), 404);

        $user = $request->user();
        $active = DB::transaction(function () use ($user, $product): bool {
            $wishlist = Wishlist::query()->firstOrCreate(['user_id' => $user->id]);
            $item = WishlistItem::query()
                ->where('wishlist_id', $wishlist->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if ($item !== null) {
                $item->delete();

                return false;
            }

            $wishlist->items()->create(['product_id' => $product->id]);

            return true;
        });
        $data = $this->wishlistData($user);

        return response()->json([
            'message' => $active ? 'Product added to wishlist.' : 'Product removed from wishlist.',
            'active' => $active,
            'wishlist_count' => $data['items']->count(),
            'html' => view('partials.wishlist-content', $data)->render(),
        ]);
    }

    public function destroy(Request $request, WishlistItem $wishlistItem): JsonResponse
    {
        $user = $request->user();
        $owned = WishlistItem::query()
            ->whereKey($wishlistItem)
            ->whereHas('wishlist', fn ($query) => $query->whereBelongsTo($user))
            ->exists();

        abort_unless($owned, 404);
        $wishlistItem->delete();
        $data = $this->wishlistData($user);

        return response()->json([
            'message' => 'Product removed from wishlist.',
            'wishlist_count' => $data['items']->count(),
            'html' => view('partials.wishlist-content', $data)->render(),
        ]);
    }

    /** @return array{wishlist: ?Wishlist, items: Collection<int, WishlistItem>} */
    private function wishlistData(User $user): array
    {
        $wishlist = Wishlist::query()
            ->whereBelongsTo($user)
            ->with(['items' => function (HasMany $query): void {
                $query->whereHas('product', fn (Builder $productQuery): Builder => $productQuery->active())
                    ->with('product');
            }])
            ->first();
        $items = $wishlist?->items ?? collect();

        return compact('wishlist', 'items');
    }
}
