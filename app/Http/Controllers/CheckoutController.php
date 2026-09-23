<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $cart = Cart::query()
            ->whereBelongsTo($request->user())
            ->with(['items' => fn ($query) => $query->with('product')->orderBy('id')])
            ->first();
        $items = $cart?->items ?? collect();

        return view('checkout', [
            'items' => $items,
            'total' => $this->calculateTotal($items),
            'hasUnavailableItems' => $items->contains(
                fn (CartItem $item): bool => ! $item->product?->isAvailableToCustomers(),
            ),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $order = DB::transaction(function () use ($validated, $user): Order {
            $cart = Cart::query()->whereBelongsTo($user)->lockForUpdate()->first();
            $items = $cart?->items()->lockForUpdate()->orderBy('id')->get() ?? collect();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Your cart is empty. Add a fragrance before checking out.',
                ]);
            }

            $products = Product::query()
                ->withTrashed()
                ->whereKey($items->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $total = 0.0;

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product instanceof Product || ! $product->isAvailableToCustomers()) {
                    throw ValidationException::withMessages([
                        'cart' => 'One or more fragrances are no longer available. Please review your cart.',
                    ]);
                }

                if ($item->quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => $product->name.' has only '.$product->stock.' bottle(s) available.',
                    ]);
                }

                $total += round((float) $product->price * $item->quantity, 2);
            }

            $order = $user->orders()->create([
                'order_number' => $this->nextOrderNumber(),
                'shipping_name' => $validated['shipping_name'],
                'shipping_email' => $validated['shipping_email'],
                'shipping_address' => $validated['shipping_address'],
                'total' => round($total, 2),
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                /** @var Product $product */
                $product = $products->get($item->product_id);
                $subtotal = round((float) $product->price * $item->quantity, 2);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'unit_price' => $product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $subtotal,
                ]);
                $product->decrement('stock', $item->quantity);
            }

            $cart?->items()->delete();

            return $order;
        }, 3);

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Order placed successfully. No payment was processed.');
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load('items');

        return view('order-confirmation', compact('order'));
    }

    /** @param Collection<int, CartItem> $items */
    private function calculateTotal(Collection $items): float
    {
        return (float) $items->sum(
            fn (CartItem $item): float => $item->product === null
                ? 0.0
                : (float) $item->product->price * $item->quantity,
        );
    }

    private function nextOrderNumber(): string
    {
        do {
            $orderNumber = 'IP-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (Order::query()->where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
