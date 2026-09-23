@extends('layouts.admin')

@section('title', $order->order_number)
@section('page-title', 'Order Detail')
@section('context', 'Dispatch ledger · '.$order->order_number)

@section('content')
    <div class="order-detail-grid">
        <section class="admin-surface">
            <header class="surface-heading surface-heading-actions">
                <div><p>Placed {{ $order->created_at->format('F d, Y · H:i') }}</p><h2>{{ $order->order_number }}</h2></div>
                <span class="status-badge is-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
            </header>

            <div class="admin-table-wrap">
                <table class="admin-table order-items-table">
                    <thead><tr><th>Product</th><th>Quantity</th><th>Unit price</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td><div class="table-product"><img src="{{ $item->product_image ?: '/images/signature.jpg' }}" alt=""><strong>{{ $item->product_name }}</strong></div></td>
                                <td>{{ $item->quantity }}</td>
                                <td>₱{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td><strong>₱{{ number_format((float) $item->subtotal, 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><th colspan="3">Order total</th><td>₱{{ number_format((float) $order->total, 2) }}</td></tr></tfoot>
                </table>
            </div>
        </section>

        <aside class="admin-surface order-sidebar">
            <section>
                <p class="record-kicker">Customer snapshot</p>
                <h2>{{ $order->shipping_name }}</h2>
                <dl class="record-facts">
                    <div><dt>Account</dt><dd>{{ $order->user->name }} · {{ '@'.$order->user->username }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $order->shipping_email }}</dd></div>
                    <div><dt>Address</dt><dd class="preserve-lines">{{ $order->shipping_address }}</dd></div>
                </dl>
            </section>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" data-status-form>
                @csrf
                @method('PATCH')
                <div class="admin-field">
                    <label for="detailOrderStatus">Order status</label>
                    <select id="detailOrderStatus" name="status" data-status-select>
                        @foreach (\App\Models\Order::STATUSES as $status)
                            <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="admin-button is-primary status-save" type="submit">Update Status</button>
            </form>
            <a class="admin-button" href="{{ route('admin.orders.index') }}">Back to Orders</a>
        </aside>
    </div>
@endsection
