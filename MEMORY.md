# Facility Booking - Local Memory

> **Last Updated**: 2026-02-19

## Project Summary
Laravel 12 facility booking system (Kopa Arena) with multi-branch support, online payments (SenangPay), QR check-in, e-commerce shop, and WhatsApp notifications.

## Quick Reference
- **Stack**: Laravel 12, Blade, TailwindCSS, Alpine.js, Vite, SQLite
- **Auth**: Breeze (session-based, email verification)
- **Roles**: superadmin, hq_staff, branch_manager, branch_staff
- **Routes**: HQ (`/`) vs Branch (`/branch/*`) modules
- **Key Models**: User, Branch, Facility, FacilitySlot, Booking, Product, Order, CloseSale, Setting

## Recent Changes
- 2026-02-19: Activity Log + Last Login tracking feature
  - Created `activity_logs` table + `ActivityLog` model with static `log()` helper
  - Added `last_login_at` + `last_login_ip` to users table
  - HQ ActivityLogController (all logs) + Branch ActivityLogController (branch-scoped)
  - Added `ActivityLog::log()` calls to all 17 HQ+Branch controllers (store/update/destroy/approve/reject/cancel/etc)
  - Login/logout tracking in AuthenticatedSessionController
  - Sidebar links in 4 places (HQ desktop/mobile, Branch desktop/mobile)
  - Last Login column on both staff index views
  - New `view_activity_logs` permission with checkbox on staff create/edit
  - Server-side pagination (50/page) with filters (user, action, date range)

## Known Patterns
- Branch controllers mirror HQ controllers but scoped to user's branch
- Match bookings linked via `match_parent_id` (parent-child)
- Dynamic pricing via `pricing_rules` (day/time based)
- Permissions stored as JSON array on User model
- QR check-in via unique `checkin_token` per booking
- Daily sales closing locks edits for that day
