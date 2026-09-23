@extends('layouts.app')

@section('title', 'Issey Parfums — The Quiet Atelier')

@section('content')
    <section class="campaign-hero">
        <div class="hero-copy reveal">
            <h1>Fragrance,<br><em>held in quiet.</em></h1>
            <div class="hero-details">
                <p class="hero-intro">Ten compositions shaped around rain, petals, smoke, salt, and skin. Made for the moments that refuse to announce themselves.</p>
                <div class="hero-actions">
                    <a class="button-solid" href="{{ route('shop') }}">Shop Now</a>
                </div>
            </div>
            <div class="hero-index"><span>EAU DE PARFUM</span><span>50 ML / 1.7 FL OZ</span></div>
        </div>
        <figure class="hero-product reveal">
            <img src="{{ asset('images/hero-blush-perfume-cutout.png') }}" alt="Blush-pink perfume bottle with a charcoal cap" width="1199" height="1312" fetchpriority="high">
        </figure>
    </section>

    <section class="collection-section" aria-labelledby="featured-heading">
        <header class="section-heading">
            <div><p class="eyebrow">Selected compositions / 04</p><h2 id="featured-heading">The featured edit</h2></div>
            <p>Perfumes chosen for contrast: a rain-washed floral, a darkened wood, a glowing amber, a rose with its sweetness pared back.</p>
        </header>
        <div class="product-grid" id="featuredGrid">
            @include('partials.product-grid', ['products' => $featuredProducts, 'wishlistIds' => $wishlistIds])
        </div>
    </section>

    <section class="live-search-section" id="liveSearchSection" aria-labelledby="live-search-title" hidden>
        <header class="section-heading compact">
            <div><p class="eyebrow">Live database search</p><h2 id="live-search-title">Search results</h2></div>
            <p id="liveSearchSummary" aria-live="polite"></p>
        </header>
        <div class="product-grid" id="liveSearchGrid"></div>
    </section>

@endsection
