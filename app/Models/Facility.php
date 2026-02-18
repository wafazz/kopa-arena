<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use SoftDeletes;

    protected $fillable = ['branch_id', 'name', 'type', 'status'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function slots()
    {
        return $this->hasMany(FacilitySlot::class);
    }

    public function slotTimeRule()
    {
        return $this->hasOne(SlotTimeRule::class);
    }

    public function pricings()
    {
        return $this->hasMany(Pricing::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
