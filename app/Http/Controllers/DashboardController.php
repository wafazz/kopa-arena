<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Facility;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isBranchStaff()) {
            return redirect()->route('branch.dashboard');
        }

        $today = Carbon::today();

        $todaySales = Booking::whereDate('booking_date', $today)
            ->where('status', 'approved')
            ->sum('amount');

        $monthSales = Booking::whereMonth('booking_date', $today->month)
            ->whereYear('booking_date', $today->year)
            ->where('status', 'approved')
            ->sum('amount');

        $yearSales = Booking::whereYear('booking_date', $today->year)
            ->where('status', 'approved')
            ->sum('amount');

        $todayBookings = Booking::whereDate('booking_date', $today)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->count();

        $totalBranches = Branch::where('status', 'active')->count();
        $totalFacilities = Facility::where('status', 'active')->count();

        $pendingCount = Booking::where('status', 'pending')->count();

        // Monthly sales for chart (last 6 months)
        $monthlySales = [];
        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = $today->copy()->subMonths($i);
            $monthLabels[] = $date->format('M');
            $monthlySales[] = (float) Booking::whereMonth('booking_date', $date->month)
                ->whereYear('booking_date', $date->year)
                ->where('status', 'approved')
                ->sum('amount');
        }

        // Bookings by branch for chart
        $branches = Branch::where('status', 'active')->get();
        $branchLabels = [];
        $branchBookings = [];
        foreach ($branches as $branch) {
            $branchLabels[] = $branch->name;
            $branchBookings[] = Booking::whereHas('facility', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->where('status', 'approved')
              ->whereYear('booking_date', $today->year)
              ->count();
        }

        $activeBookings = Booking::with('facility.branch')
            ->where('status', 'approved')
            ->where('booking_date', '>=', $today)
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        $pendingBookings = Booking::with('facility.branch')
            ->where('status', 'pending')
            ->orderBy('booking_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // Ecommerce stats
        $totalProducts = Product::active()->count();
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $orderRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $recentOrders = Order::with('branch')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'todaySales', 'monthSales', 'yearSales',
            'todayBookings', 'totalBranches', 'totalFacilities', 'pendingCount',
            'monthlySales', 'monthLabels', 'branchLabels', 'branchBookings',
            'activeBookings', 'pendingBookings',
            'totalProducts', 'totalOrders', 'pendingOrders', 'orderRevenue', 'recentOrders'
        ));
    }

    public function calendarEvents(Request $request)
    {
        $query = Booking::with('facility.branch')
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
