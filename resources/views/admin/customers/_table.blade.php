@if ($customers->isEmpty())
    <div class="admin-empty"><strong>No customers found.</strong><p>Try a different name, username, email, or ID.</p></div>
@else
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Registered</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td>#{{ str_pad((string) $customer->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td><strong>{{ $customer->name }}</strong></td>
                        <td>{{ '@'.$customer->username }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->created_at->format('M d, Y') }}</td>
                        <td><span class="status-badge is-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span></td>
                        <td><a class="table-action" href="{{ route('admin.customers.show', $customer) }}">View details</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $customers])
@endif
