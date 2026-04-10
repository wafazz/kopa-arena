<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlotTimeRule extends Model
{
    use SoftDeletes;

    protected $fillable = ['facility_id', 'slot_duration', 'slot_interval', 'interval_overrides', 'earliest_start', 'latest_start'];

    protected $casts = [
        'interval_overrides' => 'array',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
