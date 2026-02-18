<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'facility_id', 'user_id', 'booking_date', 'start_time', 'end_time',
        'status', 'booking_type', 'match_parent_id', 'payment_type', 'payment_status',
        'paid_at', 'transaction_id', 'checkin_token', 'checked_in_at', 'checked_in_by',
        'reminders_sent', 'amount', 'customer_name', 'customer_phone', 'customer_email', 'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'paid_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'amount' => 'decimal:2',
        'reminders_sent' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($booking) {
            if (!$booking->checkin_token) {
                $booking->checkin_token = Str::random(40);
            }
        });
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function matchParent()
    {
        return $this->belongsTo(Booking::class, 'match_parent_id');
    }

    public function matchOpponent()
    {
        return $this->hasOne(Booking::class, 'match_parent_id');
    }

    public function checkedInByUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'checked_in_by');
    }

    public function isCheckedIn()
    {
        return $this->checked_in_at !== null;
    }

    public static function isSlotAvailable($facilityId, $bookingDate, $startTime, $endTime, $excludeId = null)
    {
        $query = static::where('facility_id', $facilityId)
            ->where('booking_date', $bookingDate)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->whereNotIn('status', ['rejected', 'cancelled']);

        if ($excludeId) {
            $excludeIds = [$excludeId];
            $booking = static::find($excludeId);
            if ($booking && $booking->booking_type === 'match') {
                if ($booking->match_parent_id) {
                    $excludeIds[] = $booking->match_parent_id;
                }
                $opponent = static::where('match_parent_id', $booking->id)->value('id');
                if ($opponent) {
                    $excludeIds[] = $opponent;
                }
            }
            $query->whereNotIn('id', $excludeIds);
        }

        return !$query->exists();
    }

    public static function findOpenMatch($facilityId, $bookingDate, $startTime)
    {
        return static::where('facility_id', $facilityId)
            ->where('booking_date', $bookingDate)
            ->where('start_time', $startTime)
            ->where('booking_type', 'match')
            ->whereNull('match_parent_id')
            ->whereDoesntHave('matchOpponent')
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->first();
    }
}
