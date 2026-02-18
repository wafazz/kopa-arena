<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloseSale extends Model
{
    protected $fillable = [
        'branch_id', 'close_date', 'closed_by', 'total_amount', 'total_bookings',
        'total_orders', 'total_order_amount', 'notes',
    ];

    protected $casts = [
        'close_date' => 'date',
        'total_amount' => 'decimal:2',
        'total_order_amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public static function isClosed($branchId, $date)
    {
        return static::where('branch_id', $branchId)
            ->where('close_date', $date)
            ->exists();
    }
}
