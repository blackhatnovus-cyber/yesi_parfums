<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $query = trim((string) ($validated['q'] ?? ''));
        $customers = User::query()
            ->select(['id', 'name', 'username', 'email', 'status', 'created_at'])
            ->where('role', 'customer')
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $search) use ($query): void {
                    $search->where('name', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");

                    if (ctype_digit($query)) {
                        $search->orWhere('id', (int) $query);
                    }
                });
            })
            ->latest('created_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.customers._table', compact('customers'))->render(),
                'count' => $customers->total(),
            ]);
        }

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        $customer = User::query()
            ->select(['id', 'name', 'username', 'email', 'status', 'created_at'])
            ->whereKey($customer->id)
            ->where('role', 'customer')
            ->with(['orders' => fn ($query) => $query->latest('created_at')->latest('id')->limit(10)])
            ->firstOrFail();

        return view('admin.customers.show', compact('customer'));
    }
}
