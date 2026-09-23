<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => "Too many login attempts. Try again in {$seconds} seconds."]);
        }

        $credentials = [
            ...$request->safe()->only(['username', 'password']),
            'status' => 'active',
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => 'Invalid username or password.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdmin()) {
            $request->session()->forget('url.intended');

            return redirect()->route('shop');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function forgotPassword(): View
    {
        return view('forgot-password');
    }

    public function simulateRecovery(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:255']]);

        return back()->with('status', 'Recovery simulation complete. No email was sent.');
    }

    private function throttleKey(LoginRequest $request): string
    {
        return 'login:'.Str::lower($request->string('username')->toString()).'|'.$request->ip();
    }
}
