@forelse ($products as $product)
    @php($wished = in_array($product->id, $wishlistIds ?? [], true))
    <article class="product-card" data-product-card data-product-url="{{ route('products.show', $product) }}" tabindex="0" aria-label="View {{ $product->name }}">
        <div class="product-media">
            <a href="{{ route('products.show', $product) }}" aria-label="View {{ $product->name }} details">
                <img src="{{ $product->image }}" alt="Editorial studio photograph of the {{ $product->name }} perfume bottle" loading="lazy">
            </a>
            <button
                class="heart-control {{ $wished ? 'is-active' : '' }}"
                type="button"
                data-toggle-wishlist="{{ route('wishlist.toggle', $product) }}"
                aria-label="{{ $wished ? 'Remove '.$product->name.' from' : 'Add '.$product->name.' to' }} wishlist"
                aria-pressed="{{ $wished ? 'true' : 'false' }}"
            >
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 5.8a5.1 5.1 0 0 0-7.2 0L12 7.4l-1.6-1.6a5.1 5.1 0 0 0-7.2 7.2L12 21l8.8-8a5.1 5.1 0 0 0 0-7.2Z"/></svg>
            </button>
            <span class="stock-tag">{{ $product->stock > 0 ? 'In atelier · '.$product->stock : 'Out of stock' }}</span>
        </div>
        <div class="product-meta">
            <div>
                <p>{{ $product->category }}</p>
                <h3><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a></h3>
            </div>
            <strong>₱{{ number_format((float) $product->price, 2) }}</strong>
        </div>
        <button
            class="text-action add-cart"
            type="button"
            data-add-cart="{{ route('cart.items.store', $product) }}"
            {{ $product->stock < 1 ? 'disabled' : '' }}
        >{{ $product->stock > 0 ? 'Add to Cart' : 'Unavailable' }}</button>
    </article>
@empty
    <div class="empty-state">
        <span>Search note / 00</span>
        <h2>No products found.</h2>
        <p>Try a fragrance family such as floral, woody, amber, or fresh.</p>
        <a class="button-outline" href="{{ route('shop') }}">View the full collection</a>
    </div>
@endforelse
