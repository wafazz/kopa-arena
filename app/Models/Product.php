<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'category_id', 'name', 'slug', 'description', 'image',
        'has_variation', 'price', 'stock', 'sku', 'weight', 'status',
    ];

    protected $casts = [
        'has_variation' => 'boolean',
        'price' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getEffectivePrice()
    {
        if ($this->has_variation) {
            $activeVariation = $this->variations()->where('status', 'active')->orderBy('price')->first();
            return $activeVariation ? $activeVariation->price : 0;
        }
        return $this->price;
    }

    public function getEffectiveStock()
    {
        if ($this->has_variation) {
            return $this->variations()->where('status', 'active')->sum('stock');
        }
        return $this->stock;
    }

    public function isInStock()
    {
        return $this->getEffectiveStock() > 0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
