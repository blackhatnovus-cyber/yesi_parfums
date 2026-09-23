<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        User::query()->create($request->safe()->only([
            'name', 'username', 'email', 'password',
        ]));

        return redirect()->route('login')
            ->with('status', 'Account created successfully. Please log in with your new username and password.');
    }
}
