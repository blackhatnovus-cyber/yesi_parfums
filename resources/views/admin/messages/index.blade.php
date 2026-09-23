@extends('layouts.admin')

@section('title', 'Messages')
@section('page-title', 'Messages')
@section('context', 'Atelier correspondence')

@section('content')
    <section class="admin-surface">
        <header class="surface-heading"><div><p>Correspondence desk</p><h2>Contact messages</h2></div></header>
        <form class="admin-filters" method="GET" action="{{ route('admin.messages.index') }}">
            <div class="admin-field is-search">
                <label for="messageSearch">Search messages</label>
                <input id="messageSearch" name="q" type="search" value="{{ request('q') }}" placeholder="Name, email, or message">
            </div>
            <button class="admin-button" type="submit">Search</button>
        </form>

        @if ($messages->isEmpty())
            <div class="admin-empty"><strong>No messages found.</strong><p>Customer contact submissions will appear here.</p></div>
        @else
            <div class="message-list">
                @foreach ($messages as $message)
                    <article class="message-row {{ $message->status === 'unread' ? 'is-unread' : '' }}">
                        <span class="message-marker" aria-hidden="true"></span>
                        <div class="message-sender"><strong>{{ $message->name }}</strong><small>{{ $message->email }}</small></div>
                        <p>{{ \Illuminate\Support\Str::limit($message->message, 115) }}</p>
                        <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('M d, Y · H:i') }}</time>
                        <span class="status-badge is-{{ $message->status }}">{{ ucfirst($message->status) }}</span>
                        <a class="table-action" href="{{ route('admin.messages.show', $message) }}">Open</a>
                    </article>
                @endforeach
            </div>
            @include('admin.partials.pagination', ['paginator' => $messages])
        @endif
    </section>
@endsection
