<div class="admin-form-grid">
    <div class="admin-field admin-field-wide">
        <label for="name">Product name</label>
        <input id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required maxlength="255">
        @error('name')<p>{{ $message }}</p>@enderror
    </div>
    <div class="admin-field">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>
            <option value="">Choose a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p>{{ $message }}</p>@enderror
    </div>
    <div class="admin-field">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $product->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
        @error('status')<p>{{ $message }}</p>@enderror
    </div>
    <div class="admin-field">
        <label for="price">Price (PHP)</label>
        <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $product->price ?? '') }}" required>
        @error('price')<p>{{ $message }}</p>@enderror
    </div>
    <div class="admin-field">
        <label for="stock">Stock</label>
        <input id="stock" name="stock" type="number" min="0" step="1" value="{{ old('stock', $product->stock ?? 0) }}" required>
        @error('stock')<p>{{ $message }}</p>@enderror
    </div>
    <div class="admin-field admin-field-wide">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="7" required maxlength="5000">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')<p>{{ $message }}</p>@enderror
    </div>
    <div class="admin-field admin-field-wide">
        <label for="image">Product image <span>JPEG, PNG, or WebP · max 2 MB</span></label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-image-input>
        @error('image')<p>{{ $message }}</p>@enderror
        <div class="image-preview" data-image-preview>
            <img src="{{ $product->image ?? '/images/signature.jpg' }}" alt="Current product preview">
            <small>{{ isset($product) ? 'Current image remains if no file is selected.' : 'Preview' }}</small>
        </div>
    </div>
</div>

<div class="admin-form-actions">
    <button class="admin-button is-primary" type="submit">{{ isset($product) ? 'Save Changes' : 'Save Product' }}</button>
    <a class="admin-button" href="{{ route('admin.products.index') }}">Cancel</a>
</div>
