<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilitySlot extends Model
{
    use SoftDeletes;

    protected $fillable = ['facility_id', 'status', 'effective_from', 'effective_to', 'notes'];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
