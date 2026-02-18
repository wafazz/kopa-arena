<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::withCount('products')->orderBy('sort_order')->get();
        return view('product-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('product-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = ProductCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(4),
            'status' => $request->status,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        ActivityLog::log('store', 'ProductCategory', $category->id, $category->name);
        return redirect()->route('product-categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product-categories.edit', compact('productCategory'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $productCategory->update([
            'name' => $request->name,
            'status' => $request->status,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        ActivityLog::log('update', 'ProductCategory', $productCategory->id, $productCategory->name);
        return redirect()->route('product-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        ActivityLog::log('destroy', 'ProductCategory', $productCategory->id, $productCategory->name);
        $productCategory->delete();
        return redirect()->route('product-categories.index')->with('success', 'Category deleted successfully.');
    }
}
