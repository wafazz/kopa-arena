<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private function branchId()
    {
        return auth()->user()->branch_id;
    }

    public function index()
    {
        $products = Product::with('category')->where('branch_id', $this->branchId())->latest()->get();
        return view('branch.products.index', compact('products'));
    }

    public function create()
    {
        $categories = ProductCategory::where('status', 'active')->orderBy('sort_order')->get();
        return view('branch.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'has_variation' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,draft,out_of_stock',
            'gallery.*' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'variations' => 'nullable|array',
            'variations.*.name' => 'required_with:variations|string|max:255',
            'variations.*.sku' => 'nullable|string|max:100',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
        ]);

        $hasVariation = $request->boolean('has_variation');

        $product = Product::create([
            'branch_id' => $this->branchId(),
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(4),
            'description' => $request->description,
            'has_variation' => $hasVariation,
            'price' => $hasVariation ? 0 : ($request->price ?? 0),
            'stock' => $hasVariation ? 0 : ($request->stock ?? 0),
            'sku' => $hasVariation ? null : $request->sku,
            'weight' => $request->weight ?? 0,
            'status' => $request->status,
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'product_' . $product->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $product->update(['image' => 'uploads/products/' . $filename]);
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $i => $file) {
                $filename = 'gallery_' . $product->id . '_' . time() . '_' . $i . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/products'), $filename);
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => 'uploads/products/' . $filename,
                    'sort_order' => $i,
                ]);
            }
        }

        if ($hasVariation && $request->variations) {
            foreach ($request->variations as $v) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'name' => $v['name'],
                    'sku' => $v['sku'] ?? null,
                    'price' => $v['price'],
                    'stock' => $v['stock'] ?? 0,
                    'status' => 'active',
                ]);
            }
        }

        ActivityLog::log('store', 'Product', $product->id, $product->name);
        return redirect()->route('branch.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        if ($product->branch_id !== $this->branchId()) {
            abort(403);
        }
        $product->load('images', 'variations');
        $categories = ProductCategory::where('status', 'active')->orderBy('sort_order')->get();
        return view('branch.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->branch_id !== $this->branchId()) {
            abort(403);
        }

        $request->validate([
            'category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'has_variation' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|max:100',
            'weight' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,draft,out_of_stock',
            'gallery.*' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'remove_images' => 'nullable|array',
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.name' => 'required_with:variations|string|max:255',
            'variations.*.sku' => 'nullable|string|max:100',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
        ]);

        $hasVariation = $request->boolean('has_variation');

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'has_variation' => $hasVariation,
            'price' => $hasVariation ? 0 : ($request->price ?? 0),
            'stock' => $hasVariation ? 0 : ($request->stock ?? 0),
            'sku' => $hasVariation ? null : $request->sku,
            'weight' => $request->weight ?? 0,
            'status' => $request->status,
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                unlink(public_path($product->image));
            }
            $file = $request->file('image');
            $filename = 'product_' . $product->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $product->update(['image' => 'uploads/products/' . $filename]);
        }

        if ($request->remove_images) {
            $toRemove = ProductImage::whereIn('id', $request->remove_images)->where('product_id', $product->id)->get();
            foreach ($toRemove as $img) {
                if (file_exists(public_path($img->image))) {
                    unlink(public_path($img->image));
                }
                $img->delete();
            }
        }

        if ($request->hasFile('gallery')) {
            $maxSort = $product->images()->max('sort_order') ?? 0;
            foreach ($request->file('gallery') as $i => $file) {
                $filename = 'gallery_' . $product->id . '_' . time() . '_' . $i . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/products'), $filename);
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => 'uploads/products/' . $filename,
                    'sort_order' => $maxSort + $i + 1,
                ]);
            }
        }

        $existingIds = [];
        if ($hasVariation && $request->variations) {
            foreach ($request->variations as $v) {
                if (!empty($v['id'])) {
                    $variation = ProductVariation::find($v['id']);
                    if ($variation && $variation->product_id === $product->id) {
                        $variation->update([
                            'name' => $v['name'],
                            'sku' => $v['sku'] ?? null,
                            'price' => $v['price'],
                            'stock' => $v['stock'] ?? 0,
                        ]);
                        $existingIds[] = $variation->id;
                    }
                } else {
                    $new = ProductVariation::create([
                        'product_id' => $product->id,
                        'name' => $v['name'],
                        'sku' => $v['sku'] ?? null,
                        'price' => $v['price'],
                        'stock' => $v['stock'] ?? 0,
                        'status' => 'active',
                    ]);
                    $existingIds[] = $new->id;
                }
            }
        }

        $product->variations()->whereNotIn('id', $existingIds)->delete();

        ActivityLog::log('update', 'Product', $product->id, $product->name);
        return redirect()->route('branch.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->branch_id !== $this->branchId()) {
            abort(403);
        }
        ActivityLog::log('destroy', 'Product', $product->id, $product->name);
        if ($product->image && file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }
        foreach ($product->images as $img) {
            if (file_exists(public_path($img->image))) {
                unlink(public_path($img->image));
            }
            $img->delete();
        }
        $product->delete();
        return redirect()->route('branch.products.index')->with('success', 'Product deleted successfully.');
    }
}
