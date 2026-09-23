@extends('layouts.app')

@section('title', 'Wishlist — Issey Parfums')

@section('content')
    <section class="page-intro compact-page">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Wishlist</span></nav>
            <p class="eyebrow">Saved compositions</p>
            <h1>Wishlist</h1>
        </div>
        <p>A private edit of fragrances you want to revisit.</p>
    </section>
    <section class="commerce-shell" id="wishlistPanel" aria-live="polite" aria-busy="false">
        @include('partials.wishlist-content', ['wishlist' => $wishlist, 'items' => $items])
    </section>
@endsection
