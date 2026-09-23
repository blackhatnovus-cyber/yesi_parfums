@if ($products->isEmpty())
    <div class="admin-empty"><strong>No products found.</strong><p>Adjust the filters or add a new fragrance.</p></div>
@else
    <div class="admin-table-wrap is-mobile-cards">
        <table class="admin-table product-admin-table mobile-card-table">
            <thead><tr><th>ID</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td data-label="ID">#{{ str_pad((string) $product->id, 3, '0', STR_PAD_LEFT) }}</td>
                        <td data-label="Product"><div class="table-product"><img src="{{ $product->image }}" alt=""><strong>{{ $product->name }}</strong></div></td>
                        <td data-label="Category">{{ $product->categoryRecord?->name ?? $product->category }}</td>
                        <td data-label="Price">₱{{ number_format((float) $product->price, 2) }}</td>
                        <td data-label="Stock"><span class="stock-inline {{ $product->stock <= 5 ? 'is-low' : '' }}">{{ $product->stock }}</span></td>
                        <td data-label="Status">
                            <form method="POST" action="{{ route('admin.products.status', $product) }}" data-status-form>
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="product-status-{{ $product->id }}">Status for {{ $product->name }}</label>
                                <select id="product-status-{{ $product->id }}" name="status" data-status-select>
                                    <option value="active" @selected($product->status === 'active')>Active</option>
                                    <option value="inactive" @selected($product->status === 'inactive')>Inactive</option>
                                </select>
                                <button class="status-save" type="submit">Save</button>
                            </form>
                        </td>
                        <td data-label="Created">{{ $product->created_at->format('M d, Y') }}</td>
                        <td data-label="Actions">
                            <div class="table-actions">
                                <a href="{{ route('admin.products.show', $product) }}">View</a>
                                <a href="{{ route('admin.products.edit', $product) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Are you sure you want to delete this product?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="is-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('admin.partials.pagination', ['paginator' => $products])
@endif
