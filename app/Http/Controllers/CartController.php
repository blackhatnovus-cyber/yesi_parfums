<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        return view('cart', $this->cartData($request->user()));
    }

    public function store(StoreCartItemRequest $request, Product $product): JsonResponse
    {
        $requestedQuantity = (int) ($request->validated('quantity') ?? 1);
        $user = $request->user();

        DB::transaction(function () use ($user, $product, $requestedQuantity): void {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

            abort_unless($lockedProduct->isAvailableToCustomers(), 404);

            $cart = Cart::query()->firstOrCreate(['user_id' => $user->id]);
            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $lockedProduct->id)
                ->lockForUpdate()
                ->first();
            $newQuantity = ($item?->quantity ?? 0) + $requestedQuantity;

            if ($lockedProduct->stock < $newQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Only '.$lockedProduct->stock.' item(s) are available.',
                ]);
            }

            if ($item === null) {
                $cart->items()->create([
                    'product_id' => $lockedProduct->id,
                    'quantity' => $newQuantity,
                ]);
            } else {
                $item->update(['quantity' => $newQuantity]);
            }
        });

        return $this->cartResponse($user, 'Product added to cart.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $user = $request->user();
        $quantity = (int) $request->validated('quantity');
        $this->ensureOwnership($cartItem, $user);

        DB::transaction(function () use ($cartItem, $quantity): void {
            $lockedItem = CartItem::query()->lockForUpdate()->findOrFail($cartItem->id);
            $product = Product::query()->lockForUpdate()->findOrFail($lockedItem->product_id);

            abort_unless($product->isAvailableToCustomers(), 404);

            if ($quantity > $product->stock) {
                throw ValidationException::withMessages([
                    'quantity' => 'Only '.$product->stock.' item(s) are available.',
                ]);
            }

            $lockedItem->update(['quantity' => $quantity]);
        });

        return $this->cartResponse($user, 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $user = $request->user();
        $this->ensureOwnership($cartItem, $user);
        $cartItem->delete();

        return $this->cartResponse($user, 'Product removed from cart.');
    }

    private function ensureOwnership(CartItem $cartItem, User $user): void
    {
        $owned = CartItem::query()
            ->whereKey($cartItem)
            ->whereHas('cart', fn ($query) => $query->whereBelongsTo($user))
            ->exists();

        abort_unless($owned, 404);
    }

    /** @return array{cart: ?Cart, items: Collection<int, CartItem>, total: float} */
    private function cartData(User $user): array
    {
        $cart = Cart::query()
            ->whereBelongsTo($user)
            ->with(['items' => function (HasMany $query): void {
                $query->whereHas('product', fn (Builder $productQuery): Builder => $productQuery->active())
                    ->with('product');
            }])
            ->first();
        $items = $cart?->items ?? collect();
        $total = (float) $items->sum(
            fn (CartItem $item): float => (float) $item->product->price * $item->quantity,
        );

        return compact('cart', 'items', 'total');
    }

    private function cartResponse(User $user, string $message): JsonResponse
    {
        $data = $this->cartData($user);

        return response()->json([
            'message' => $message,
            'cart_count' => $data['items']->sum('quantity'),
            'html' => view('partials.cart-content', $data)->render(),
        ]);
    }
}
