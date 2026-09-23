@extends('layouts.app')

@section('title', 'Contacts — Issey Parfums')

@section('content')
    <section class="form-page">
        <aside class="form-aside">
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Contacts</span></nav>
            <p class="eyebrow">Correspondence</p>
            <h1>Leave a note at the atelier.</h1>
            <p>Questions about the collection, the coursework, or a particular composition are welcome.</p>
            <dl class="contact-list">
                <div><dt>Visit</dt><dd>Salcedo Village, Makati<br>By appointment · Tue–Sat</dd></div>
                <div><dt>Write</dt><dd>atelier@isseyparfums.test</dd></div>
                <div><dt>Course</dt><dd>Event Driven Programming · Section 3-B</dd></div>
            </dl>
        </aside>
        <div class="form-panel">
            @if (session('status'))<div class="notice success" role="status">{{ session('status') }}</div>@endif
            <form id="contactForm" method="POST" action="{{ route('contacts.store') }}" novalidate>
                @csrf
                <div class="field @error('name') has-error @enderror">
                    <label for="contactName">Name</label>
                    <input id="contactName" name="name" value="{{ old('name') }}" autocomplete="name" required>
                    @error('name')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('email') has-error @enderror">
                    <label for="contactEmail">Email</label>
                    <input id="contactEmail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    @error('email')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('message') has-error @enderror">
                    <label for="contactMessage">Message</label>
                    <textarea id="contactMessage" name="message" rows="7" required>{{ old('message') }}</textarea>
                    @error('message')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <button class="button-solid" type="submit">Send Message</button>
                <p class="form-live" aria-live="polite"></p>
            </form>
        </div>
    </section>
@endsection
