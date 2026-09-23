@extends('layouts.admin')

@section('title', 'Edit '.$product->name)
@section('page-title', 'Edit Product')
@section('context', 'Catalogue · '.$product->name)

@section('content')
    <section class="admin-surface form-surface">
        <header class="surface-heading"><div><p>Product #{{ $product->id }}</p><h2>Refine the listing</h2></div></header>
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" data-admin-form>
            @csrf
            @method('PUT')
            @include('admin.products._form')
        </form>
    </section>
@endsection
