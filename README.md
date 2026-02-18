# Kopa Arena

A multi-branch facility booking and management system built with Laravel 12. Designed for sports venue operators to manage bookings, walk-ins, payments, check-ins, e-commerce, and daily sales — all from one platform.

## Features

### Booking Management
- Online booking with real-time slot availability
- Walk-in booking (POS-style) for counter staff
- Match booking — split cost between two teams
- Double-booking prevention with automatic slot conflict detection
- Booking approval workflow (approve / reject / cancel)
- Deposit and full payment tracking
- Calendar view (daily / weekly / monthly) powered by FullCalendar

### Multi-Branch Support
- Unlimited branches, each with their own facilities and staff
- HQ module (`/`) — superadmin and HQ staff manage everything
- Branch module (`/branch/*`) — branch staff scoped to their own branch only
- Role-based access: Superadmin, HQ Staff, Branch Manager, Branch Staff
- Granular permission system (11 configurable permissions per staff)

### Pricing & Sales
- Flexible pricing rules by day of week and peak/off-peak hours
- Per-branch pricing rule assignment
- Daily sales closing with full breakdown (bookings + orders)
- Day lock — prevents edits after sales are closed

### Check-In System
- QR code generated per booking
- Staff scanner via phone camera
- Self check-in for customers (30 min before slot)
- Match auto check-in — checking in one team checks in the opponent

### Online Payment
- SenangPay integration (sandbox + production)
- Auto payment verification via callback

### WhatsApp Notifications
- Booking confirmation via WhatsApp
- Automated reminders at 1 hour, 30 min, and 15 min before booking
- Powered by OnSend API

### E-Commerce (Shop)
- Product catalog with categories per branch
- Product variations (size, color) with separate stock tracking
- Shopping cart and checkout flow
- Order management (pending → paid → processing → shipped → completed)
- Delivery options: branch pickup or shipping

### Reports & Dashboard
- Booking reports filterable by date, branch, facility
- E-commerce sales reports
- Combined revenue dashboard with charts (Chart.js)
- Real-time stats: today's sales, monthly, yearly

### Activity Log & Audit Trail
- Tracks all staff actions (create, update, delete, approve, reject, cancel, login, logout)
- HQ sees all logs, Branch sees only their own staff
- Server-side pagination with filters (user, action, date range)
- Last login tracking (timestamp + IP) shown on dashboard and staff list

### PWA Support
- Installable as app on mobile devices
- Service worker with network-first caching strategy
- Useful for staff at venue (quick scanner access)

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade, TailwindCSS, Alpine.js |
| Database | SQLite (dev) / MySQL (production) |
| Auth | Laravel Breeze (session-based, email verification) |
| Build | Vite 7.0 |
| Calendar | FullCalendar 6.1.11 |
| Charts | Chart.js 4.5.1 |
| Payment | SenangPay |
| Notifications | WhatsApp via OnSend API |

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- SQLite (dev) or MySQL 8+ (production)

## Installation

```bash
# Clone the repository
git clone https://github.com/wafazz/kopa-arena.git
cd kopa-arena

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate

# Build assets
npm run build

# Start the server
php artisan serve
```

## Configuration

### Timezone
The application is configured for Malaysia Time (MYT, UTC+8) in `config/app.php`.

### SenangPay
Configure payment credentials in Settings (admin panel) or via the database:
- Sandbox/Production mode toggle
- Merchant ID and Secret Key for each mode

### WhatsApp (OnSend)
Configure the OnSend API token in Settings for booking confirmations and reminders.

### Booking Reminders
Schedule the reminder command in your server's crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## User Roles

| Role | Access |
|---|---|
| Superadmin | Full access to everything, including settings |
| HQ Staff | HQ module with configurable permissions |
| Branch Manager | Full access to their branch |
| Branch Staff | Branch module with configurable permissions |

## License

Private — all rights reserved.
