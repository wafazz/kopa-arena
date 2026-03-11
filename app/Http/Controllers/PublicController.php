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
        $pricingRules = PricingRule::with('facilities')->get();
        $stats = [
            'branches' => $branches->count(),
            'facilities' => $facilities->count(),
            'bookings' => Booking::where('status', 'approved')->count(),
        ];

        $activeGateway = $this->getActiveGateway();
        $onlinePaymentEnabled = $activeGateway['gateway'] !== 'none';
        $depositPercentage = (int) Setting::get('deposit_percentage', 50);

        return view('landing', compact('branches', 'facilities', 'pricingRules', 'stats', 'onlinePaymentEnabled', 'depositPercentage'));
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
            ->select('id', 'start_time', 'end_time', 'booking_type', 'match_parent_id', 'customer_name', 'team_name')
            ->with('matchOpponent:id,match_parent_id,customer_name,team_name')
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
                    'team_a_name' => $b->match_parent_id === null ? ($b->team_name ?? $b->customer_name) : null,
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
            'team_name' => 'nullable|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,online',
            'payment_option' => 'nullable|in:deposit,full',
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

        $pricingRules = PricingRule::whereHas('facilities', function ($q) use ($facility) {
            $q->where('facilities.id', $facility->id);
        })->where(function ($q) use ($dayOfWeek) {
            $q->where('day_of_week', $dayOfWeek)->orWhereNull('day_of_week');
        })->orderByRaw('day_of_week IS NULL ASC')->get();

        $pricing = $facility->pricings()->first();
        if ($pricing) {
            $amount = $pricing->normal_price;
        }

        foreach ($pricingRules as $pricingRule) {
            if ($pricingRule->peak_start && $pricingRule->peak_end && $pricingRule->peak_price) {
                $peakStart = \Carbon\Carbon::createFromFormat('H:i', substr($pricingRule->peak_start, 0, 5));
                $peakEnd = \Carbon\Carbon::createFromFormat('H:i', substr($pricingRule->peak_end, 0, 5));
                $inPeak = $peakEnd->lt($peakStart)
                    ? ($startTime->gte($peakStart) || $startTime->lte($peakEnd))
                    : ($startTime->between($peakStart, $peakEnd) || $startTime->eq($peakStart));
                if ($inPeak) {
                    $amount = $pricingRule->peak_price;
                    break;
                }
            }
        }

        if ($request->booking_type === 'match') {
            $amount = $amount / 2;
        }

        $paymentMethod = $request->input('payment_method', 'cash');
        $activeGateway = $this->getActiveGateway();
        $isOnline = $paymentMethod === 'online' && $activeGateway['gateway'] !== 'none';
        $paymentOption = $request->input('payment_option', 'deposit');
        $depositPercentage = (int) Setting::get('deposit_percentage', 50);
        $depositAmount = round($amount * $depositPercentage / 100, 2);

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
                DB::transaction(function () use ($request, $parent, $amount, $depositAmount, &$booking) {
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
                        'deposit_amount' => $depositAmount,
                        'customer_name' => $request->customer_name,
                        'team_name' => $request->team_name,
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
                return $this->redirectToGateway($booking, $activeGateway, $paymentOption);
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
            DB::transaction(function () use ($request, $endTime, $amount, $depositAmount, &$booking) {
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
                    'deposit_amount' => $depositAmount,
                    'customer_name' => $request->customer_name,
                    'team_name' => $request->team_name,
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
            return $this->redirectToGateway($booking, $activeGateway, $paymentOption);
        }

        if ($booking) {
            $this->sendWhatsAppNotification($booking);
        }

        return back()->with('success', 'Booking submitted successfully! Our team will review and confirm your booking shortly.');
    }

    public function paymentReturn(Request $request)
    {
        if ($request->has('billcode')) {
            return $this->toyyibPayReturn($request);
        }
        return $this->senangPayReturn($request);
    }

    private function senangPayReturn(Request $request)
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

        $isDeposit = str_ends_with($orderId, '-D');
        $bookingId = preg_replace('/^KOPA-(\d+)-(D|F)$/', '$1', $orderId);
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return redirect()->route('landing')->with('error', 'Booking not found.');
        }

        if ($statusId == 1) {
            $booking->update([
                'payment_status' => $isDeposit ? 'deposit' : 'full_payment',
                'payment_type' => 'online',
                'paid_at' => now(),
                'transaction_id' => $transactionId,
            ]);
            $msg = $isDeposit ? 'Deposit payment successful! Your booking will be reviewed shortly.' : 'Payment successful! Your booking has been confirmed.';
            return redirect()->route('landing')->with('success', $msg);
        }

        if ($statusId == 2) {
            return redirect()->route('landing')->with('info', 'Payment is being processed. We\'ll update your booking once confirmed.');
        }

        return redirect()->route('landing')->with('info', 'Payment was not completed. Your booking is saved — you can pay at the venue.');
    }

    private function toyyibPayReturn(Request $request)
    {
        $statusId = $request->query('status_id');
        $billcode = $request->query('billcode');
        $orderId = $request->query('order_id');
        $transactionId = $request->query('transaction_id');
        $msg = $request->query('msg');
        $hash = $request->query('hash');

        $config = $this->getToyyibPayConfig();
        $secretKey = $config['secret_key'];

        $expectedHash = md5($secretKey . $statusId . $orderId . $transactionId . $msg);

        if ($hash !== $expectedHash) {
            return redirect()->route('landing')->with('error', 'Invalid payment response.');
        }

        $isDeposit = str_ends_with($orderId, '-D');
        $bookingId = preg_replace('/^KOPA-(\d+)-(D|F)$/', '$1', $orderId);
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return redirect()->route('landing')->with('error', 'Booking not found.');
        }

        if ($statusId == 1) {
            $booking->update([
                'payment_status' => $isDeposit ? 'deposit' : 'full_payment',
                'payment_type' => 'online',
                'paid_at' => now(),
                'transaction_id' => $billcode,
            ]);
            $successMsg = $isDeposit ? 'Deposit payment successful! Your booking will be reviewed shortly.' : 'Payment successful! Your booking has been confirmed.';
            return redirect()->route('landing')->with('success', $successMsg);
        }

        if ($statusId == 3) {
            return redirect()->route('landing')->with('info', 'Payment is being processed. We\'ll update your booking once confirmed.');
        }

        return redirect()->route('landing')->with('info', 'Payment was not completed. Your booking is saved — you can pay at the venue.');
    }

    public function paymentCallback(Request $request)
    {
        if ($request->has('billcode')) {
            return $this->toyyibPayCallback($request);
        }
        return $this->senangPayCallback($request);
    }

    private function senangPayCallback(Request $request)
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

        $isDeposit = str_ends_with($orderId, '-D');
        $bookingId = preg_replace('/^KOPA-(\d+)-(D|F)$/', '$1', $orderId);
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response('NOT FOUND', 404);
        }

        if ($statusId == 1) {
            $booking->update([
                'payment_status' => $isDeposit ? 'deposit' : 'full_payment',
                'payment_type' => 'online',
                'paid_at' => now(),
                'transaction_id' => $transactionId,
            ]);
        }

        return response('OK');
    }

    private function toyyibPayCallback(Request $request)
    {
        $statusId = $request->input('status_id');
        $billcode = $request->input('billcode');
        $orderId = $request->input('order_id');
        $transactionId = $request->input('transaction_id');
        $msg = $request->input('msg');
        $hash = $request->input('hash');

        $config = $this->getToyyibPayConfig();
        $secretKey = $config['secret_key'];

        $expectedHash = md5($secretKey . $statusId . $orderId . $transactionId . $msg);

        if ($hash !== $expectedHash) {
            return response('FAIL', 400);
        }

        $isDeposit = str_ends_with($orderId, '-D');
        $bookingId = preg_replace('/^KOPA-(\d+)-(D|F)$/', '$1', $orderId);
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return response('NOT FOUND', 404);
        }

        if ($statusId == 1) {
            $booking->update([
                'payment_status' => $isDeposit ? 'deposit' : 'full_payment',
                'payment_type' => 'online',
                'paid_at' => now(),
                'transaction_id' => $billcode,
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
        if (!$booking->isBookingToday()) {
            return back()->with('error', 'Self check-in is only available on the booking date.');
        }

        $startTime = $booking->getActualStartTime();
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
        $teamInfo = $booking->team_name ? "Team: {$booking->team_name}\n" : '';
        $depositInfo = '';
        if ($booking->deposit_amount && $booking->deposit_amount < $booking->amount) {
            $depositInfo = "Deposit: RM " . number_format($booking->deposit_amount, 2) . "\n"
                . "Balance: RM " . number_format($booking->amount - $booking->deposit_amount, 2) . "\n";
        }

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
            . $teamInfo
            . "Amount: {$amount}\n"
            . $depositInfo . "\n"
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

    private function getActiveGateway()
    {
        $gateway = Setting::get('payment_gateway', 'none');

        if ($gateway === 'senangpay') {
            $config = $this->getSenangPayConfig();
            if (!empty($config['merchant_id']) && !empty($config['secret_key'])) {
                return ['gateway' => 'senangpay', 'config' => $config];
            }
        }

        if ($gateway === 'toyyibpay') {
            $config = $this->getToyyibPayConfig();
            if (!empty($config['secret_key']) && !empty($config['category_code'])) {
                return ['gateway' => 'toyyibpay', 'config' => $config];
            }
        }

        return ['gateway' => 'none', 'config' => []];
    }

    private function getToyyibPayConfig()
    {
        $mode = Setting::get('toyyibpay_mode', 'sandbox');
        $prefix = 'toyyibpay_' . $mode . '_';
        return [
            'mode' => $mode,
            'secret_key' => Setting::get($prefix . 'secret_key'),
            'category_code' => Setting::get($prefix . 'category_code'),
            'base_url' => $mode === 'production'
                ? 'https://toyyibpay.com'
                : 'https://dev.toyyibpay.com',
        ];
    }

    private function redirectToGateway($booking, $activeGateway, $paymentOption = 'deposit')
    {
        $chargeAmount = $paymentOption === 'full' ? $booking->amount : $booking->deposit_amount;
        $orderId = 'KOPA-' . $booking->id . ($paymentOption === 'deposit' ? '-D' : '-F');

        if ($activeGateway['gateway'] === 'toyyibpay') {
            return $this->redirectToToyyibPay($booking, $activeGateway['config'], $chargeAmount, $orderId);
        }
        return $this->redirectToSenangPay($booking, $activeGateway['config'], $chargeAmount, $orderId);
    }

    private function redirectToSenangPay($booking, $config, $chargeAmount, $orderId)
    {
        $facility = $booking->facility;
        $detail = 'Booking_' . $facility->name . '_' . $booking->booking_date->format('Y-m-d');
        $amount = number_format($chargeAmount, 2, '.', '');

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

    private function redirectToToyyibPay($booking, $config, $chargeAmount, $orderId)
    {
        $facility = $booking->facility;
        $detail = 'Booking ' . $facility->name . ' ' . $booking->booking_date->format('Y-m-d');
        $amount = intval(round($chargeAmount * 100)); // cents

        $phone = preg_replace('/[^0-9]/', '', $booking->customer_phone);

        try {
            $response = Http::asForm()->post($config['base_url'] . '/index.php/api/createBill', [
                'userSecretKey' => $config['secret_key'],
                'categoryCode' => $config['category_code'],
                'billName' => $detail,
                'billDescription' => $detail,
                'billPriceSetting' => 1,
                'billPayorInfo' => 1,
                'billAmount' => $amount,
                'billReturnUrl' => url('/payment/return'),
                'billCallbackUrl' => url('/payment/callback'),
                'billExternalReferenceNo' => $orderId,
                'billTo' => $booking->customer_name,
                'billEmail' => $booking->customer_email ?? '',
                'billPhone' => $phone,
                'billSplitPayment' => 0,
                'billSplitPaymentArgs' => '',
                'billPaymentChannel' => 0,
                'billContentEmail' => 'Thank you for your booking at ' . $facility->name . '!',
                'billChargeToCustomer' => 1,
                'billExpiryDays' => 3,
            ]);

            $result = $response->json();

            if (isset($result[0]['BillCode'])) {
                $billCode = $result[0]['BillCode'];
                return redirect($config['base_url'] . '/' . $billCode);
            }

            Log::error('ToyyibPay createBill failed', ['response' => $result]);
            return redirect()->route('landing')->with('error', 'Payment gateway error. Your booking is saved — you can pay at the venue.');
        } catch (\Exception $e) {
            Log::error('ToyyibPay redirect failed: ' . $e->getMessage());
            return redirect()->route('landing')->with('error', 'Payment gateway error. Your booking is saved — you can pay at the venue.');
        }
    }
}
