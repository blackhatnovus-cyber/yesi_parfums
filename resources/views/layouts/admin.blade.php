<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Issey Parfums management atelier">
    <title>@yield('title', 'Admin') · Issey Parfums</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body">
    <a class="admin-skip-link" href="#admin-main">Skip to content</a>

    <div class="admin-shell">
        <div class="admin-sidebar-overlay" data-sidebar-overlay hidden></div>
        <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin navigation" aria-hidden="false">
            <div class="admin-wordmark">
                <span>ISSEY</span>
                <small>PARFUMS / ADMIN</small>
            </div>

            <nav class="admin-nav">
                <a class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <span>01</span> Dashboard
                </a>
                <a class="{{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}" href="{{ route('admin.products.index') }}">
                    <span>02</span> Products
                </a>
                <a class="{{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}" href="{{ route('admin.categories.index') }}">
                    <span>03</span> Categories
                </a>
                <a class="{{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}" href="{{ route('admin.orders.index') }}">
                    <span>04</span> Orders
                </a>
                <a class="{{ request()->routeIs('admin.customers.*') ? 'is-active' : '' }}" href="{{ route('admin.customers.index') }}">
                    <span>05</span> Customers
                </a>
                <a class="{{ request()->routeIs('admin.messages.*') ? 'is-active' : '' }}" href="{{ route('admin.messages.index') }}">
                    <span>06</span> Messages
                    @if ($unreadMessageCount > 0)<b>{{ $unreadMessageCount }}</b>@endif
                </a>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ route('home') }}">← Back to Website</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Logout</button>
                </form>
            </div>
        </aside>

        <div class="admin-workspace">
            <header class="admin-topbar">
                <button class="admin-menu-button" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="false" aria-label="Open admin navigation">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>
                <div class="admin-page-identity">
                    <p>@yield('context', 'Management atelier')</p>
                    <h1>@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="admin-profile">
                    <a href="{{ route('admin.messages.index') }}" aria-label="{{ $unreadMessageCount }} unread messages" class="admin-notification">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                        @if ($unreadMessageCount > 0)<span>{{ $unreadMessageCount }}</span>@endif
                    </a>
                    <span class="admin-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <div><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></div>
                </div>
            </header>

            <main id="admin-main" class="admin-main">
                @if ($errors->any())
                    <div class="admin-alert is-error" role="alert">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <div id="adminFlash" data-message="{{ session('status') }}" data-type="success" hidden></div>
    <div class="admin-toast-region" id="adminToastRegion" aria-live="polite" aria-atomic="true"></div>
</body>
</html>
