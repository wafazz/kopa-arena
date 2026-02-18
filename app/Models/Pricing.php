<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pricing extends Model
{
    use SoftDeletes;

    protected $fillable = ['facility_id', 'normal_price', 'peak_price', 'peak_start', 'peak_end', 'day_of_week'];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }
}
