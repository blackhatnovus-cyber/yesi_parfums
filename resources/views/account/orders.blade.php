@extends('layouts.app')

@section('title', 'Order History — Issey Parfums')

@section('content')
    <div class="account-page account-history-page">
        <nav class="breadcrumb account-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('account.dashboard') }}">My Account</a><span>/</span><span>Order History</span>
        </nav>

        <header class="account-history-header">
            <div><p class="eyebrow">Your atelier record</p><h1>Order history<span class="account-heading-period">.</span></h1></div>
            <p>Every composition you have chosen, collected in one place.</p>
        </header>

        <nav class="account-tabs" aria-label="Account sections">
            <a href="{{ route('account.dashboard') }}">Overview</a>
            <a href="{{ route('account.orders') }}" aria-current="page">Order history <span>{{ $orders->total() }}</span></a>
            <a href="{{ route('wishlist.index') }}">Wishlist</a>
        </nav>

        <section class="account-history-list" aria-labelledby="history-list-title">
            <div class="account-panel-heading account-history-list-heading">
                <div><p class="eyebrow">The archive</p><h2 id="history-list-title">{{ $orders->total() }} {{ Illuminate\Support\Str::plural('order', $orders->total()) }}</h2></div>
                <span class="account-history-sort">Newest first</span>
            </div>

            @forelse ($orders as $order)
                <article class="account-history-row">
                    <div class="account-history-order">
                        <span class="account-micro-label">Order number</span>
                        <h3>{{ $order->order_number }}</h3>
                        <p class="account-history-meta"><time datetime="{{ $order->created_at->toDateString() }}">{{ $order->created_at->format('F j, Y') }}</time><span aria-hidden="true"> · </span>{{ $order->items_count }} {{ \Illuminate\Support\Str::plural('item', $order->items_count) }}</p>
                    </div>
                    <div><span class="account-micro-label">Placed</span><span>{{ $order->created_at->format('F j, Y') }}</span></div>
                    <div><span class="account-micro-label">Fragrances</span><span>{{ $order->items_count }} {{ Illuminate\Support\Str::plural('item', $order->items_count) }}</span></div>
                    <div><span class="account-micro-label">Status</span><span class="account-status" data-status="{{ $order->status }}">{{ ucfirst($order->status) }}</span></div>
                    <div><span class="account-micro-label">Total</span><strong>₱{{ number_format((float) $order->total, 2) }}</strong></div>
                    <a class="account-history-view" href="{{ route('orders.show', $order) }}" aria-label="View order {{ $order->order_number }}">View order <span aria-hidden="true">↗</span></a>
                </article>
            @empty
                <div class="account-empty account-history-empty">
                    <span class="account-empty-number" aria-hidden="true">00</span>
                    <div><h3>No orders just yet.</h3><p>Explore the collection when you are ready. Your orders will appear here after you place one.</p><a class="button-solid" href="{{ route('shop') }}">Explore the collection</a></div>
                </div>
            @endforelse
        </section>

        @if ($orders->hasPages())
            <nav class="account-pagination" aria-label="Order history pages">
                @if ($orders->onFirstPage())
                    <span aria-disabled="true">← Previous</span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}" rel="prev">← Previous</a>
                @endif
                <span>Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</span>
                @if ($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}" rel="next">Next →</a>
                @else
                    <span aria-disabled="true">Next →</span>
                @endif
            </nav>
        @endif
    </div>
@endsection
