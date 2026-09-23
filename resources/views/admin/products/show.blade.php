@extends('layouts.admin')

@section('title', $product->name)
@section('page-title', 'Product Detail')
@section('context', 'Catalogue · product #'.$product->id)

@section('content')
    <article class="admin-surface product-record">
        <div class="product-record-image"><img src="{{ $product->image }}" alt="{{ $product->name }} bottle"></div>
        <div class="product-record-copy">
            <p class="record-kicker">{{ $product->categoryRecord?->name ?? $product->category }}</p>
            <h2>{{ $product->name }}</h2>
            <p class="record-price">₱{{ number_format((float) $product->price, 2) }}</p>
            <p>{{ $product->description }}</p>
            <dl class="record-facts">
                <div><dt>Stock</dt><dd>{{ $product->stock }} bottle(s)</dd></div>
                <div><dt>Status</dt><dd><span class="status-badge is-{{ $product->status }}">{{ ucfirst($product->status) }}</span></dd></div>
                <div><dt>Created</dt><dd>{{ $product->created_at->format('F d, Y') }}</dd></div>
            </dl>
            <div class="admin-form-actions">
                <a class="admin-button is-primary" href="{{ route('admin.products.edit', $product) }}">Edit Product</a>
                <a class="admin-button" href="{{ route('admin.products.index') }}">Back to Products</a>
            </div>
        </div>
    </article>
@endsection
