<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminProductRequest;
use App\Http\Requests\UpdateAdminProductRequest;
use App\Http\Requests\UpdateProductStatusRequest;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminProductController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'status' => ['nullable', Rule::in(Product::STATUSES)],
        ]);
        $query = trim((string) ($validated['q'] ?? ''));
        $categoryId = isset($validated['category']) ? (int) $validated['category'] : null;
        $status = (string) ($validated['status'] ?? '');
        $products = Product::query()
            ->with('categoryRecord:id,name')
            ->when($query !== '', function (Builder $builder) use ($query): void {
                $builder->where(function (Builder $search) use ($query): void {
                    $search->where('name', 'like', "%{$query}%")
                        ->orWhere('category', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                });
            })
            ->when($categoryId !== null, fn (Builder $builder): Builder => $builder->where('category_id', $categoryId))
            ->when($status !== '', fn (Builder $builder): Builder => $builder->where('status', $status))
            ->latest('created_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.products._table', compact('products'))->render(),
                'count' => $products->total(),
            ]);
        }

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminProductRequest $request): RedirectResponse
    {
        $category = Category::query()->findOrFail($request->integer('category_id'));
        $attributes = $request->safe()->only(['name', 'description', 'price', 'stock', 'status']);
        $attributes['category_id'] = $category->id;
        $attributes['category'] = $category->name;
        $attributes['image'] = $request->hasFile('image')
            ? '/storage/'.$request->file('image')->store('product-images', 'public')
            : '/images/signature.jpg';

        Product::query()->create($attributes);

        return redirect()->route('admin.products.index')->with('status', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        $product->load('categoryRecord:id,name');

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminProductRequest $request, Product $product): RedirectResponse
    {
        $category = Category::query()->findOrFail($request->integer('category_id'));
        $attributes = $request->safe()->only(['name', 'description', 'price', 'stock', 'status']);
        $attributes['category_id'] = $category->id;
        $attributes['category'] = $category->name;
        $priorImage = $product->image;

        if ($request->hasFile('image')) {
            $attributes['image'] = '/storage/'.$request->file('image')->store('product-images', 'public');
        }

        $product->update($attributes);

        if (isset($attributes['image'])) {
            $this->deleteUnusedAdminImage($priorImage);
        }

        return redirect()->route('admin.products.index')->with('status', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        DB::transaction(function () use ($product): void {
            $product->cartItems()->delete();
            $product->wishlistItems()->delete();
            $product->delete();
        });

        return redirect()->route('admin.products.index')->with('status', 'Product deleted successfully.');
    }

    public function updateStatus(UpdateProductStatusRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $product->update(['status' => $request->validated('status')]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Product status updated successfully.',
                'status' => $product->status,
            ]);
        }

        return back()->with('status', 'Product status updated successfully.');
    }

    private function deleteUnusedAdminImage(string $image): void
    {
        if (! Str::startsWith($image, '/storage/product-images/')) {
            return;
        }

        if (OrderItem::query()->where('product_image', $image)->exists()) {
            return;
        }

        Storage::disk('public')->delete(Str::after($image, '/storage/'));
    }
}
