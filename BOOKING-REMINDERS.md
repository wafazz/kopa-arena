# Booking Reminders

WhatsApp reminder notifications sent automatically before booking start time.

## How It Works

- **Command**: `php artisan bookings:send-reminders`
- **Schedule**: Runs every minute via Laravel scheduler
- **Sends at**: 1 hour, 30 minutes, 15 minutes before booking start
- **Skips**: Already checked in (staff scan or self check-in) — no spam
- **Tracks**: `reminders_sent` JSON column on bookings table (`["1h", "30m", "15m"]`) — prevents duplicates
- **Message**: WhatsApp via OnSend.io with booking details + link to booking details page

## Requirements

- OnSend.io API token configured in **Settings** page
- Cron job running on server

## VPS Setup (Nginx + PHP-FPM)

Add this single cron entry:

```
* * * * * cd /path/to/facility-booking && php artisan schedule:run >> /dev/null 2>&1
```

## Local Testing

Run manually anytime:

```
php artisan bookings:send-reminders
```

## Reminder Timeline Example

For a booking at **8:00 PM**:

| Time | Reminder | Condition |
|------|----------|-----------|
| 7:00 PM | "Your booking starts in **1 hour**!" | If not checked in |
| 7:30 PM | "Your booking starts in **30 minutes**!" | If not checked in |
| 7:45 PM | "Your booking starts in **15 minutes**!" | If not checked in |

If the customer checks in at 7:35 PM, the 15-minute reminder will **not** be sent.
