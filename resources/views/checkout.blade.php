@extends('layouts.app')

@section('title', 'Checkout — Issey Parfums')

@section('content')
    <section class="page-intro compact-page">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('cart.index') }}">Cart</a><span>/</span><span>Checkout</span></nav>
            <p class="eyebrow">Shipping details</p>
            <h1>Checkout</h1>
        </div>
        <p>Confirm where the atelier should send your order. This demonstration records an order but does not process payment.</p>
    </section>

    <section class="checkout-shell">
        @if ($items->isEmpty())
            <div class="empty-state commerce-empty">
                <span>Order / 00</span>
                <h2>Your cart is empty.</h2>
                <p>Add a fragrance before checking out.</p>
                <a class="button-solid" href="{{ route('shop') }}">Explore the Collection</a>
            </div>
        @else
            <form class="checkout-form" method="POST" action="{{ route('checkout.store') }}">
                @csrf
                @if ($errors->has('cart'))<div class="notice" role="alert">{{ $errors->first('cart') }}</div>@endif
                @if ($hasUnavailableItems)<div class="notice" role="alert">One or more cart items are unavailable. Return to your cart before placing the order.</div>@endif

                <div class="field @error('shipping_name') has-error @enderror">
                    <label for="shipping_name">Shipping name</label>
                    <input id="shipping_name" name="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" autocomplete="name" required>
                    @error('shipping_name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('shipping_email') has-error @enderror">
                    <label for="shipping_email">Email</label>
                    <input id="shipping_email" name="shipping_email" type="email" value="{{ old('shipping_email', auth()->user()->email) }}" autocomplete="email" required>
                    @error('shipping_email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('shipping_address') has-error @enderror">
                    <label for="shipping_address">Shipping address</label>
                    <textarea id="shipping_address" name="shipping_address" rows="5" autocomplete="street-address" required>{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <button class="button-solid" type="submit" @disabled($hasUnavailableItems)>Place Order</button>
                <p class="checkout-disclaimer">No payment is requested or processed.</p>
            </form>

            <aside class="checkout-summary" aria-label="Order summary">
                <p class="eyebrow">Order summary</p>
                <h2>{{ $items->sum('quantity') }} bottle(s)</h2>
                <div class="checkout-items">
                    @foreach ($items as $item)
                        <article>
                            <img src="{{ $item->product?->image ?? '/images/signature.jpg' }}" alt="">
                            <div><strong>{{ $item->product?->name ?? 'Unavailable product' }}</strong><small>Quantity {{ $item->quantity }}</small></div>
                            <span>₱{{ number_format($item->product ? (float) $item->product->price * $item->quantity : 0, 2) }}</span>
                        </article>
                    @endforeach
                </div>
                <p class="checkout-total"><span>Total</span><strong>₱{{ number_format($total, 2) }}</strong></p>
            </aside>
        @endif
    </section>
@endsection
