<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOrderController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(Order::STATUSES)],
        ]);
        $query = trim((string) ($validated['q'] ?? ''));
        $status = (string) ($validated['status'] ?? '');
        $orders = Order::query()
            ->with('user:id,name,username')
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $search) use ($query): void {
                    $search->where('order_number', 'like', "%{$query}%")
                        ->orWhere('shipping_name', 'like', "%{$query}%")
                        ->orWhere('shipping_email', 'like', "%{$query}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($query): void {
                            $userQuery->where('name', 'like', "%{$query}%")
                                ->orWhere('username', 'like', "%{$query}%");
                        });
                });
            })
            ->when($status !== '', fn (Builder $builder): Builder => $builder->where('status', $status))
            ->latest('created_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.orders._table', compact('orders'))->render(),
                'count' => $orders->total(),
            ]);
        }

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user:id,name,username,email', 'items']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): JsonResponse|RedirectResponse
    {
        $order->update(['status' => $request->validated('status')]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Order status updated successfully.',
                'status' => $order->status,
            ]);
        }

        return back()->with('status', 'Order status updated successfully.');
    }
}
