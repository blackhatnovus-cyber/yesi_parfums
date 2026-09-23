@extends('layouts.admin')

@section('title', 'Edit '.$category->name)
@section('page-title', 'Edit Category')
@section('context', 'Catalogue taxonomy · '.$category->name)

@section('content')
    <section class="admin-surface form-surface form-surface-narrow">
        <header class="surface-heading"><div><p>Existing scent family</p><h2>Refine the category</h2></div></header>
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" data-admin-form>
            @csrf
            @method('PUT')
            @include('admin.categories._form')
        </form>
    </section>
@endsection
