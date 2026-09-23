@extends('layouts.admin')

@section('title', 'Add Product')
@section('page-title', 'Add Product')
@section('context', 'Catalogue · new formula')

@section('content')
    <section class="admin-surface form-surface">
        <header class="surface-heading"><div><p>New catalogue entry</p><h2>Compose the listing</h2></div></header>
        @if ($categories->isEmpty())
            <div class="admin-alert">Create a <a href="{{ route('admin.categories.create') }}">category</a> before adding a product.</div>
        @endif
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" data-admin-form>
            @csrf
            @include('admin.products._form')
        </form>
    </section>
@endsection
