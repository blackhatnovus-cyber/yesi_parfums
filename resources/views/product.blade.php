@extends('layouts.app')

@section('title', $product->name.' — Issey Parfums')

@section('content')
    <nav class="detail-breadcrumb breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('shop') }}">Shop</a><span>/</span><span aria-current="page">{{ $product->name }}</span></nav>
    <article class="product-detail">
        <div class="detail-image reveal">
            <img src="{{ $product->image }}" alt="Editorial studio photograph of the {{ $product->name }} perfume bottle">
            <span>Object study / {{ str_pad((string) $product->id, 2, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="detail-copy reveal reveal-delay">
            <p class="eyebrow">{{ $product->category }} · Eau de parfum</p>
            <h1>{{ $product->name }}</h1>
            <p class="detail-price">₱{{ number_format((float) $product->price, 2) }}</p>
            <p class="detail-description">{{ $product->description }}</p>
            <dl class="detail-facts">
                <div><dt>Availability</dt><dd>{{ $product->stock > 0 ? $product->stock.' bottles in atelier' : 'Out of stock' }}</dd></div>
                <div><dt>Format</dt><dd>50 ml · 1.7 fl oz</dd></div>
                <div><dt>Origin</dt><dd>Composed in Manila</dd></div>
            </dl>
            <div class="detail-actions">
                <div class="quantity-control" data-quantity-control>
                    <button type="button" data-quantity-minus aria-label="Decrease quantity">−</button>
                    <label class="sr-only" for="detailQuantity">Quantity</label>
                    <input id="detailQuantity" type="number" min="1" max="{{ max(1, $product->stock) }}" value="1" inputmode="numeric" {{ $product->stock < 1 ? 'disabled' : '' }}>
                    <button type="button" data-quantity-plus aria-label="Increase quantity">+</button>
                </div>
                <button class="button-solid" type="button" data-add-cart="{{ route('cart.items.store', $product) }}" data-detail-quantity {{ $product->stock < 1 ? 'disabled' : '' }}>{{ $product->stock > 0 ? 'Add to Cart' : 'Unavailable' }}</button>
                <button class="button-outline wish-detail {{ $isWished ? 'is-active' : '' }}" type="button" data-toggle-wishlist="{{ route('wishlist.toggle', $product) }}" aria-pressed="{{ $isWished ? 'true' : 'false' }}">{{ $isWished ? 'Remove Wishlist' : 'Add to Wishlist' }}</button>
            </div>
            <a class="editorial-link back-link" href="{{ route('shop') }}">← Back to Shop</a>
        </div>
    </article>
@endsection
