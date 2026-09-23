@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories')
@section('context', 'Catalogue taxonomy')

@section('content')
    <section class="admin-surface">
        <header class="surface-heading surface-heading-actions">
            <div><p>Scent families</p><h2>Category management</h2></div>
            <a class="admin-button is-primary" href="{{ route('admin.categories.create') }}">Add Category</a>
        </header>

        @if ($categories->isEmpty())
            <div class="admin-empty"><strong>No categories yet.</strong><p>Add a scent family before creating products.</p></div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>Name</th><th>Description</th><th>Linked products</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td>{{ $category->description ?: 'No description' }}</td>
                                <td>{{ $category->products_count }}</td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Are you sure you want to delete this category?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="is-danger" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $categories])
        @endif
    </section>
@endsection
