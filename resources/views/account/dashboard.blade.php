@extends('layouts.app')

@section('title', 'My Account — Issey Parfums')

@section('content')
    <div class="account-page">
        <nav class="breadcrumb account-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span>/</span><span>My Account</span>
        </nav>

        <header class="account-hero">
            <div class="account-hero-copy">
                <p class="eyebrow">Your personal atelier</p>
                <h1>Welcome back,<br><em>{{ $customer->name }}.</em></h1>
                <p>A quiet place for your orders, saved fragrances, and account details.</p>
            </div>
            <aside class="account-folio" aria-label="Your account at a glance">
                <span class="account-folio-index">ISSEY / PRIVATE FOLIO</span>
                <span class="account-folio-monogram" aria-hidden="true">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                <div class="account-folio-stats">
                    <div><strong>{{ str_pad((string) $orderCount, 2, '0', STR_PAD_LEFT) }}</strong><span>Orders placed</span></div>
                    <div><strong>{{ str_pad((string) $wishlistCount, 2, '0', STR_PAD_LEFT) }}</strong><span>Saved scents</span></div>
                </div>
            </aside>
        </header>

        <nav class="account-tabs" aria-label="Account sections">
            <a href="{{ route('account.dashboard') }}" aria-current="page">Overview</a>
            <a href="{{ route('account.orders') }}">Order history <span>{{ $orderCount }}</span></a>
            <a href="{{ route('wishlist.index') }}">Wishlist <span>{{ $wishlistCount }}</span></a>
        </nav>

        <div class="account-content-grid">
            <section class="account-panel account-orders-panel" aria-labelledby="recent-orders-title">
                <div class="account-panel-heading">
                    <div><p class="eyebrow">01 / Orders</p><h2 id="recent-orders-title">Recent orders</h2></div>
                    @if ($orderCount > 0)
                        <a class="editorial-link" href="{{ route('account.orders') }}">View all orders <span aria-hidden="true">↗</span></a>
                    @endif
                </div>
                @forelse ($recentOrders as $order)
                    <article class="account-order-row">
                        <div class="account-order-identity">
                            <span class="account-micro-label">Order {{ $order->order_number }}</span>
                            <h3>{{ $order->created_at->format('F j, Y') }}</h3>
                        </div>
                        <span class="account-status" data-status="{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                        <strong>₱{{ number_format((float) $order->total, 2) }}</strong>
                        <a href="{{ route('orders.show', $order) }}" aria-label="View order {{ $order->order_number }}">View order <span aria-hidden="true">↗</span></a>
                    </article>
                @empty
                    <div class="account-empty">
                        <span class="account-empty-number" aria-hidden="true">01</span>
                        <div><h3>Your story starts here.</h3><p>When you place an order, its details will be kept here for you.</p><a class="editorial-link" href="{{ route('shop') }}">Explore the collection <span aria-hidden="true">↗</span></a></div>
                    </div>
                @endforelse
            </section>

            <section class="account-panel account-saved-panel" aria-labelledby="saved-title">
                <div class="account-panel-heading">
                    <div><p class="eyebrow">02 / Wishlist</p><h2 id="saved-title">Saved fragrances</h2></div>
                    @if ($wishlistCount > 0)
                        <a class="editorial-link" href="{{ route('wishlist.index') }}">View wishlist <span aria-hidden="true">↗</span></a>
                    @endif
                </div>
                @if ($wishlistItems->isEmpty())
                    <div class="account-empty account-empty-saved">
                        <span class="account-empty-number" aria-hidden="true">02</span>
                        <div><h3>Nothing saved yet.</h3><p>Find a scent worth returning to and tap its heart.</p><a class="editorial-link" href="{{ route('shop') }}">Discover fragrances <span aria-hidden="true">↗</span></a></div>
                    </div>
                @else
                    <div class="account-saved-list">
                        @foreach ($wishlistItems as $item)
                            <a class="account-saved-item" href="{{ route('products.show', $item->product) }}">
                                <img src="{{ $item->product->image }}" alt="{{ $item->product->name }} bottle" loading="lazy">
                                <span><small>{{ $item->product->category }}</small><strong>{{ $item->product->name }}</strong></span>
                                <span aria-hidden="true">↗</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="account-panel account-details-panel" aria-labelledby="details-title">
                <div class="account-panel-heading"><div><p class="eyebrow">03 / Identity</p><h2 id="details-title">Personal details</h2></div></div>
                <dl class="account-details">
                    <div><dt>Name</dt><dd>{{ $customer->name }}</dd></div>
                    <div><dt>Username</dt><dd>{{ $customer->username }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $customer->email }}</dd></div>
                    <div><dt>Member since</dt><dd>{{ $customer->created_at->format('F Y') }}</dd></div>
                </dl>
                <form class="account-logout" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Log out of your account <span aria-hidden="true">↗</span></button>
                </form>
            </section>
        </div>
    </div>
@endsection
