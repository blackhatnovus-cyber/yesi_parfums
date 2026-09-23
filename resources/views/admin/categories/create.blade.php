@extends('layouts.admin')

@section('title', 'Add Category')
@section('page-title', 'Add Category')
@section('context', 'Catalogue taxonomy · new family')

@section('content')
    <section class="admin-surface form-surface form-surface-narrow">
        <header class="surface-heading"><div><p>New scent family</p><h2>Define a category</h2></div></header>
        <form method="POST" action="{{ route('admin.categories.store') }}" data-admin-form>
            @csrf
            @include('admin.categories._form')
        </form>
    </section>
@endsection
