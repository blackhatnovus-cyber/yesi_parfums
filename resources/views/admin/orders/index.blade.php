@extends('layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Orders')
@section('context', 'Dispatch and fulfilment')

@section('content')
    <section class="admin-surface">
        <header class="surface-heading"><div><p>Order ledger</p><h2>Customer orders</h2></div></header>
        <form class="admin-filters" method="GET" action="{{ route('admin.orders.index') }}" data-admin-search-form data-search-endpoint="{{ route('admin.orders.index') }}">
            <div class="admin-field is-search">
                <label for="orderSearch">Search orders</label>
                <input id="orderSearch" name="q" type="search" value="{{ request('q') }}" placeholder="Order, customer, or email" data-debounced-search>
            </div>
            <div class="admin-field">
                <label for="orderStatus">Status</label>
                <select id="orderStatus" name="status" data-immediate-filter>
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Order::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="admin-button" type="submit">Apply</button>
        </form>
        <p class="result-count"><span data-result-count>{{ $orders->total() }}</span> order(s)</p>
        <div data-admin-table-region aria-live="polite" aria-busy="false">
            @include('admin.orders._table', ['orders' => $orders])
        </div>
    </section>
@endsection
