@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('context', 'Atelier overview · live database')

@section('content')
    <section class="metric-ledger" aria-label="Store metrics">
        <article class="metric-entry"><p>Total products</p><strong>{{ number_format($metrics['products']) }}</strong><span>Current catalogue</span></article>
        <article class="metric-entry"><p>Total orders</p><strong>{{ number_format($metrics['orders']) }}</strong><span>All order records</span></article>
        <article class="metric-entry"><p>Customers</p><strong>{{ number_format($metrics['customers']) }}</strong><span>Customer accounts</span></article>
        <article class="metric-entry"><p>Completed sales</p><strong>₱{{ number_format($metrics['sales'], 2) }}</strong><span>Completed orders only</span></article>
        <article class="metric-entry is-warning"><p>Low stock</p><strong>{{ number_format($metrics['lowStock']) }}</strong><span>Five bottles or fewer</span></article>
    </section>

    <div class="dashboard-columns">
        <section class="admin-surface" aria-labelledby="recent-orders-title">
            <header class="surface-heading">
                <div><p>Dispatch ledger</p><h2 id="recent-orders-title">Recent orders</h2></div>
                <a href="{{ route('admin.orders.index') }}">View all</a>
            </header>

            @if ($recentOrders->isEmpty())
                <div class="admin-empty"><strong>No orders yet.</strong><p>Customer checkouts will appear here.</p></div>
            @else
                <div class="admin-table-wrap is-mobile-cards">
                    <table class="admin-table dashboard-orders-table mobile-card-table">
                        <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td data-label="Order"><strong>{{ $order->order_number }}</strong></td>
                                    <td data-label="Customer">{{ $order->user->name }}</td>
                                    <td data-label="Date">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td data-label="Total">₱{{ number_format((float) $order->total, 2) }}</td>
                                    <td data-label="Status"><span class="status-badge is-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                                    <td data-label="Action"><a class="table-action" href="{{ route('admin.orders.show', $order) }}">Open</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="admin-surface low-stock-panel" aria-labelledby="low-stock-title">
            <header class="surface-heading"><div><p>Inventory watch</p><h2 id="low-stock-title">Low stock</h2></div></header>
            @if ($lowStockProducts->isEmpty())
                <div class="admin-empty"><strong>Stock levels are healthy.</strong><p>No active product is at or below five bottles.</p></div>
            @else
                <div class="stock-list">
                    @foreach ($lowStockProducts as $product)
                        <article>
                            <img src="{{ $product->image }}" alt="">
                            <div><strong>{{ $product->name }}</strong><small>{{ $product->category }}</small></div>
                            <span class="stock-number">{{ $product->stock }}</span>
                            <span class="status-badge {{ $product->stock === 0 ? 'is-cancelled' : 'is-warning' }}">{{ $product->stock === 0 ? 'Out' : 'Low' }}</span>
                            <a class="table-action" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
