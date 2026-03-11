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
        'reminders_sent', 'amount', 'deposit_amount', 'customer_name', 'team_name', 'customer_phone', 'customer_email', 'notes',
        'include_referee', 'referee_price',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'paid_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'amount' => 'decimal:2',
        'include_referee' => 'boolean',
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

    public function isBookingToday()
    {
        if ($this->booking_date->isToday()) return true;
        if (!$this->booking_date->isYesterday()) return false;

        $rule = $this->facility->slotTimeRule ?? null;
        if ($rule) {
            $earliest = substr($rule->earliest_start, 0, 5);
            $latest = substr($rule->latest_start, 0, 5);
            if ($latest < $earliest && substr($this->start_time, 0, 5) < $earliest) {
                return true;
            }
        }
        return $this->end_time < $this->start_time;
    }

    public function getActualStartTime()
    {
        $startTime = \Carbon\Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->start_time);
        $rule = $this->facility->slotTimeRule ?? null;
        if ($rule) {
            $earliest = substr($rule->earliest_start, 0, 5);
            $latest = substr($rule->latest_start, 0, 5);
            if ($latest < $earliest && substr($this->start_time, 0, 5) < $earliest) {
                $startTime->addDay();
            }
        } elseif ($this->end_time < $this->start_time && substr($this->start_time, 0, 5) < '12:00') {
            $startTime->addDay();
        }
        return $startTime;
    }

    public static function isSlotAvailable($facilityId, $bookingDate, $startTime, $endTime, $excludeId = null)
    {
        $facility = Facility::with('slotTimeRule')->find($facilityId);
        $rule = $facility ? $facility->slotTimeRule : null;
        $earliestMin = 0;
        $crossesMidnight = false;

        if ($rule) {
            $ep = explode(':', substr($rule->earliest_start, 0, 5));
            $lp = explode(':', substr($rule->latest_start, 0, 5));
            $earliestMin = (int)$ep[0] * 60 + (int)$ep[1];
            $latestMin = (int)$lp[0] * 60 + (int)$lp[1];
            $crossesMidnight = $latestMin < $earliestMin;
        }

        $query = static::where('facility_id', $facilityId)
            ->where('booking_date', $bookingDate)
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

        $existingBookings = $query->get(['start_time', 'end_time']);

        $newStart = self::timeToMinutes($startTime);
        $newEnd = self::timeToMinutes($endTime);
        if ($crossesMidnight && $newStart < $earliestMin) $newStart += 1440;
        if ($newEnd <= $newStart) $newEnd += 1440;

        foreach ($existingBookings as $existing) {
            $existStart = self::timeToMinutes($existing->start_time);
            $existEnd = self::timeToMinutes($existing->end_time);
            if ($crossesMidnight && $existStart < $earliestMin) $existStart += 1440;
            if ($existEnd <= $existStart) $existEnd += 1440;

            if ($newStart < $existEnd && $newEnd > $existStart) {
                return false;
            }
        }

        return true;
    }

    private static function timeToMinutes($time)
    {
        $parts = explode(':', substr($time, 0, 5));
        return (int)$parts[0] * 60 + (int)$parts[1];
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
