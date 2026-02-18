<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('facility.branch', 'checkedInByUser')
            ->whereNotNull('checked_in_at')
            ->whereNull('match_parent_id');

        if ($request->filled('branch_id')) {
            $query->whereHas('facility', fn($q) => $q->where('branch_id', $request->branch_id));
        }
        if ($request->filled('date')) {
            $query->where('booking_date', $request->date);
        }

        $checkins = $query->orderByDesc('checked_in_at')->get();
        $branches = Branch::where('status', 'active')->get();

        return view('checkins.index', compact('checkins', 'branches'));
    }

    public function scan()
    {
        return view('checkins.scan');
    }

    public function verify($checkin_token)
    {
        $booking = Booking::with('facility.branch')->where('checkin_token', $checkin_token)->first();

        if (!$booking) {
            return view('checkins.verify', ['error' => 'Invalid check-in token. Booking not found.', 'booking' => null]);
        }

        $error = null;
        if ($booking->status !== 'approved') {
            $error = 'This booking is not approved. Current status: ' . ucfirst($booking->status);
        } elseif ($booking->isCheckedIn()) {
            $error = 'Already checked in at ' . $booking->checked_in_at->format('d M Y, g:i A');
        } elseif (!$booking->booking_date->isToday()) {
            $error = 'This booking is for ' . $booking->booking_date->format('d M Y') . ', not today.';
        } else {
            $startTime = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
            $minutesUntilStart = now()->diffInMinutes($startTime, false);
            if ($minutesUntilStart > 30) {
                $error = 'Check-in opens 30 minutes before booking time (' . $startTime->format('g:i A') . '). Please try again later.';
            }
        }

        return view('checkins.verify', compact('booking', 'error'));
    }

    public function process(Request $request)
    {
        $request->validate(['checkin_token' => 'required|string']);

        $booking = Booking::where('checkin_token', $request->checkin_token)->first();

        if (!$booking) {
            return redirect()->route('checkins.scan')->with('error', 'Invalid check-in token.');
        }
        if ($booking->status !== 'approved') {
            return redirect()->route('checkins.scan')->with('error', 'Booking is not approved.');
        }
        if ($booking->isCheckedIn()) {
            return redirect()->route('checkins.scan')->with('error', 'Already checked in.');
        }
        if (!$booking->booking_date->isToday()) {
            return redirect()->route('checkins.scan')->with('error', 'Booking is not for today.');
        }

        $startTime = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
        $minutesUntilStart = now()->diffInMinutes($startTime, false);
        if ($minutesUntilStart > 30) {
            return redirect()->route('checkins.scan')->with('error', 'Check-in opens 30 minutes before booking time (' . $startTime->format('g:i A') . ').');
        }

        $booking->update([
            'checked_in_at' => now(),
            'checked_in_by' => auth()->id(),
        ]);

        // Also check in match opponent if exists
        if ($booking->booking_type === 'match') {
            $opponent = $booking->match_parent_id
                ? Booking::find($booking->match_parent_id)
                : Booking::where('match_parent_id', $booking->id)->first();

            if ($opponent && !$opponent->isCheckedIn() && $opponent->status === 'approved') {
                $opponent->update([
                    'checked_in_at' => now(),
                    'checked_in_by' => auth()->id(),
                ]);
            }
        }

        ActivityLog::log('process', 'Booking', $booking->id, 'Check-in: ' . $booking->customer_name);
        return redirect()->route('checkins.scan')->with('success', 'Check-in successful for ' . $booking->customer_name . '!');
    }
}
