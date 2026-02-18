<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\CloseSale;
use App\Models\Facility;
use App\Models\PricingRule;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublicController extends Controller
{
    public function index()
    {
        $branches = Branch::where('status', 'active')->get();
        $facilities = Facility::with('branch', 'slotTimeRule', 'pricings')
            ->where('status', 'active')
            ->whereHas('branch', fn($q) => $q->where('status', 'active'))
            ->get();
        $pricingRules = PricingRule::with('branches')->get();
        $stats = [
            'branches' => $branches->count(),
            'facilities' => $facilities->count(),
            'bookings' => Booking::where('status', 'approved')->count(),
        ];

        $config = $this->getSenangPayConfig();
        $senangpayEnabled = !empty($config['merchant_id']) && !empty($config['secret_key']);

        return view('landing', compact('branches', 'facilities', 'pricingRules', 'stats', 'senangpayEnabled'));
    }

    public function bookedSlots(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'booking_date' => 'required|date',
        ]);

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

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|exists:facilities,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'booking_type' => 'required|in:normal,match',
            'match_parent_id' => 'nullable|exists:bookings,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,online',
        ]);

        $facility = Facility::with('slotTimeRule', 'pricings', 'branch')->findOrFail($request->facility_id);

        if (CloseSale::isClosed($facility->branch_id, $request->booking_date)) {
            return back()->withInput()->with('error', 'Booking is not available for this date.');
        }

        $rule = $facility->slotTimeRule;
        $duration = $rule ? $rule->slot_duration : 90;
        $interval = $rule ? $rule->slot_interval : 30;

        $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);

        if ($startTime->minute % $interval !== 0) {
            return back()->withInput()->withErrors(['start_time' => 'Invalid time slot.']);
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

        $paymentMethod = $request->input('payment_method', 'cash');
        $config = $this->getSenangPayConfig();
        $isOnline = $paymentMethod === 'online' && !empty($config['merchant_id']) && !empty($config['secret_key']);

        // Team B joining match
        if ($request->match_parent_id) {
            $parent = Booking::findOrFail($request->match_parent_id);
            if ($parent->booking_type !== 'match' || $parent->match_parent_id !== null) {
                return back()->withInput()->with('error', 'Invalid match booking.');
            }
            if ($parent->matchOpponent) {
                return back()->withInput()->with('error', 'This match already has an opponent.');
            }

            $booking = null;
            try {
                DB::transaction(function () use ($request, $parent, $amount, &$booking) {
                    $booking = Booking::create([
                        'facility_id' => $parent->facility_id,
                        'user_id' => null,
                        'booking_date' => $parent->booking_date,
                        'start_time' => $parent->start_time,
                        'end_time' => $parent->end_time,
                        'status' => 'pending',
                        'booking_type' => 'match',
                        'match_parent_id' => $parent->id,
                        'payment_type' => 'cash',
                        'payment_status' => 'deposit',
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

            if ($isOnline && $booking) {
                $this->sendWhatsAppNotification($booking);
                return $this->redirectToSenangPay($booking, $config);
            }

            if ($booking) {
                $this->sendWhatsAppNotification($booking);
            }

            return back()->with('success', 'Match booking submitted! Our team will confirm your booking shortly.');
        }

        if (!Booking::isSlotAvailable($request->facility_id, $request->booking_date, $request->start_time, $endTime->format('H:i'))) {
            return back()->withInput()->with('error', 'This time slot is no longer available.');
        }

        $booking = null;
        try {
            DB::transaction(function () use ($request, $endTime, $amount, &$booking) {
                $booking = Booking::create([
                    'facility_id' => $request->facility_id,
                    'user_id' => null,
                    'booking_date' => $request->booking_date,
                    'start_time' => $request->start_time,
                    'end_time' => $endTime->format('H:i'),
                    'status' => 'pending',
                    'booking_type' => $request->booking_type,
                    'payment_type' => 'cash',
                    'payment_status' => 'deposit',
                    'amount' => $amount,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'customer_email' => $request->customer_email,
                    'notes' => $request->notes,
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withInput()->with('error', 'This slot is no longer available.');
        }

        if ($isOnline && $booking) {
            $this->sendWhatsAppNotification($booking);
            return $this->redirectToSenangPay($booking, $config);
        }

        if ($booking) {
            $this->sendWhatsAppNotification($booking);
        }

        return back()->with('success', 'Booking submitted successfully! Our team will review and confirm your booking shortly.');
    }

    public function paymentReturn(Request $request)
    {
        $statusId = $request->query('status_id');
        $orderId = $request->query('order_id');
        $transactionId = $request->query('transaction_id');
        $msg = $request->query('msg');
        $hash = $request->query('hash');

        $config = $this->getSenangPayConfig();
        $secretKey = $config['secret_key'];

        $expectedHash = hash_hmac('SHA256', $secretKey . $statusId . $orderId . $transactionId . $msg, $secretKey);

        if ($hash !== $expectedHash) {
            return redirect()->route('landing')->with('error', 'Invalid payment response.');
        }

        $bookingId = str_replace('KOPA-', '', $orderId);
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return redirect()->route('landing')->with('error', 'Booking not found.');
        }

        if ($statusId == 1) {
            $booking->update([
                'payment_status' => 'full_payment',
                'payment_type' => 'online',
                'paid_at' => now(),
                'transaction_id' => $transactionId,
            ]);
            return redirect()->route('landing')->with('success', 'Payment successful! Your booking has been confirmed.');
        }

        if ($statusId == 2) {
            return redirect()->route('landing')->with('info', 'Payment is being processed. We\'ll update your booking once confirmed.');
        }

        return redirect()->route('landing')->with('info', 'Payment was not completed. Your booking is saved — you can pay at the venue.');
    }

    public function paymentCallback(Request $request)
    {
        $statusId = $request->input('status_id');
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');
        $msg = $request->input('msg');
        $hash = $request->input('hash');

        $config = $this->getSenangPayConfig();
        $secretKey = $config['secret_key'];

        $expectedHash = hash_hmac('SHA256', $secretKey . $statusId . $orderId . $transactionId . $msg, $secretKey);

        if ($hash !== $expectedHash) {
            return response('FAIL', 400);
        }

        $bookingId = str_replace('KOPA-', '', $orderId);
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response('NOT FOUND', 404);
        }

        if ($statusId == 1) {
            $booking->update([
                'payment_status' => 'full_payment',
                'payment_type' => 'online',
                'paid_at' => now(),
                'transaction_id' => $transactionId,
            ]);
        }

        return response('OK');
    }

    public function bookingDetails(Booking $booking)
    {
        $booking->load('facility.branch');
        return view('booking-details', compact('booking'));
    }

    public function selfCheckin(Booking $booking)
    {
        if ($booking->status !== 'approved') {
            return back()->with('error', 'This booking is not approved yet.');
        }
        if ($booking->isCheckedIn()) {
            return back()->with('error', 'Already checked in.');
        }
        if (!$booking->booking_date->isToday()) {
            return back()->with('error', 'Self check-in is only available on the booking date.');
        }

        $startTime = \Carbon\Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
        $minutesUntilStart = now()->diffInMinutes($startTime, false);

        if ($minutesUntilStart > 30) {
            return back()->with('error', 'Self check-in opens 30 minutes before your booking time.');
        }
        if ($minutesUntilStart < -30) {
            return back()->with('error', 'Self check-in window has expired. Please contact staff.');
        }

        $booking->update([
            'checked_in_at' => now(),
            'checked_in_by' => null,
        ]);

        // Also check in match opponent if exists
        if ($booking->booking_type === 'match') {
            $opponent = $booking->match_parent_id
                ? Booking::find($booking->match_parent_id)
                : Booking::where('match_parent_id', $booking->id)->first();

            if ($opponent && !$opponent->isCheckedIn() && $opponent->status === 'approved') {
                $opponent->update([
                    'checked_in_at' => now(),
                    'checked_in_by' => null,
                ]);
            }
        }

        return back()->with('success', 'Check-in successful! Enjoy your game.');
    }

    public function checkinVerify($checkin_token)
    {
        $booking = Booking::where('checkin_token', $checkin_token)->first();
        if (!$booking) {
            abort(404, 'Invalid check-in token.');
        }

        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('info', 'Please login to process check-in.');
        }

        if ($user->isBranchStaff()) {
            return redirect()->route('branch.checkins.verify', $checkin_token);
        }

        if ($user->hasRole(['superadmin', 'hq_staff'])) {
            return redirect()->route('checkins.verify', $checkin_token);
        }

        abort(403, 'Unauthorized.');
    }

    private function getSenangPayConfig()
    {
        $mode = Setting::get('senangpay_mode', 'sandbox');
        $prefix = 'senangpay_' . $mode . '_';
        return [
            'mode' => $mode,
            'merchant_id' => Setting::get($prefix . 'merchant_id'),
            'secret_key' => Setting::get($prefix . 'secret_key'),
            'base_url' => $mode === 'production'
                ? 'https://app.senangpay.my/payment/'
                : 'https://sandbox.senangpay.my/payment/',
        ];
    }

    private function sendWhatsAppNotification($booking)
    {
        $token = Setting::get('onsend_api_token');
        if (!$token) return;

        $booking->load('facility.branch');

        $phone = preg_replace('/[^0-9]/', '', $booking->customer_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '6' . $phone;
        }
        if (!str_starts_with($phone, '6')) {
            $phone = '60' . $phone;
        }

        $detailsUrl = route('public.booking.details', ['booking' => $booking->id]);

        $facility = $booking->facility->name ?? '-';
        $branch = $booking->facility->branch->name ?? '-';
        $date = \Carbon\Carbon::parse($booking->booking_date)->format('d M Y (l)');
        $time = \Carbon\Carbon::parse($booking->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($booking->end_time)->format('g:i A');
        $amount = 'RM ' . number_format($booking->amount, 2);
        $type = ucfirst($booking->booking_type);

        $message = "*KOPA ARENA - Booking Confirmation*\n\n"
            . "Hi *{$booking->customer_name}*,\n"
            . "Your booking has been submitted!\n\n"
            . "*Booking Details:*\n"
            . "Reference: #KOPA-{$booking->id}\n"
            . "Branch: {$branch}\n"
            . "Facility: {$facility}\n"
            . "Date: {$date}\n"
            . "Time: {$time}\n"
            . "Type: {$type}\n"
            . "Amount: {$amount}\n\n"
            . "Status: _Pending Approval_\n\n"
            . "View your booking details:\n{$detailsUrl}\n\n"
            . "Thank you for choosing Kopa Arena!";

        try {
            Http::withToken($token)->post('https://onsend.io/api/v1/send', [
                'phone_number' => $phone,
                'type' => 'text',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp notification failed: ' . $e->getMessage());
        }
    }

    private function redirectToSenangPay($booking, $config)
    {
        $orderId = 'KOPA-' . $booking->id;
        $facility = $booking->facility;
        $detail = 'Booking_' . $facility->name . '_' . $booking->booking_date->format('Y-m-d');
        $amount = number_format($booking->amount, 2, '.', '');

        $hash = hash_hmac('SHA256', $config['secret_key'] . $detail . $amount . $orderId, $config['secret_key']);

        $url = $config['base_url'] . $config['merchant_id'];
        $params = http_build_query([
            'detail' => $detail,
            'amount' => $amount,
            'order_id' => $orderId,
            'hash' => $hash,
            'name' => $booking->customer_name,
            'email' => $booking->customer_email ?? '',
            'phone' => $booking->customer_phone,
        ]);

        return redirect($url . '?' . $params);
    }
}
