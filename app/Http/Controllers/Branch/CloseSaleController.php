<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\CloseSale;
use App\Models\Facility;
use App\Models\Order;
use App\Models\PricingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CloseSaleController extends Controller
{
    private function branchId()
    {
        return auth()->user()->branch_id;
    }

    private function branchScope()
    {
        return fn($q) => $q->where('branch_id', $this->branchId());
    }

    public function index(Request $request)
    {
        $branchId = $this->branchId();
        $date = $request->date ?? date('Y-m-d');
        $bookings = collect();
        $orders = collect();
        $closeSale = null;
        $summary = ['total' => 0, 'approved' => 0, 'revenue' => 0, 'deposits' => 0];
        $paymentBreakdown = ['by_type' => [], 'by_status' => ['full_payment' => 0, 'deposit' => 0]];
        $orderSummary = ['total' => 0, 'paid' => 0, 'revenue' => 0, 'unpaid' => 0];
        $orderPaymentBreakdown = ['by_type' => [], 'by_status' => ['paid' => 0, 'unpaid' => 0]];

        $bookings = Booking::with('facility')
            ->whereHas('facility', $this->branchScope())
            ->where('booking_date', $date)
            ->where('status', 'approved')
            ->orderBy('start_time')
            ->get();

        $summary['total'] = $bookings->count();
        $summary['approved'] = $bookings->count();
        $summary['revenue'] = $bookings->sum('amount');
        $summary['deposits'] = $bookings->where('payment_status', 'deposit')->count();

        foreach ($bookings as $b) {
            $type = $b->payment_type;
            if (!isset($paymentBreakdown['by_type'][$type])) {
                $paymentBreakdown['by_type'][$type] = ['count' => 0, 'amount' => 0];
            }
            $paymentBreakdown['by_type'][$type]['count']++;
            $paymentBreakdown['by_type'][$type]['amount'] += $b->amount;
            $paymentBreakdown['by_status'][$b->payment_status]++;
        }

        // Orders
        $orders = Order::where('branch_id', $branchId)
            ->whereDate('created_at', $date)
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->get();

        $orderSummary['total'] = $orders->count();
        $orderSummary['paid'] = $orders->where('payment_status', 'paid')->count();
        $orderSummary['revenue'] = $orders->where('payment_status', 'paid')->sum('total_amount');
        $orderSummary['unpaid'] = $orders->where('payment_status', '!=', 'paid')->count();

        foreach ($orders as $o) {
            $type = $o->payment_type ?? 'cash';
            if (!isset($orderPaymentBreakdown['by_type'][$type])) {
                $orderPaymentBreakdown['by_type'][$type] = ['count' => 0, 'amount' => 0];
            }
            $orderPaymentBreakdown['by_type'][$type]['count']++;
            $orderPaymentBreakdown['by_type'][$type]['amount'] += $o->total_amount;
            $status = $o->payment_status === 'paid' ? 'paid' : 'unpaid';
            $orderPaymentBreakdown['by_status'][$status]++;
        }

        $closeSale = CloseSale::where('branch_id', $branchId)->where('close_date', $date)->first();

        return view('branch.close-sales.index', compact(
            'branchId', 'date', 'bookings', 'closeSale', 'summary', 'paymentBreakdown',
            'orders', 'orderSummary', 'orderPaymentBreakdown'
        ));
    }

    public function close(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $branchId = $this->branchId();

        if (CloseSale::isClosed($branchId, $request->date)) {
            return back()->with('error', 'This day is already closed.');
        }

        $bookings = Booking::whereHas('facility', $this->branchScope())
            ->where('booking_date', $request->date)
            ->where('status', 'approved')
            ->get();

        $orders = Order::where('branch_id', $branchId)
            ->whereDate('created_at', $request->date)
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->get();

        $cs = CloseSale::create([
            'branch_id' => $branchId,
            'close_date' => $request->date,
            'closed_by' => Auth::id(),
            'total_amount' => $bookings->sum('amount'),
            'total_bookings' => $bookings->count(),
            'total_orders' => $orders->count(),
            'total_order_amount' => $orders->sum('total_amount'),
            'notes' => $request->notes,
        ]);

        ActivityLog::log('close', 'CloseSale', $cs->id, 'Closed ' . $request->date);
        return back()->with('success', 'Day closed successfully.');
    }

    public function reopen(CloseSale $closeSale)
    {
        if ($closeSale->branch_id !== $this->branchId()) {
            abort(403);
        }
        ActivityLog::log('reopen', 'CloseSale', $closeSale->id, 'Reopened ' . $closeSale->close_date);
        $closeSale->delete();
        return back()->with('success', 'Day reopened successfully.');
    }

    public function markPaid(Booking $booking)
    {
        if ($booking->facility->branch_id !== $this->branchId()) {
            abort(403);
        }

        if ($booking->status !== 'approved' || $booking->payment_status !== 'deposit') {
            return back()->with('error', 'Only approved deposit bookings can be marked as paid.');
        }

        $booking->update([
            'payment_status' => 'full_payment',
            'paid_at' => now(),
        ]);

        ActivityLog::log('markPaid', 'Booking', $booking->id, $booking->customer_name);
        return back()->with('success', 'Booking marked as fully paid.');
    }

    public function markOrderPaid(Order $order)
    {
        if ($order->branch_id !== $this->branchId()) {
            abort(403);
        }

        if ($order->status === 'cancelled') {
            return back()->with('error', 'Cannot mark a cancelled order as paid.');
        }

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'This order is already paid.');
        }

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        ActivityLog::log('markOrderPaid', 'Order', $order->id, 'Order #' . $order->id);
        return back()->with('success', 'Order marked as paid.');
    }

    public function walkinCreate()
    {
        $facilities = Facility::with('slotTimeRule', 'pricings')
            ->where('branch_id', $this->branchId())
            ->where('status', 'active')
            ->get();
        $pricingRules = PricingRule::with('branches')->whereHas('branches', function ($q) {
            $q->where('branches.id', $this->branchId());
        })->get();

        return view('branch.close-sales.walkin', compact('facilities', 'pricingRules'));
    }

    public function walkinStore(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'payment_type' => 'required|in:cash,online,bank_transfer',
        ]);

        $facility = Facility::with('slotTimeRule', 'pricings', 'branch')->findOrFail($request->facility_id);
        if ($facility->branch_id !== $this->branchId()) {
            abort(403);
        }

        if (CloseSale::isClosed($facility->branch_id, $request->booking_date)) {
            return back()->withInput()->with('error', 'Cannot create booking. Sales for this date have been closed.');
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

        if (!Booking::isSlotAvailable($request->facility_id, $request->booking_date, $request->start_time, $endTime->format('H:i'))) {
            return back()->withInput()->with('error', 'This slot overlaps with an existing booking.');
        }

        try {
            $walkin = DB::transaction(function () use ($request, $facility, $endTime, $amount) {
                return Booking::create([
                    'facility_id' => $facility->id,
                    'user_id' => Auth::id(),
                    'booking_date' => $request->booking_date,
                    'start_time' => $request->start_time,
                    'end_time' => $endTime->format('H:i'),
                    'status' => 'approved',
                    'booking_type' => 'normal',
                    'payment_type' => $request->payment_type,
                    'payment_status' => 'full_payment',
                    'paid_at' => now(),
                    'amount' => $amount,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'notes' => $request->notes,
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withInput()->with('error', 'Failed to create walk-in booking.');
        }

        ActivityLog::log('walkinStore', 'Booking', $walkin->id, 'Walk-in for ' . $request->customer_name);
        return redirect()->route('branch.close-sales.index', [
            'date' => $request->booking_date,
        ])->with('success', 'Walk-in booking created successfully.');
    }
}
