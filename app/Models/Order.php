<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'branch_id', 'customer_name', 'customer_email', 'customer_phone',
        'delivery_method', 'shipping_address', 'shipping_city', 'shipping_state', 'shipping_postcode',
        'notes', 'subtotal', 'shipping_fee', 'total_amount', 'payment_type', 'payment_status',
        'paid_at', 'transaction_id', 'status', 'tracking_number',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
