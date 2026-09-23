@extends('layouts.app')

@section('title', 'Cart — Issey Parfums')

@section('content')
    <section class="page-intro compact-page">
        <div>
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Cart</span></nav>
            <p class="eyebrow">Private selection</p>
            <h1>Your Cart</h1>
        </div>
        <p>Quantities are checked against current MySQL stock with every change.</p>
    </section>
    <section class="commerce-shell" id="cartPanel" aria-live="polite" aria-busy="false">
        @include('partials.cart-content', ['cart' => $cart, 'items' => $items, 'total' => $total])
    </section>
@endsection
