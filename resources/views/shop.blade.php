@extends('layouts.app')

@section('title', 'Shop — Issey Parfums')

@section('content')
    <section class="page-intro">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('shop') }}" aria-current="page">Shop</a></nav>
            <p class="eyebrow">Collection 01 · {{ $products->count() }} fragrances</p>
            <h1>The Collection</h1>
        </div>
        <p>Compositions for weather, memory, ritual, and skin. Every bottle is photographed locally and every listing is drawn directly from the atelier database.</p>
    </section>

    <div class="shop-toolbar">
        <p><span id="productCount">{{ $products->count() }}</span> fragrances</p>
        <div class="sort-field">
            <label for="sortSelect">Sort collection</label>
            <select id="sortSelect" data-sort-select>
                <option value="default">Default Sorting</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="name-asc">Name: A to Z</option>
                <option value="name-desc">Name: Z to A</option>
            </select>
        </div>
    </div>

    <section class="shop-products" aria-label="Perfume products">
        <div class="product-grid" id="productGrid" aria-live="polite" aria-busy="false">
            @include('partials.product-grid', ['products' => $products, 'wishlistIds' => $wishlistIds])
        </div>
    </section>
@endsection
