@extends('layouts.admin')
@section('title', 'Close Sales - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Close Sales</h2>
            <a href="{{ route('branch.close-sales.walkin.create') }}" class="au-btn au-btn-icon au-btn--green">
                <i class="zmdi zmdi-walk"></i>walk-in booking</a>
        </div>
    </div>
</div>

<!-- FILTER -->
<div class="row m-t-25">
    <div class="col-md-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <form method="GET" action="{{ route('branch.close-sales.index') }}" class="row align-items-end">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $date }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <button type="submit" class="au-btn au-btn-icon au-btn--blue">
                            <i class="zmdi zmdi-search"></i>load</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- STATUS BANNER -->
<div class="row">
    <div class="col-md-12">
        @if($closeSale)
        <div class="alert alert-success d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-lock me-2"></i>
                <strong>Day Closed</strong> — Closed by {{ $closeSale->closedByUser->name ?? 'Unknown' }} on {{ $closeSale->created_at->format('d M Y, h:i A') }}
            </div>
            <form action="{{ route('branch.close-sales.reopen', $closeSale) }}" method="POST" class="d-inline" id="reopenForm">
                @csrf
                <button type="button" class="btn btn-sm btn-outline-light" onclick="confirmReopen()">
                    <i class="fas fa-unlock me-1"></i>Reopen Day</button>
            </form>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="fas fa-unlock me-2"></i>
            <strong>Day Open</strong> — Sales for {{ \Carbon\Carbon::parse($date)->format('d M Y') }} have not been closed yet.
        </div>
        @endif
    </div>
</div>

<!-- BOOKING SUMMARY CARDS -->
<div class="row">
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c1">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon"><i class="zmdi zmdi-calendar-note"></i></div>
                    <div class="text">
                        <h2>{{ $summary['total'] }}</h2>
                        <span>total bookings</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c3">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon"><i class="zmdi zmdi-check-circle"></i></div>
                    <div class="text">
                        <h2>{{ $summary['approved'] }}</h2>
                        <span>approved</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c4">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon"><i class="zmdi zmdi-money"></i></div>
                    <div class="text">
                        <h2>RM {{ number_format($summary['revenue'], 2) }}</h2>
                        <span>booking revenue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c2">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon"><i class="zmdi zmdi-time-restore"></i></div>
                    <div class="text">
                        <h2>{{ $summary['deposits'] }}</h2>
                        <span>deposits</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BOOKING PAYMENT BREAKDOWN -->
<div class="row m-t-25">
    <div class="col-md-6">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-money-box"></i> Booking — By Payment Type</h3>
                <table class="table table-borderless table-sm">
                    <thead><tr><th>Type</th><th class="text-center">Count</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                    @forelse($paymentBreakdown['by_type'] as $type => $data)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                        <td class="text-center">{{ $data['count'] }}</td>
                        <td class="text-end">RM {{ number_format($data['amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-receipt"></i> Booking — By Payment Status</h3>
                <table class="table table-borderless table-sm">
                    <thead><tr><th>Status</th><th class="text-center">Count</th></tr></thead>
                    <tbody>
                    <tr>
                        <td><span class="badge bg-success">Full Payment</span></td>
                        <td class="text-center">{{ $paymentBreakdown['by_status']['full_payment'] }}</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-warning">Deposit</span></td>
                        <td class="text-center">{{ $paymentBreakdown['by_status']['deposit'] }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- BOOKINGS TABLE -->
<div class="row">
    <div class="col-md-12">
        <h2 class="title-1 m-b-25"><i class="zmdi zmdi-calendar-check"></i> Bookings for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h2>
        <div class="table-responsive table--no-card m-b-40">
            <table class="table table-borderless table-striped table-earning" data-datatable>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Facility</th>
                        <th>Time</th>
                        <th class="text-end">Amount</th>
                        <th>Pay Type</th>
                        <th>Pay Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>
                            <strong>{{ $booking->customer_name }}</strong>
                            @if($booking->customer_phone)
                            <br><small class="text-muted">{{ $booking->customer_phone }}</small>
                            @endif
                        </td>
                        <td>{{ $booking->facility->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</td>
                        <td class="text-end">RM {{ number_format($booking->amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $booking->payment_type)) }}</td>
                        <td>
                            <span class="badge bg-{{ $booking->payment_status === 'full_payment' ? 'success' : 'warning' }}">
                                {{ $booking->payment_status === 'full_payment' ? 'Full Payment' : 'Deposit' }}
                            </span>
                            @if($booking->paid_at)
                            <br><small class="text-muted">{{ $booking->paid_at->format('h:i A') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($booking->payment_status === 'deposit' && !$closeSale)
                            <form action="{{ route('branch.close-sales.mark-paid', $booking) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" title="Mark as Paid"><i class="fas fa-check-double"></i> Mark Paid</button>
                            </form>
                            @elseif($booking->payment_status === 'deposit' && $closeSale)
                            <span class="text-muted"><i class="fas fa-lock"></i> Locked</span>
                            @else
                            <span class="text-success"><i class="fas fa-check"></i> Paid</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ECOMMERCE ADDON
<!-- E-COMMERCE ORDER SUMMARY CARDS -->
<div class="row">
    <div class="col-md-12">
        <h2 class="title-1 m-b-25"><i class="fas fa-shopping-cart"></i> E-Commerce Orders</h2>
    </div>
</div>
<div class="row">
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c1">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                    <div class="text">
                        <h2>{{ $orderSummary['total'] }}</h2>
                        <span>total orders</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c3">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <div class="text">
                        <h2>{{ $orderSummary['paid'] }}</h2>
                        <span>paid orders</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c4">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon"><i class="zmdi zmdi-money"></i></div>
                    <div class="text">
                        <h2>RM {{ number_format($orderSummary['revenue'], 2) }}</h2>
                        <span>order revenue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c2">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <div class="text">
                        <h2>{{ $orderSummary['unpaid'] }}</h2>
                        <span>unpaid orders</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ORDER PAYMENT BREAKDOWN -->
<div class="row m-t-25">
    <div class="col-md-6">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-money-box"></i> Orders — By Payment Type</h3>
                <table class="table table-borderless table-sm">
                    <thead><tr><th>Type</th><th class="text-center">Count</th><th class="text-end">Amount</th></tr></thead>
                    <tbody>
                    @forelse($orderPaymentBreakdown['by_type'] as $type => $data)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                        <td class="text-center">{{ $data['count'] }}</td>
                        <td class="text-end">RM {{ number_format($data['amount'], 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted">No data</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-receipt"></i> Orders — By Payment Status</h3>
                <table class="table table-borderless table-sm">
                    <thead><tr><th>Status</th><th class="text-center">Count</th></tr></thead>
                    <tbody>
                    <tr>
                        <td><span class="badge bg-success">Paid</span></td>
                        <td class="text-center">{{ $orderPaymentBreakdown['by_status']['paid'] }}</td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-warning">Unpaid</span></td>
                        <td class="text-center">{{ $orderPaymentBreakdown['by_status']['unpaid'] }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ORDERS TABLE -->
<div class="row">
    <div class="col-md-12">
        <div class="table-responsive table--no-card m-b-40">
            <table class="table table-borderless table-striped table-earning" data-datatable>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th class="text-end">Total</th>
                        <th>Pay Type</th>
                        <th>Pay Status</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>
                            <strong>{{ $order->customer_name }}</strong>
                            @if($order->customer_phone)
                            <br><small class="text-muted">{{ $order->customer_phone }}</small>
                            @endif
                        </td>
                        <td class="text-end">RM {{ number_format($order->total_amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $order->payment_type ?? '-')) }}</td>
                        <td>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($order->payment_status ?? 'unpaid') }}
                            </span>
                        </td>
                        <td><span class="badge bg-info">{{ ucfirst($order->status) }}</span></td>
                        <td class="text-center">
                            @if($order->payment_status !== 'paid' && !$closeSale)
                            <form action="{{ route('branch.close-sales.mark-order-paid', $order) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" title="Mark as Paid"><i class="fas fa-check-double"></i> Mark Paid</button>
                            </form>
                            @elseif($order->payment_status !== 'paid' && $closeSale)
                            <span class="text-muted"><i class="fas fa-lock"></i> Locked</span>
                            @else
                            <span class="text-success"><i class="fas fa-check"></i> Paid</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
ECOMMERCE ADDON --}}

<!-- CLOSE DAY BUTTON -->
@if(!$closeSale && $bookings->count() > 0)
<div class="row m-b-30">
    <div class="col-md-12 text-center">
        <form action="{{ route('branch.close-sales.close') }}" method="POST" id="closeDayForm">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">
            <button type="button" class="au-btn au-btn-icon au-btn--green btn-lg" onclick="confirmClose()">
                <i class="zmdi zmdi-lock"></i>close day</button>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function confirmClose() {
    Swal.fire({
        title: 'Close Day?',
        text: 'This will lock all bookings for this date. No changes can be made until reopened.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, close day!'
    }).then(function(result) {
        if (result.isConfirmed) document.getElementById('closeDayForm').submit();
    });
}
function confirmReopen() {
    Swal.fire({
        title: 'Reopen Day?',
        text: 'This will unlock bookings for this date, allowing modifications.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, reopen!'
    }).then(function(result) {
        if (result.isConfirmed) document.getElementById('reopenForm').submit();
    });
}
</script>
@endpush
