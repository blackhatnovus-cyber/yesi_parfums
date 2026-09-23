@extends('layouts.admin')

@section('title', $customer->name)
@section('page-title', 'Customer Detail')
@section('context', 'Account registry · customer #'.$customer->id)

@section('content')
    <div class="customer-detail-grid">
        <aside class="admin-surface customer-profile-card">
            <span class="large-avatar">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
            <p class="record-kicker">Customer account</p>
            <h2>{{ $customer->name }}</h2>
            <dl class="record-facts">
                <div><dt>Username</dt><dd>{{ '@'.$customer->username }}</dd></div>
                <div><dt>Email</dt><dd>{{ $customer->email }}</dd></div>
                <div><dt>Status</dt><dd><span class="status-badge is-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span></dd></div>
                <div><dt>Registered</dt><dd>{{ $customer->created_at->format('F d, Y') }}</dd></div>
            </dl>
            <a class="admin-button" href="{{ route('admin.customers.index') }}">Back to Customers</a>
        </aside>

        <section class="admin-surface">
            <header class="surface-heading"><div><p>Order history</p><h2>Latest orders</h2></div></header>
            @if ($customer->orders->isEmpty())
                <div class="admin-empty"><strong>No orders yet.</strong><p>This customer has not completed checkout.</p></div>
            @else
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($customer->orders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>₱{{ number_format((float) $order->total, 2) }}</td>
                                    <td><span class="status-badge is-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                                    <td><a class="table-action" href="{{ route('admin.orders.show', $order) }}">Open</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
