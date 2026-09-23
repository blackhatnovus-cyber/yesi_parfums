@extends('layouts.app')

@section('title', 'Create an Account — Issey Parfums')

@section('content')
    <section class="auth-layout register-layout">
        <div class="auth-image">
            <img src="/images/noir-essence.jpg" alt="Dark glass perfume bottle in a warm editorial studio">
            <div><span>PRIVATE EDIT / 02</span><p>A place for the scents you love.</p></div>
        </div>
        <div class="auth-panel register-panel">
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><span>Create account</span></nav>
            <p class="eyebrow">New account</p>
            <h1>Create your account</h1>
            <p class="auth-intro">Save your favourite scents, keep your cart, and make your next visit feel like yours.</p>

            @if ($errors->any())
                <div class="notice register-error-summary" role="alert">Please review the highlighted fields below.</div>
            @endif

            <form id="registerForm" method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="field @error('name') has-error @enderror">
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                    @error('name')<p id="name-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('username') has-error @enderror">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
                    @error('username')<p id="username-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('email') has-error @enderror">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('password') has-error @enderror">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    @error('password')<p id="password-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <div class="field @error('password_confirmation') has-error @enderror">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required @error('password_confirmation') aria-invalid="true" aria-describedby="password-confirmation-error" @enderror>
                    @error('password_confirmation')<p id="password-confirmation-error" class="field-error">{{ $message }}</p>@enderror
                </div>
                <button class="button-solid register-submit" type="submit">CREATE ACCOUNT</button>
            </form>

            <p class="account-switch">Already part of the atelier? <a class="editorial-link" href="{{ route('login') }}">Log in <span aria-hidden="true">↗</span></a></p>
        </div>
    </section>
@endsection
