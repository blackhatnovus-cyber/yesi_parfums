@extends('layouts.app')

@section('title', 'Portfolio — Issey Parfums')

@section('content')
    <section class="portfolio-hero">
        <p class="eyebrow">Atelier journal · Three studies</p>
        <h1>Compositions shaped by <em>place.</em></h1>
        <p>Issey Parfums is a student-built fragrance house imagining how atmosphere can be bottled: forests after rain, flowers at blue hour, and coastlines in bright wind.</p>
    </section>

    @php
        $moods = collect([
            ['title' => 'Rain / Air', 'copy' => 'Transparent compositions where iris, mineral notes, and citrus move like weather across skin.', 'image' => '/images/portfolio-tide.jpg', 'product' => $products->get(4)],
            ['title' => 'Petal / Dusk', 'copy' => 'Flowers shown with their shadows intact: peppered rose, tea, soft suede, and the warmth of osmanthus.', 'image' => '/images/rose-eclat.jpg', 'product' => $products->get(1)],
            ['title' => 'Wood / Ember', 'copy' => 'Hinoki, oud, incense, and amber edited into dark structures that stay measured and close.', 'image' => '/images/amber-elegance.jpg', 'product' => $products->get(6)],
        ])->filter(fn ($mood) => $mood['product'] !== null)->values();
    @endphp
    <section class="mood-list" aria-label="Fragrance collection stories">
        @forelse ($moods as $index => $mood)
            <article class="mood-story">
                <a class="mood-image" href="{{ route('products.show', $mood['product']) }}">
                    <img src="{{ $mood['image'] }}" alt="{{ $mood['title'] }} perfume campaign still life" loading="lazy">
                    <span>0{{ $index + 1 }}</span>
                </a>
                <div>
                    <p class="eyebrow">Collection study / 0{{ $index + 1 }}</p>
                    <h2>{{ $mood['title'] }}</h2>
                    <p>{{ $mood['copy'] }}</p>
                    <a class="editorial-link" href="{{ route('products.show', $mood['product']) }}">Discover {{ $mood['product']->name }} <span>↗</span></a>
                </div>
            </article>
        @empty
            <div class="empty-state"><span>Portfolio / 00</span><h2>The next study is being composed.</h2><p>Seed the product collection to reveal the atelier stories.</p><a class="button-outline" href="{{ route('shop') }}">Visit the Shop</a></div>
        @endforelse
    </section>
@endsection
