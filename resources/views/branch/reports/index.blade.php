@extends('layouts.admin')
@section('title', 'Reports - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Sales Report</h2>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <form action="{{ route('branch.reports.generate') }}" method="POST" class="row g-3 align-items-end" id="reportForm">
                    @csrf
                    <div class="col-md-2">
                        <label class="form-label">Report Category</label>
                        <select name="report_category" class="form-select" id="reportCategory">
                            <option value="booking" {{ (isset($reportCategory) && $reportCategory === 'booking') ? 'selected' : '' }}>Booking</option>
                            <option value="ecommerce" {{ (isset($reportCategory) && $reportCategory === 'ecommerce') ? 'selected' : '' }}>E-Commerce</option>
                            <option value="combined" {{ (isset($reportCategory) && $reportCategory === 'combined') ? 'selected' : '' }}>Combined</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3" id="facilityFilter">
                        <label class="form-label">Facility (optional)</label>
                        <select name="facility_id" class="form-select">
                            <option value="">All Facilities</option>
                            @foreach($facilities as $facility)
                            <option value="{{ $facility->id }}">{{ $facility->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="au-btn au-btn--green w-100">Generate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(isset($reportCategory))
    {{-- BOOKING SECTION --}}
    @if($reportCategory === 'booking' || $reportCategory === 'combined')
    <div class="row">
        <div class="col-md-12">
            <h2 class="title-1 m-b-25">
                @if($reportCategory === 'combined')<i class="zmdi zmdi-calendar-check"></i> Bookings — @endif{{ $label }}
                <span class="badge bg-primary ms-2" style="font-size:14px;">{{ $totalBookings }} bookings</span>
                <span class="badge bg-success ms-1" style="font-size:14px;">RM {{ number_format($totalAmount, 2) }}</span>
            </h2>
            <div class="table-responsive table--no-card m-b-40">
                <table class="table table-borderless table-striped table-earning">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Facility</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Payment</th>
                            <th>Pay Status</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td>{{ $booking->customer_name }}</td>
                            <td>{{ $booking->facility->name ?? '-' }}</td>
                            <td>{{ $booking->booking_date->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $booking->payment_type)) }}</td>
                            <td>
                                <span class="badge bg-{{ ($booking->payment_status ?? 'full_payment') === 'full_payment' ? 'success' : 'warning' }}">
                                    {{ ($booking->payment_status ?? 'full_payment') === 'full_payment' ? 'Full Payment' : 'Deposit' }}
                                </span>
                            </td>
                            <td class="text-end">RM {{ number_format($booking->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No bookings found for this period</td></tr>
                        @endforelse
                    </tbody>
                    @if($bookings->count())
                    <tfoot>
                        <tr>
                            <td colspan="7" class="text-end fw-bold">Total:</td>
                            <td class="text-end fw-bold">RM {{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ORDER SECTION --}}
    @if($reportCategory === 'ecommerce' || $reportCategory === 'combined')
    <div class="row">
        <div class="col-md-12">
            <h2 class="title-1 m-b-25">
                @if($reportCategory === 'combined')<i class="fas fa-shopping-cart"></i> E-Commerce — @endif{{ $label }}
                <span class="badge bg-primary ms-2" style="font-size:14px;">{{ $totalOrders }} orders</span>
                <span class="badge bg-success ms-1" style="font-size:14px;">RM {{ number_format($totalOrderAmount, 2) }}</span>
            </h2>
            <div class="table-responsive table--no-card m-b-40">
                <table class="table table-borderless table-striped table-earning">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Pay Status</th>
                            <th>Status</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $order->payment_type ?? '-')) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->payment_status ?? 'unpaid') }}
                                </span>
                            </td>
                            <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                            <td class="text-end">RM {{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No orders found for this period</td></tr>
                        @endforelse
                    </tbody>
                    @if($orders->count())
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Total:</td>
                            <td class="text-end fw-bold">RM {{ number_format($totalOrderAmount, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- COMBINED GRAND TOTAL --}}
    @if($reportCategory === 'combined')
    <div class="row m-b-30">
        <div class="col-md-12">
            <div class="au-card">
                <div class="au-card-inner text-center">
                    <h3 class="title-2">
                        Grand Total:
                        <span class="badge bg-primary ms-2" style="font-size:16px;">{{ $totalBookings }} bookings + {{ $totalOrders }} orders</span>
                        <span class="badge bg-success ms-2" style="font-size:16px;">RM {{ number_format($totalAmount + $totalOrderAmount, 2) }}</span>
                    </h3>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif
@endsection

@push('scripts')
<script>
document.getElementById('reportCategory').addEventListener('change', function() {
    var val = this.value;
    document.getElementById('facilityFilter').style.display = (val === 'ecommerce') ? 'none' : '';
});
document.addEventListener('DOMContentLoaded', function() {
    var val = document.getElementById('reportCategory').value;
    document.getElementById('facilityFilter').style.display = (val === 'ecommerce') ? 'none' : '';
});
</script>
@endpush
