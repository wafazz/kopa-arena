<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingRule extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'day_of_week', 'normal_price', 'peak_price', 'peak_start', 'peak_end'];

    protected $casts = [
        'normal_price' => 'decimal:2',
        'peak_price' => 'decimal:2',
    ];

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_pricing_rule');
    }
}
