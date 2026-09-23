<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="search-endpoint" content="{{ route('products.search') }}">
    <meta name="description" content="Issey Parfums — a quiet Parisian-inspired perfume atelier.">
    <title>@yield('title', 'Issey Parfums')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="grain" aria-hidden="true"></div>

    <header class="site-header">
        <div class="header-left">
            <button class="icon-control menu-toggle" id="menuToggle" type="button" aria-label="Open menu" aria-controls="mobileNav" aria-expanded="false">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h18M3 12h18M3 17h18"/></svg>
            </button>
            <nav class="desktop-nav" aria-label="Primary navigation">
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Home</a>
                <a href="{{ route('shop') }}" @if (request()->routeIs('shop')) aria-current="page" @endif>Shop</a>
                <a href="{{ route('contacts') }}" @if (request()->routeIs('contacts')) aria-current="page" @endif>Contacts</a>
            </nav>
        </div>

        <a class="wordmark" href="{{ route('home') }}" aria-label="Issey Parfums home">
            <span>ISSEY</span>
            <small>PARFUMS</small>
        </a>

        <div class="header-tools">
            <nav class="account-nav" aria-label="Account navigation">
                @guest
                    <a href="{{ route('login') }}" @if (request()->routeIs('login')) aria-current="page" @endif>Login</a>
                    <a class="register-link" href="{{ route('register') }}" @if (request()->routeIs('register')) aria-current="page" @endif>Register</a>
                @else
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                    @else
                        <a href="{{ route('account.dashboard') }}" @if (request()->routeIs('account.*')) aria-current="page" @endif>My Account</a>
                    @endif
                @endguest
            </nav>
            <form class="header-search" id="headerSearchForm" role="search">
                <label class="sr-only" for="globalSearch">Search perfume products</label>
                <input id="globalSearch" name="q" type="search" placeholder="Search scents" autocomplete="off">
                <button id="searchSubmit" class="icon-control" type="submit" aria-label="Search products">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
                </button>
                <div class="search-panel" id="searchPanel" role="region" aria-label="Search results" aria-live="polite" hidden></div>
            </form>
            <a class="icon-control count-link" href="{{ route('cart.index') }}" aria-label="Cart, {{ $cartCount }} items">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2 12h11l2-8H6M9 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM17 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/></svg>
                <span class="count-badge" data-cart-count>{{ $cartCount }}</span>
            </a>
            <a class="icon-control count-link" href="{{ route('wishlist.index') }}" aria-label="Wishlist, {{ $wishlistCount }} items">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 5.8a5.1 5.1 0 0 0-7.2 0L12 7.4l-1.6-1.6a5.1 5.1 0 0 0-7.2 7.2L12 21l8.8-8a5.1 5.1 0 0 0 0-7.2Z"/></svg>
                <span class="count-badge" data-wishlist-count>{{ $wishlistCount }}</span>
            </a>
        </div>
    </header>

    <div class="nav-overlay" id="navOverlay" hidden></div>
    <aside class="mobile-nav" id="mobileNav" aria-hidden="true" aria-label="Mobile navigation" inert>
        <div class="mobile-nav-head">
            <span>Atelier index</span>
            <button class="icon-control" id="menuClose" type="button" aria-label="Close menu">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4l16 16M20 4 4 20"/></svg>
            </button>
        </div>
        <form class="mobile-search" id="mobileSearchForm" role="search">
            <label class="sr-only" for="mobileSearch">Search perfume products</label>
            <input id="mobileSearch" type="search" placeholder="Search the collection" autocomplete="off">
            <button class="icon-control" type="submit" aria-label="Search products">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
            </button>
            <div class="mobile-search-panel" id="mobileSearchPanel" aria-live="polite" hidden></div>
        </form>
        <nav>
            <a data-nav-close href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Home <small>01</small></a>
            <a data-nav-close href="{{ route('shop') }}" @if (request()->routeIs('shop')) aria-current="page" @endif>Shop <small>02</small></a>
            <a data-nav-close href="{{ route('contacts') }}" @if (request()->routeIs('contacts')) aria-current="page" @endif>Contacts <small>03</small></a>
            <a data-nav-close href="{{ route('cart.index') }}">Cart <small data-cart-count>{{ $cartCount }}</small></a>
            <a data-nav-close href="{{ route('wishlist.index') }}">Wishlist <small data-wishlist-count>{{ $wishlistCount }}</small></a>
            @guest
                <a data-nav-close href="{{ route('login') }}">Login <small>04</small></a>
                <a data-nav-close href="{{ route('register') }}">Register <small>05</small></a>
            @else
                @if (auth()->user()->isAdmin())
                    <a data-nav-close href="{{ route('admin.dashboard') }}">Admin Dashboard <small>04</small></a>
                @else
                    <a data-nav-close href="{{ route('account.dashboard') }}" @if (request()->routeIs('account.*')) aria-current="page" @endif>My Account <small>04</small></a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout <small>{{ auth()->user()->username }}</small></button>
                </form>
            @endguest
        </nav>
        <p>Section 3-B · Event Driven Programming</p>
    </aside>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-brand">
            <span class="footer-mark">ISSEY</span>
            <p>Modern perfume studies in weather, memory, wood, and skin.</p>
        </div>
        <nav aria-label="Footer navigation">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('shop') }}">Shop</a>
            <a href="{{ route('contacts') }}">Contacts</a>
            <a href="{{ route('wishlist.index') }}">Wishlist</a>
            <a href="{{ route('cart.index') }}">Cart</a>
        </nav>
        <p class="course-credit">Designed &amp; developed by Issey A. Cabangon · EDP 3-B · {{ date('Y') }}</p>
    </footer>

    <div class="toast-region" id="toastRegion" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
