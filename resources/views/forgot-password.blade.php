@extends('layouts.app')

@section('title', 'Recovery Simulation — Issey Parfums')

@section('content')
    <section class="recovery-page">
        <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('login') }}">Login</a><span>/</span><span>Recovery</span></nav>
        <p class="eyebrow">School-project simulation</p>
        <h1>Password recovery,<br><em>honestly labelled.</em></h1>
        <p>This form validates an email address and demonstrates the recovery event. No mail service is configured, so it does not send an email or change a password.</p>
        @if (session('status'))<div class="notice success" role="status">{{ session('status') }}</div>@endif
        <form method="POST" action="{{ route('password.simulate') }}" novalidate>
            @csrf
            <div class="field @error('email') has-error @enderror">
                <label for="recoveryEmail">Email address</label>
                <input id="recoveryEmail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="button-solid" type="submit">Run Recovery Simulation</button>
        </form>
    </section>
@endsection
