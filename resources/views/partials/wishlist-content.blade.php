@if ($items->isEmpty())
    <div class="empty-state commerce-empty">
        <span>Wishlist / 00</span>
        <h2>No keepsakes yet.</h2>
        <p>Use the heart on any fragrance to save it here.</p>
        <a class="button-solid" href="{{ route('shop') }}">Explore the Collection</a>
    </div>
@else
    <div class="wishlist-list">
        @foreach ($items as $item)
            <article class="wishlist-row">
                <a href="{{ route('products.show', $item->product) }}"><img src="{{ $item->product->image }}" alt="{{ $item->product->name }} bottle"></a>
                <div><p>{{ $item->product->category }}</p><h2><a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a></h2><strong>₱{{ number_format((float) $item->product->price, 2) }}</strong></div>
                <div class="wishlist-actions">
                    <button class="button-solid" type="button" data-add-cart="{{ route('cart.items.store', $item->product) }}" {{ $item->product->stock < 1 ? 'disabled' : '' }}>Add to Cart</button>
                    <button class="remove-action" type="button" data-remove-wishlist="{{ route('wishlist.items.destroy', $item) }}">Remove Wishlist</button>
                </div>
            </article>
        @endforeach
    </div>
@endif
