<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function branchId()
    {
        return auth()->user()->branch_id;
    }

    public function index()
    {
        $facilities = Facility::where('branch_id', $this->branchId())
            ->where('status', 'active')
            ->get();
        return view('branch.reports.index', compact('facilities'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:daily,weekly,monthly,yearly',
            'report_category' => 'required|in:booking,ecommerce,combined',
            'date' => 'required|date',
            'facility_id' => 'nullable|exists:facilities,id',
        ]);

        $date = Carbon::parse($request->date);
        $reportCategory = $request->report_category;
        $facilities = Facility::where('branch_id', $this->branchId())
            ->where('status', 'active')
            ->get();

        $bookings = collect();
        $orders = collect();
        $totalAmount = 0;
        $totalBookings = 0;
        $totalOrders = 0;
        $totalOrderAmount = 0;
        $label = '';

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
            $query = Booking::with('facility')
                ->whereHas('facility', fn($q) => $q->where('branch_id', $this->branchId()))
                ->where('status', 'approved');

            if ($request->facility_id) {
                $facility = Facility::findOrFail($request->facility_id);
                if ($facility->branch_id !== $this->branchId()) {
                    abort(403);
                }
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
            $oQuery = Order::where('branch_id', $this->branchId())
                ->where('status', '!=', 'cancelled');

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

        return view('branch.reports.index', compact(
            'bookings', 'totalAmount', 'totalBookings',
            'orders', 'totalOrderAmount', 'totalOrders',
            'label', 'facilities', 'reportCategory'
        ));
    }
}
