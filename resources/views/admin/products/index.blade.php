@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products')
@section('context', 'Catalogue and inventory')

@section('content')
    <section class="admin-surface">
        <header class="surface-heading surface-heading-actions">
            <div><p>Formula catalogue</p><h2>Product management</h2></div>
            <a class="admin-button is-primary" href="{{ route('admin.products.create') }}">Add Product</a>
        </header>

        <form class="admin-filters" method="GET" action="{{ route('admin.products.index') }}" data-admin-search-form data-search-endpoint="{{ route('admin.products.index') }}">
            <div class="admin-field is-search">
                <label for="productSearch">Search products</label>
                <input id="productSearch" name="q" type="search" value="{{ request('q') }}" placeholder="Name, category, or note" data-debounced-search>
            </div>
            <div class="admin-field">
                <label for="productCategory">Category</label>
                <select id="productCategory" name="category" data-immediate-filter>
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-field">
                <label for="productStatus">Status</label>
                <select id="productStatus" name="status" data-immediate-filter>
                    <option value="">All statuses</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <button class="admin-button" type="submit">Apply</button>
        </form>

        <p class="result-count" aria-live="polite"><span data-result-count>{{ $products->total() }}</span> product(s)</p>
        <div data-admin-table-region aria-live="polite" aria-busy="false">
            @include('admin.products._table', ['products' => $products])
        </div>
    </section>
@endsection
