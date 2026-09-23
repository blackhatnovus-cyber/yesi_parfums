<div class="admin-form-grid is-single">
    <div class="admin-field">
        <label for="name">Category name</label>
        <input id="name" name="name" value="{{ old('name', $category->name ?? '') }}" required maxlength="255">
        @error('name')<p>{{ $message }}</p>@enderror
    </div>
    <div class="admin-field">
        <label for="description">Description <span>Optional</span></label>
        <textarea id="description" name="description" rows="6" maxlength="2000">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')<p>{{ $message }}</p>@enderror
    </div>
</div>
<div class="admin-form-actions">
    <button class="admin-button is-primary" type="submit">{{ isset($category) ? 'Save Changes' : 'Save Category' }}</button>
    <a class="admin-button" href="{{ route('admin.categories.index') }}">Cancel</a>
</div>
