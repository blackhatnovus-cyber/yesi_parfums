<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('contacts');
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::create($request->validated());

        return back()->with('status', 'Message sent successfully.');
    }
}
