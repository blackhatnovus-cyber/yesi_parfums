<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $customer */
        $customer = $request->user();
        $orders = $customer->orders()
            ->withCount('items')
            ->latest('created_at')
            ->latest('id')
            ->paginate(8);

        return view('account.orders', compact('orders'));
    }
}
