# Kopa Arena — Future Plan

## High Priority (Before Go-Live)

### ~~Booking Calendar View~~ ✅ Done
- ~~Visual daily/weekly grid per facility~~
- ~~Staff can see at a glance what's booked when~~
- ~~Color-coded by status (pending/approved/checked-in)~~
- Implemented: FullCalendar 6.1.11, table/calendar toggle on bookings index, compact widget on dashboard, elegant glassmorphism design, SweetAlert popups, branch/facility filters (HQ + Branch)

### Customer Repeat Booking
- Phone number lookup to auto-fill name/email
- Simple — no customer account needed, just match by phone

### Refund Tracking
- Add `refund_status`, `refund_amount` columns to bookings
- Track refund for cancelled online-paid bookings
- Show refund status on booking details + close sales

### ~~Audit Log~~ ✅ Done
- ~~Who approved/rejected/cancelled what and when~~
- ~~Table: `audit_logs` (user_id, action, model, model_id, details, created_at)~~
- ~~Important for disputes and accountability~~
- Implemented: `activity_logs` table + ActivityLog model with static `log()` helper, all 17 HQ+Branch controllers instrumented, login/logout tracking, last_login_at + last_login_ip on users, HQ sees all logs / Branch sees own staff only, server-side pagination with filters (user, action, date range), `view_activity_logs` permission, Last Login column on staff index

## Medium Priority (After Launch)

### Promo Codes / Discounts
- Promo code input on booking form
- Percentage or fixed amount discount
- Expiry date, usage limit, branch-specific

### Recurring Bookings
- Weekly repeat for same slot (e.g. every Tuesday 8pm)
- Auto-create bookings X weeks ahead
- Easy cancel/pause recurring series

### Facility Maintenance Slots
- Block facility for certain dates/times
- Prevents bookings during maintenance/events
- Show as "unavailable" on public booking form

### Customer Booking History
- Public page — enter phone number to see all bookings
- Past + upcoming, with check-in status
- No login required

### Export Reports
- PDF/Excel export for reports
- Branch managers can print/download

## Nice to Have (Future)

### Multi-Sport Extension
- Extend beyond football to support badminton, futsal, tennis, pickleball, etc.
- **Already works**: Branches, pricing, bookings, close sales, check-in, reports, ecommerce — all sport-agnostic
- **Slot duration**: Configurable per facility via `slot_time_rules` — football 90 min, others 60 min
- **Match booking**: Works for any 2-team sport
- **Sport categorization** (required):
  - `sport_categories` table (id, name, slug, icon, duration_minutes)
  - `sport_category_id` FK on facilities table
  - Landing page: sport selector first → then branch → facility
  - Admin: filter facilities by sport category
  - Each sport defines default duration (football=90, others=60)
- **Optional**:
  - Sport-specific icons/colors on landing page
  - Rebrand from "Kopa Arena" to sport-neutral name if going multi-sport

### Membership System
- Monthly pass, loyalty points
- Member pricing tier (separate from normal/peak)

### Waiting List
- Full slot? Join queue
- Auto-notify via WhatsApp if cancellation opens up

### Email Notification Fallback
- Send email when WhatsApp fails or not configured
- Booking confirmation + reminders via email

### ~~PWA / Mobile Wrapper~~ ✅ Done
- ~~Install as app on phone~~
- ~~Useful for staff at venue (quick scanner access)~~
- Implemented: manifest.json, service worker (network-first with cache fallback), wired to admin + landing + login layouts, Apple touch icon support
