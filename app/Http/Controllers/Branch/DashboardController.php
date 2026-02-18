<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $today = Carbon::today();

        $branchScope = fn($q) => $q->where('branch_id', $branchId);

        $todaySales = Booking::whereHas('facility', $branchScope)
            ->whereDate('booking_date', $today)
            ->where('status', 'approved')
            ->sum('amount');

        $monthSales = Booking::whereHas('facility', $branchScope)
            ->whereMonth('booking_date', $today->month)
            ->whereYear('booking_date', $today->year)
            ->where('status', 'approved')
            ->sum('amount');

        $yearSales = Booking::whereHas('facility', $branchScope)
            ->whereYear('booking_date', $today->year)
            ->where('status', 'approved')
            ->sum('amount');

        $todayBookings = Booking::whereHas('facility', $branchScope)
            ->whereDate('booking_date', $today)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->count();

        $totalFacilities = Facility::where('branch_id', $branchId)
            ->where('status', 'active')
            ->count();

        $pendingCount = Booking::whereHas('facility', $branchScope)
            ->where('status', 'pending')
            ->count();

        $upcomingCount = Booking::whereHas('facility', $branchScope)
            ->where('status', 'approved')
            ->where('booking_date', '>=', $today)
            ->count();

        // Monthly sales for chart (last 6 months)
        $monthlySales = [];
        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = $today->copy()->subMonths($i);
            $monthLabels[] = $date->format('M');
            $monthlySales[] = (float) Booking::whereHas('facility', $branchScope)
                ->whereMonth('booking_date', $date->month)
                ->whereYear('booking_date', $date->year)
                ->where('status', 'approved')
                ->sum('amount');
        }

        $pendingBookings = Booking::with('facility')
            ->whereHas('facility', $branchScope)
            ->where('status', 'pending')
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        $activeBookings = Booking::with('facility')
            ->whereHas('facility', $branchScope)
            ->where('status', 'approved')
            ->where('booking_date', '>=', $today)
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // Ecommerce stats
        $totalProducts = Product::active()->where('branch_id', $branchId)->count();
        $totalOrders = Order::where('branch_id', $branchId)->count();
        $pendingOrders = Order::where('branch_id', $branchId)->where('status', 'pending')->count();
        $orderRevenue = Order::where('branch_id', $branchId)->where('payment_status', 'paid')->sum('total_amount');
        $recentOrders = Order::where('branch_id', $branchId)
            ->latest()
            ->limit(5)
            ->get();

        return view('branch.dashboard', compact(
            'todaySales', 'monthSales', 'yearSales',
            'todayBookings', 'totalFacilities', 'pendingCount', 'upcomingCount',
            'monthlySales', 'monthLabels',
            'activeBookings', 'pendingBookings',
            'totalProducts', 'totalOrders', 'pendingOrders', 'orderRevenue', 'recentOrders'
        ));
    }

    public function calendarEvents(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $branchScope = fn($q) => $q->where('branch_id', $branchId);

        $query = Booking::with('facility')
            ->whereHas('facility', $branchScope)
            ->whereNull('match_parent_id')
            ->whereBetween('booking_date', [$request->start, $request->end]);

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
