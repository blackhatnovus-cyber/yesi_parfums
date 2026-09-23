@extends('layouts.app')

@section('title', 'Login — Issey Parfums')

@section('content')
    <section class="auth-layout">
        <div class="auth-image">
            <img src="/images/noir-essence.jpg" alt="Dark glass perfume bottle in a warm editorial studio">
            <div><span>PRIVATE EDIT / 01</span><p>Return to the collection you kept.</p></div>
        </div>
        <div class="auth-panel">
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Login</span></nav>
            <p class="eyebrow">Private account</p>
            <h1>Welcome back.</h1>
            <p class="auth-intro">Sign in to keep a database-backed cart and wishlist across your session.</p>
            @if (session('status'))
                <div class="notice success" role="status">{{ session('status') }}</div>
            @endif
            <form id="loginForm" method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf
                <div class="field @error('username') has-error @enderror">
                    <label for="username">Username</label>
                    <input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required data-auth-input>
                    @error('username')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('password') has-error @enderror">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required data-auth-input>
                    @error('password')<p class="field-error">{{ $message }}</p>@enderror
                </div>
                <label class="remember-row"><input id="remember" name="remember" type="checkbox" value="1" {{ old('remember') ? 'checked' : '' }}> <span>Remember Me</span></label>
                <p id="rememberStatus" class="form-live" aria-live="polite">{{ old('remember') ? 'Remembered session enabled.' : 'Normal session selected.' }}</p>
                <div class="form-actions">
                    <button class="button-solid" type="submit">LOGIN</button>
                    <a id="forgotPasswordLink" class="editorial-link" href="{{ route('password.request') }}">Forgot Password</a>
                </div>
            </form>
            <p class="account-switch">New to the atelier? <a class="editorial-link" href="{{ route('register') }}">Create account <span aria-hidden="true">↗</span></a></p>
        </div>
    </section>
@endsection
