<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\CloseSale;
use App\Models\Facility;
use App\Models\PricingRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    private function branchId()
    {
        return auth()->user()->branch_id;
    }

    private function branchScope()
    {
        return fn($q) => $q->where('branch_id', $this->branchId());
    }

    public function index()
    {
        $bookings = Booking::with('facility', 'matchOpponent')
            ->whereHas('facility', $this->branchScope())
            ->whereNull('match_parent_id')
            ->latest('booking_date')
            ->latest('start_time')
            ->get();

        $totalBookings = $bookings->count();
        $pendingCount = $bookings->where('status', 'pending')->count();
        $approvedCount = $bookings->where('status', 'approved')->count();
        $totalRevenue = $bookings->whereIn('status', ['approved', 'pending'])->sum('amount')
            + $bookings->whereIn('status', ['approved', 'pending'])
                ->sum(fn($b) => $b->matchOpponent && in_array($b->matchOpponent->status, ['approved', 'pending']) ? $b->matchOpponent->amount : 0);

        return view('branch.bookings.index', compact('bookings', 'totalBookings', 'pendingCount', 'approvedCount', 'totalRevenue'));
    }

    public function create()
    {
        $facilities = Facility::with('slotTimeRule', 'pricings')
            ->where('branch_id', $this->branchId())
            ->where('status', 'active')
            ->get();
        $pricingRules = PricingRule::with('branches')->whereHas('branches', function ($q) {
            $q->where('branches.id', $this->branchId());
        })->get();
        return view('branch.bookings.create', compact('facilities', 'pricingRules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'booking_type' => 'required|in:normal,match',
            'match_parent_id' => 'nullable|exists:bookings,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'payment_type' => 'required|in:cash,online,bank_transfer',
            'payment_status' => 'required|in:full_payment,deposit',
            'notes' => 'nullable|string',
        ]);

        $facility = Facility::with('slotTimeRule', 'pricings', 'branch')->findOrFail($request->facility_id);
        if ($facility->branch_id !== $this->branchId()) {
            abort(403);
        }

        if (CloseSale::isClosed($facility->branch_id, $request->booking_date)) {
            return back()->withInput()->with('error', 'Cannot modify. Sales for this date have been closed.');
        }

        $rule = $facility->slotTimeRule;
        $duration = $rule ? $rule->slot_duration : 90;
        $interval = $rule ? $rule->slot_interval : 30;

        $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);

        if ($startTime->minute % $interval !== 0) {
            return back()->withInput()->withErrors(['start_time' => 'Start time must be in ' . $interval . '-minute intervals.']);
        }
        $endTime = $startTime->copy()->addMinutes($duration);

        $bookingDate = \Carbon\Carbon::parse($request->booking_date);
        $dayOfWeek = $bookingDate->dayOfWeek;
        $amount = 0;

        $pricingRule = PricingRule::whereHas('branches', function ($q) use ($facility) {
            $q->where('branches.id', $facility->branch_id);
        })->where(function ($q) use ($dayOfWeek) {
            $q->where('day_of_week', $dayOfWeek)->orWhereNull('day_of_week');
        })->orderByRaw('day_of_week IS NULL ASC')->first();

        if ($pricingRule) {
            $amount = $pricingRule->normal_price;
            if ($pricingRule->peak_start && $pricingRule->peak_end && $pricingRule->peak_price) {
                $peakStart = \Carbon\Carbon::createFromFormat('H:i', substr($pricingRule->peak_start, 0, 5));
                $peakEnd = \Carbon\Carbon::createFromFormat('H:i', substr($pricingRule->peak_end, 0, 5));
                if ($startTime->between($peakStart, $peakEnd) || $startTime->eq($peakStart)) {
                    $amount = $pricingRule->peak_price;
                }
            }
        } else {
            $pricing = $facility->pricings()->first();
            if ($pricing) {
                $amount = $pricing->normal_price;
                if ($pricing->peak_start && $pricing->peak_end) {
                    $peakStart = \Carbon\Carbon::createFromFormat('H:i', substr($pricing->peak_start, 0, 5));
                    $peakEnd = \Carbon\Carbon::createFromFormat('H:i', substr($pricing->peak_end, 0, 5));
                    if ($startTime->between($peakStart, $peakEnd) || $startTime->eq($peakStart)) {
                        $amount = $pricing->peak_price;
                    }
                }
            }
        }

        if ($request->booking_type === 'match') {
            $amount = $amount / 2;
        }

        // Team B joining an existing match
        if ($request->match_parent_id) {
            $parent = Booking::findOrFail($request->match_parent_id);
            if ($parent->booking_type !== 'match' || $parent->match_parent_id !== null) {
                return back()->withInput()->with('error', 'Invalid match booking to join.');
            }
            if ($parent->matchOpponent) {
                return back()->withInput()->with('error', 'This match already has an opponent.');
            }

            try {
                DB::transaction(function () use ($request, $parent, $amount) {
                    Booking::create([
                        'facility_id' => $parent->facility_id,
                        'user_id' => Auth::id(),
                        'booking_date' => $parent->booking_date,
                        'start_time' => $parent->start_time,
                        'end_time' => $parent->end_time,
                        'status' => 'pending',
                        'booking_type' => 'match',
                        'match_parent_id' => $parent->id,
                        'payment_type' => $request->payment_type,
                        'payment_status' => $request->payment_status,
                        'amount' => $amount,
                        'customer_name' => $request->customer_name,
                        'customer_phone' => $request->customer_phone,
                        'customer_email' => $request->customer_email,
                        'notes' => $request->notes,
                    ]);
                });
            } catch (\Illuminate\Database\QueryException $e) {
                return back()->withInput()->with('error', 'Failed to join match.');
            }

            ActivityLog::log('store', 'Booking', null, 'Joined match as opponent for ' . $request->customer_name);
            return redirect()->route('branch.bookings.index')->with('success', 'Successfully joined match as opponent.');
        }

        if (!Booking::isSlotAvailable($request->facility_id, $request->booking_date, $request->start_time, $endTime->format('H:i'))) {
            return back()->withInput()->with('error', 'This slot overlaps with an existing booking.');
        }

        try {
            DB::transaction(function () use ($request, $endTime, $amount) {
                Booking::create([
                    'facility_id' => $request->facility_id,
                    'user_id' => Auth::id(),
                    'booking_date' => $request->booking_date,
                    'start_time' => $request->start_time,
                    'end_time' => $endTime->format('H:i'),
                    'status' => 'pending',
                    'booking_type' => $request->booking_type,
                    'payment_type' => $request->payment_type,
                    'payment_status' => $request->payment_status,
                    'amount' => $amount,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'notes' => $request->notes,
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('error', 'This slot is already booked (duplicate detected).');
            }
            throw $e;
        }

        ActivityLog::log('store', 'Booking', null, 'Booking for ' . $request->customer_name);
        return redirect()->route('branch.bookings.index')->with('success', 'Booking created successfully.');
    }

    public function edit(Booking $booking)
    {
        if ($booking->facility->branch_id !== $this->branchId()) {
            abort(403);
        }
        $booking->load('matchParent', 'matchOpponent');
        $facilities = Facility::where('branch_id', $this->branchId())
            ->where('status', 'active')
            ->get();
        return view('branch.bookings.edit', compact('booking', 'facilities'));
    }

    public function update(Request $request, Booking $booking)
    {
        if ($booking->facility->branch_id !== $this->branchId()) {
            abort(403);
        }

        if (CloseSale::isClosed($booking->facility->branch_id, $booking->booking_date)) {
            return back()->with('error', 'Cannot modify. Sales for this date have been closed.');
        }

        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'payment_type' => 'required|in:cash,online,bank_transfer',
            'payment_status' => 'required|in:full_payment,deposit',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $facility = Facility::with('slotTimeRule')->findOrFail($request->facility_id);
        if ($facility->branch_id !== $this->branchId()) {
            abort(403);
        }

        if (CloseSale::isClosed($facility->branch_id, $request->booking_date)) {
            return back()->withInput()->with('error', 'Cannot modify. Sales for the new date have been closed.');
        }

        $rule = $facility->slotTimeRule;
        $duration = $rule ? $rule->slot_duration : 90;
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = $startTime->copy()->addMinutes($duration);

        if (!Booking::isSlotAvailable($request->facility_id, $request->booking_date, $request->start_time, $endTime->format('H:i'), $booking->id)) {
            return back()->withInput()->with('error', 'This slot overlaps with an existing booking.');
        }

        $booking->update([
            'facility_id' => $request->facility_id,
            'booking_date' => $request->booking_date,
            'start_time' => $request->start_time,
            'end_time' => $endTime->format('H:i'),
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'payment_type' => $request->payment_type,
            'payment_status' => $request->payment_status,
            'amount' => $request->amount,
            'notes' => $request->notes,
        ]);

        ActivityLog::log('update', 'Booking', $booking->id, $booking->customer_name);
        return redirect()->route('branch.bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        if ($booking->facility->branch_id !== $this->branchId()) {
            abort(403);
        }
        if (CloseSale::isClosed($booking->facility->branch_id, $booking->booking_date)) {
            return back()->with('error', 'Cannot modify. Sales for this date have been closed.');
        }
        ActivityLog::log('destroy', 'Booking', $booking->id, $booking->customer_name);
        $booking->delete();
        return redirect()->route('branch.bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function approve(Booking $booking)
    {
        if ($booking->facility->branch_id !== $this->branchId()) {
            abort(403);
        }
        if (CloseSale::isClosed($booking->facility->branch_id, $booking->booking_date)) {
            return back()->with('error', 'Cannot modify. Sales for this date have been closed.');
        }
        $booking->update(['status' => 'approved']);
        ActivityLog::log('approve', 'Booking', $booking->id, $booking->customer_name);
        return back()->with('success', 'Booking approved.');
    }

    public function reject(Booking $booking)
    {
        if ($booking->facility->branch_id !== $this->branchId()) {
            abort(403);
        }
        if (CloseSale::isClosed($booking->facility->branch_id, $booking->booking_date)) {
            return back()->with('error', 'Cannot modify. Sales for this date have been closed.');
        }
        $booking->update(['status' => 'rejected']);
        ActivityLog::log('reject', 'Booking', $booking->id, $booking->customer_name);
        return back()->with('success', 'Booking rejected.');
    }

    public function cancel(Booking $booking)
    {
        if ($booking->facility->branch_id !== $this->branchId()) {
            abort(403);
        }
        if (CloseSale::isClosed($booking->facility->branch_id, $booking->booking_date)) {
            return back()->with('error', 'Cannot modify. Sales for this date have been closed.');
        }
        $booking->update(['status' => 'cancelled']);
        ActivityLog::log('cancel', 'Booking', $booking->id, $booking->customer_name);
        return back()->with('success', 'Booking cancelled.');
    }

    public function bookedSlots(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'booking_date' => 'required|date',
        ]);

        $facility = Facility::findOrFail($request->facility_id);
        if ($facility->branch_id !== $this->branchId()) {
            abort(403);
        }

        $bookings = Booking::where('facility_id', $request->facility_id)
            ->where('booking_date', $request->booking_date)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->select('id', 'start_time', 'end_time', 'booking_type', 'match_parent_id', 'customer_name')
            ->with('matchOpponent:id,match_parent_id,customer_name')
            ->get()
            ->map(function ($b) {
                $hasOpponent = $b->matchOpponent !== null;
                $isMatchOpen = $b->booking_type === 'match' && $b->match_parent_id === null && !$hasOpponent;
                return [
                    'start_time' => $b->start_time,
                    'end_time' => $b->end_time,
                    'type' => $b->booking_type,
                    'status' => $isMatchOpen ? 'match_open' : 'booked',
                    'match_id' => $b->match_parent_id === null ? $b->id : $b->match_parent_id,
                    'team_a_name' => $b->match_parent_id === null ? $b->customer_name : null,
                    'is_child' => $b->match_parent_id !== null,
                ];
            });

        return response()->json($bookings);
    }

    public function calendarEvents(Request $request)
    {
        $query = Booking::with('facility')
            ->whereHas('facility', $this->branchScope())
            ->whereNull('match_parent_id')
            ->whereBetween('booking_date', [$request->start, $request->end]);

        if ($request->facility_id) {
            $query->where('facility_id', $request->facility_id);
        }

        $colors = ['pending' => '#f0ad4e', 'approved' => '#2dd489', 'rejected' => '#e74a5a', 'cancelled' => '#6b7190'];

        $events = $query->get()->map(function ($b) use ($colors) {
            return [
                'id' => $b->id,
                'title' => $b->customer_name . ' (' . ($b->facility->name ?? '') . ')',
                'start' => $b->booking_date->format('Y-m-d') . 'T' . Carbon::parse($b->start_time)->format('H:i:s'),
                'end' => $b->booking_date->format('Y-m-d') . 'T' . Carbon::parse($b->end_time)->format('H:i:s'),
                'color' => $colors[$b->status] ?? '#6c757d',
                'extendedProps' => [
                    'booking_id' => $b->id,
                    'customer_name' => $b->customer_name,
                    'customer_phone' => $b->customer_phone ?? '',
                    'facility' => $b->facility->name ?? '',
                    'branch' => $b->facility->branch->name ?? '',
                    'status' => $b->status,
                    'booking_type' => $b->booking_type,
                    'amount' => number_format($b->amount, 2),
                    'checked_in' => $b->isCheckedIn(),
                ],
            ];
        });

        return response()->json($events);
    }
}
