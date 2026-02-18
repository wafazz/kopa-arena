<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Facility;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $facilities = Facility::with('branch')->where('status', 'active')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();
        return view('reports.index', compact('facilities', 'branches'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:daily,weekly,monthly,yearly',
            'report_category' => 'required|in:booking,ecommerce,combined',
            'date' => 'required|date',
            'facility_id' => 'nullable|exists:facilities,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $date = Carbon::parse($request->date);
        $reportCategory = $request->report_category;
        $facilities = Facility::with('branch')->where('status', 'active')->get();
        $branches = Branch::where('status', 'active')->orderBy('name')->get();

        $bookings = collect();
        $orders = collect();
        $totalAmount = 0;
        $totalBookings = 0;
        $totalOrders = 0;
        $totalOrderAmount = 0;
        $label = '';

        // Build date label
        switch ($request->report_type) {
            case 'daily':
                $label = 'Daily Report - ' . $date->format('d M Y');
                break;
            case 'weekly':
                $label = 'Weekly Report - ' . $date->copy()->startOfWeek()->format('d M') . ' to ' . $date->copy()->endOfWeek()->format('d M Y');
                break;
            case 'monthly':
                $label = 'Monthly Report - ' . $date->format('F Y');
                break;
            case 'yearly':
                $label = 'Yearly Report - ' . $date->format('Y');
                break;
        }

        // Booking query
        if ($reportCategory === 'booking' || $reportCategory === 'combined') {
            $query = Booking::with('facility.branch')->where('status', 'approved');

            if ($request->facility_id) {
                $query->where('facility_id', $request->facility_id);
            }

            switch ($request->report_type) {
                case 'daily':
                    $query->whereDate('booking_date', $date);
                    break;
                case 'weekly':
                    $query->whereBetween('booking_date', [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()]);
                    break;
                case 'monthly':
                    $query->whereMonth('booking_date', $date->month)->whereYear('booking_date', $date->year);
                    break;
                case 'yearly':
                    $query->whereYear('booking_date', $date->year);
                    break;
            }

            $bookings = $query->orderBy('booking_date')->orderBy('start_time')->get();
            $totalAmount = $bookings->sum('amount');
            $totalBookings = $bookings->count();
        }

        // Order query
        if ($reportCategory === 'ecommerce' || $reportCategory === 'combined') {
            $oQuery = Order::with('branch')->where('status', '!=', 'cancelled');

            if ($request->branch_id) {
                $oQuery->where('branch_id', $request->branch_id);
            } elseif ($request->facility_id) {
                $facility = Facility::find($request->facility_id);
                if ($facility) {
                    $oQuery->where('branch_id', $facility->branch_id);
                }
            }

            switch ($request->report_type) {
                case 'daily':
                    $oQuery->whereDate('created_at', $date);
                    break;
                case 'weekly':
                    $oQuery->whereBetween('created_at', [$date->copy()->startOfWeek()->startOfDay(), $date->copy()->endOfWeek()->endOfDay()]);
                    break;
                case 'monthly':
                    $oQuery->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year);
                    break;
                case 'yearly':
                    $oQuery->whereYear('created_at', $date->year);
                    break;
            }

            $orders = $oQuery->orderBy('created_at', 'desc')->get();
            $totalOrderAmount = $orders->sum('total_amount');
            $totalOrders = $orders->count();
        }

        return view('reports.index', compact(
            'bookings', 'totalAmount', 'totalBookings',
            'orders', 'totalOrderAmount', 'totalOrders',
            'label', 'facilities', 'branches', 'reportCategory'
        ));
    }
}
