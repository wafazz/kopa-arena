<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = 'Send WhatsApp reminders for upcoming bookings (1h, 30m, 15m before start)';

    private $reminderWindows = [
        '1h'  => ['min' => 55, 'max' => 65, 'label' => '1 hour'],
        '30m' => ['min' => 25, 'max' => 35, 'label' => '30 minutes'],
        '15m' => ['min' => 10, 'max' => 20, 'label' => '15 minutes'],
    ];

    public function handle()
    {
        $token = Setting::get('onsend_api_token');
        if (!$token) {
            $this->info('OnSend API token not configured. Skipping.');
            return;
        }

        $bookings = Booking::with('facility.branch')
            ->where('status', 'approved')
            ->whereNull('checked_in_at')
            ->where('booking_date', today())
            ->whereNotNull('customer_phone')
            ->get();

        $sent = 0;

        foreach ($bookings as $booking) {
            $startTime = Carbon::parse($booking->booking_date->format('Y-m-d') . ' ' . $booking->start_time);
            $minutesUntil = now()->diffInMinutes($startTime, false);
            $remindersSent = $booking->reminders_sent ?? [];

            foreach ($this->reminderWindows as $key => $window) {
                if (in_array($key, $remindersSent)) continue;
                if ($minutesUntil < $window['min'] || $minutesUntil > $window['max']) continue;

                $this->sendReminder($booking, $token, $window['label']);
                $remindersSent[] = $key;
                $booking->update(['reminders_sent' => $remindersSent]);
                $sent++;
                break; // one reminder per booking per run
            }
        }

        $this->info("Sent {$sent} reminder(s).");
    }

    private function sendReminder($booking, $token, $timeLabel)
    {
        $phone = preg_replace('/[^0-9]/', '', $booking->customer_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '6' . $phone;
        }
        if (!str_starts_with($phone, '6')) {
            $phone = '60' . $phone;
        }

        $facility = $booking->facility->name ?? '-';
        $branch = $booking->facility->branch->name ?? '-';
        $time = Carbon::parse($booking->start_time)->format('g:i A') . ' - ' . Carbon::parse($booking->end_time)->format('g:i A');
        $detailsUrl = route('public.booking.details', ['booking' => $booking->id]);

        $message = "*KOPA ARENA - Booking Reminder*\n\n"
            . "Hi *{$booking->customer_name}*,\n"
            . "Your booking starts in *{$timeLabel}*!\n\n"
            . "Branch: {$branch}\n"
            . "Facility: {$facility}\n"
            . "Time: {$time}\n\n"
            . "Don't forget to check in at the venue.\n"
            . "View details: {$detailsUrl}\n\n"
            . "See you there!";

        try {
            Http::withToken($token)->post('https://onsend.io/api/v1/send', [
                'phone_number' => $phone,
                'type' => 'text',
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Booking reminder failed [#' . $booking->id . ']: ' . $e->getMessage());
        }
    }
}
