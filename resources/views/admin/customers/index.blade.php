@extends('layouts.admin')

@section('title', 'Customers')
@section('page-title', 'Customers')
@section('context', 'Customer directory')

@section('content')
    <section class="admin-surface">
        <header class="surface-heading"><div><p>Account registry</p><h2>Customer management</h2></div></header>
        <form class="admin-filters" method="GET" action="{{ route('admin.customers.index') }}" data-admin-search-form data-search-endpoint="{{ route('admin.customers.index') }}">
            <div class="admin-field is-search">
                <label for="customerSearch">Search customers</label>
                <input id="customerSearch" name="q" type="search" value="{{ request('q') }}" placeholder="ID, name, username, or email" data-debounced-search>
            </div>
            <button class="admin-button" type="submit">Search</button>
        </form>
        <p class="result-count"><span data-result-count>{{ $customers->total() }}</span> customer(s)</p>
        <div data-admin-table-region aria-live="polite" aria-busy="false">
            @include('admin.customers._table', ['customers' => $customers])
        </div>
    </section>
@endsection
