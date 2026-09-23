@extends('layouts.admin')

@section('title', 'Message from '.$message->name)
@section('page-title', 'Message Detail')
@section('context', 'Atelier correspondence · message #'.$message->id)

@section('content')
    <article class="admin-surface message-detail">
        <header>
            <div>
                <p class="record-kicker">Received {{ $message->created_at->format('F d, Y · H:i') }}</p>
                <h2>{{ $message->name }}</h2>
                <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
            </div>
            <span class="status-badge is-{{ $message->status }}">{{ ucfirst($message->status) }}</span>
        </header>
        <div class="message-body preserve-lines">{{ $message->message }}</div>
        <div class="admin-form-actions">
            @if ($message->status === 'unread')
                <form method="POST" action="{{ route('admin.messages.update', $message) }}" data-async-status>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="read">
                    <button class="admin-button" type="submit">Mark as Read</button>
                </form>
            @endif
            @if ($message->status !== 'replied')
                <form method="POST" action="{{ route('admin.messages.update', $message) }}" data-async-status>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="replied">
                    <button class="admin-button is-primary" type="submit">Mark as Replied</button>
                </form>
            @endif
            <a class="admin-button" href="{{ route('admin.messages.index') }}">Back to Messages</a>
        </div>
    </article>
@endsection
