@if ($items->isEmpty())
    <div class="empty-state commerce-empty">
        <span>Cart / 00</span>
        <h2>Your cart is quiet.</h2>
        <p>Choose a composition and it will wait here for you.</p>
        <a class="button-solid" href="{{ route('shop') }}">Continue Shopping</a>
    </div>
@else
    <div class="cart-table" role="table" aria-label="Cart products">
        <div class="cart-head" role="row">
            <span role="columnheader">Product</span><span role="columnheader">Price</span><span role="columnheader">Quantity</span><span role="columnheader">Subtotal</span><span></span>
        </div>
        @foreach ($items as $item)
            <article class="cart-row" role="row" data-cart-row>
                <div class="cart-product" role="cell">
                    <a href="{{ route('products.show', $item->product) }}"><img src="{{ $item->product->image }}" alt="{{ $item->product->name }} bottle"></a>
                    <div><p>{{ $item->product->category }}</p><h2><a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a></h2><small>{{ $item->product->stock }} currently in stock</small></div>
                </div>
                <p role="cell" data-label="Price">₱{{ number_format((float) $item->product->price, 2) }}</p>
                <div role="cell" data-label="Quantity">
                    <div class="quantity-control" data-cart-quantity data-update-url="{{ route('cart.items.update', $item) }}">
                        <button type="button" data-cart-minus aria-label="Decrease {{ $item->product->name }} quantity" {{ $item->quantity <= 1 ? 'disabled' : '' }}>−</button>
                        <output aria-label="Current quantity">{{ $item->quantity }}</output>
                        <button type="button" data-cart-plus aria-label="Increase {{ $item->product->name }} quantity" {{ $item->quantity >= $item->product->stock ? 'disabled' : '' }}>+</button>
                    </div>
                </div>
                <strong role="cell" data-label="Subtotal">₱{{ number_format((float) $item->product->price * $item->quantity, 2) }}</strong>
                <button class="remove-action" type="button" data-remove-cart="{{ route('cart.items.destroy', $item) }}" aria-label="Remove {{ $item->product->name }} from cart">Remove</button>
            </article>
        @endforeach
    </div>
    <aside class="cart-summary">
        <p>Cart total <strong>₱{{ number_format($total, 2) }}</strong></p>
        <small>{{ $items->sum('quantity') }} item(s) · Taxes and delivery are not included in this coursework demonstration.</small>
        <div class="cart-summary-actions">
            <a class="button-solid" href="{{ route('checkout.index') }}">Checkout</a>
            <a class="button-outline" href="{{ route('shop') }}">Continue Shopping</a>
        </div>
    </aside>
@endif
