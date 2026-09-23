<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMessageStatusRequest;
use App\Models\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminMessageController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $query = trim((string) ($validated['q'] ?? ''));
        $messages = ContactMessage::query()
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $search) use ($query): void {
                    $search->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('message', 'like', "%{$query}%");
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function show(ContactMessage $message): View
    {
        return view('admin.messages.show', compact('message'));
    }

    public function update(UpdateMessageStatusRequest $request, ContactMessage $message): JsonResponse|RedirectResponse
    {
        $message->update(['status' => $request->validated('status')]);
        $confirmation = $message->status === 'replied'
            ? 'Message marked as replied.'
            : 'Message marked as read.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $confirmation,
                'status' => $message->status,
            ]);
        }

        return back()->with('status', $confirmation);
    }
}
