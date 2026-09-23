<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminCategoryRequest;
use App\Http\Requests\UpdateAdminCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount(['products' => fn (Builder $query): Builder => $query->withTrashed()])
            ->orderBy('name')
            ->paginate(12);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminCategoryRequest $request): RedirectResponse
    {
        Category::query()->create($request->validated());

        return redirect()->route('admin.categories.index')->with('status', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminCategoryRequest $request, Category $category): RedirectResponse
    {
        DB::transaction(function () use ($request, $category): void {
            $category->update($request->validated());
            Product::query()
                ->withTrashed()
                ->where('category_id', $category->id)
                ->update(['category' => $category->name]);
        });

        return redirect()->route('admin.categories.index')->with('status', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $hasLinkedProducts = Product::query()
            ->withTrashed()
            ->where('category_id', $category->id)
            ->exists();

        if ($hasLinkedProducts) {
            return back()->withErrors([
                'category' => 'This category cannot be deleted while products are linked to it.',
            ]);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted successfully.');
    }
}
