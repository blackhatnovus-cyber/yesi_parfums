@if ($orders->isEmpty())
    <div class="admin-empty"><strong>No orders found.</strong><p>Customer checkout records will appear here.</p></div>
@else
    <div class="admin-table-wrap is-mobile-cards">
        <table class="admin-table orders-admin-table mobile-card-table">
            <thead><tr><th>Order number</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td data-label="Order"><strong>{{ $order->order_number }}</strong></td>
                        <td data-label="Customer"><span>{{ $order->shipping_name }}</span><small>{{ $order->user->username }}</small></td>
                        <td data-label="Date">{{ $order->created_at->format('M d, Y · H:i') }}</td>
                        <td data-label="Total">₱{{ number_format((float) $order->total, 2) }}</td>
                        <td data-label="Status">
                            <form method="POST" action="{{ route('admin.orders.update', $order) }}" data-status-form>
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="order-status-{{ $order->id }}">Status for {{ $order->order_number }}</label>
                                <select id="order-status-{{ $order->id }}" name="status" data-status-select>
                                    @foreach (\App\Models\Order::STATUSES as $status)
                                        <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <button class="status-save" type="submit">Save</button>
                            </form>
                        </td>
                        <td data-label="Action"><a class="table-action" href="{{ route('admin.orders.show', $order) }}">View order</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $orders])
@endif
