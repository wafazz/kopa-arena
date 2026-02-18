@extends('layouts.admin')
@section('title', 'Dashboard - Kopa Arena')

@push('styles')
<style>
.fc{--fc-border-color:rgba(255,255,255,.06);--fc-page-bg-color:transparent;--fc-neutral-bg-color:rgba(255,255,255,.03);--fc-list-event-hover-bg-color:rgba(25,135,84,.1);--fc-today-bg-color:rgba(25,135,84,.08);font-family:'Segoe UI',system-ui,-apple-system,sans-serif}
.fc .fc-toolbar{margin-bottom:1em}
.fc .fc-toolbar-title{color:#e8eaf0;font-size:1.05rem;font-weight:600;letter-spacing:.3px}
.fc .fc-button{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#a8afc2;font-size:.76rem;font-weight:500;padding:5px 12px;border-radius:8px;transition:all .2s ease;text-transform:capitalize}
.fc .fc-button:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.18);color:#fff}
.fc .fc-button-active,.fc .fc-button:active{background:linear-gradient(135deg,#198754,#20c077)!important;border-color:transparent!important;color:#fff!important;box-shadow:0 4px 15px rgba(25,135,84,.35)}
.fc .fc-col-header-cell{background:rgba(255,255,255,.03);color:#7c839a;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;padding:10px 0}
.fc .fc-col-header-cell-cushion{color:#7c839a;text-decoration:none}
.fc .fc-daygrid-day-number{color:#8b92a8;font-size:.78rem;font-weight:500;padding:6px 8px;transition:color .2s}
.fc .fc-daygrid-day:hover .fc-daygrid-day-number{color:#d1d5e0}
.fc .fc-daygrid-day.fc-day-today{background:rgba(25,135,84,.08)!important}
.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number{color:#2dd489;font-weight:700;background:rgba(25,135,84,.15);border-radius:50%;width:26px;height:26px;display:inline-flex;align-items:center;justify-content:center;margin:3px}
.fc .fc-daygrid-day.fc-day-other .fc-daygrid-day-number{color:#464b5e}
.fc .fc-event{border:0;border-radius:5px;padding:2px 5px;font-size:.68rem;font-weight:500;cursor:pointer;transition:all .2s ease;border-left:3px solid rgba(255,255,255,.3);letter-spacing:.1px}
.fc .fc-event:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.3);filter:brightness(1.15)}
.fc .fc-popover{background:#1e2235;border:1px solid rgba(255,255,255,.1);border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.5)}
.fc .fc-popover-header{background:rgba(255,255,255,.05);color:#d1d4dc;border-radius:12px 12px 0 0;font-weight:600}
.fc .fc-scrollgrid{border-color:rgba(255,255,255,.06)!important;border-radius:12px;overflow:hidden}
.fc th,.fc td,.fc .fc-scrollgrid-sync-table{border-color:rgba(255,255,255,.06)!important}
.fc .fc-daygrid-more-link{color:#2dd489;font-weight:600;font-size:.68rem}
.fc .fc-daygrid-more-link:hover{color:#fff}
.dashboard-calendar-card{background:linear-gradient(145deg,rgba(30,34,53,.95),rgba(39,43,58,.9));border:1px solid rgba(255,255,255,.06);border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.2)}
.dashboard-calendar-card .au-card-inner{padding:24px}
.dashboard-calendar-card .cal-title{display:flex;align-items:center;gap:10px;margin-bottom:16px}
.dashboard-calendar-card .cal-title-icon{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,rgba(25,135,84,.2),rgba(45,212,137,.15));display:flex;align-items:center;justify-content:center;color:#2dd489;font-size:.95rem}
.dashboard-calendar-card .cal-title h3{margin:0;font-size:1rem;font-weight:600;color:#e8eaf0;letter-spacing:.3px}
.cal-legend{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.cal-legend-item{display:flex;align-items:center;gap:6px;font-size:.72rem;color:#8b92a8;font-weight:500}
.cal-legend-dot{width:7px;height:7px;border-radius:50%;display:inline-block}
.swal-booking-popup{border-radius:16px!important;border:1px solid rgba(255,255,255,.08)!important}
.swal-booking-popup .swal2-html-container{margin:0!important;padding:20px 24px!important}
.booking-detail-grid{display:grid;gap:12px}
.booking-detail-row{display:flex;align-items:center;gap:12px;padding:10px 14px;background:rgba(255,255,255,.03);border-radius:10px;border:1px solid rgba(255,255,255,.04)}
.booking-detail-row .detail-icon{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem;flex-shrink:0}
.booking-detail-row .detail-text{font-size:.85rem;color:#d1d5e0}
.booking-detail-row .detail-label{font-size:.7rem;color:#6b7190;text-transform:uppercase;letter-spacing:.5px;font-weight:600}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Dashboard</h2>
            <a href="{{ route('bookings.create') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-plus"></i>new booking</a>
        </div>
        @if(auth()->user()->last_login_at)
        <p class="text-muted mb-0 mt-1" style="font-size: .85rem;">
            <i class="fas fa-clock"></i> Last login: {{ auth()->user()->last_login_at->format('d M Y, g:i A') }}
            @if(auth()->user()->last_login_ip)
                <span class="ms-2"><i class="fas fa-globe"></i> {{ auth()->user()->last_login_ip }}</span>
            @endif
        </p>
        @endif
    </div>
</div>

<!-- OVERVIEW CARDS -->
<div class="row m-t-25">
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c1">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon">
                        <i class="zmdi zmdi-money"></i>
                    </div>
                    <div class="text">
                        <h2>RM {{ number_format($todaySales, 2) }}</h2>
                        <span>today's sales</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c2">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon">
                        <i class="zmdi zmdi-calendar-note"></i>
                    </div>
                    <div class="text">
                        <h2>RM {{ number_format($monthSales, 2) }}</h2>
                        <span>this month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c3">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon">
                        <i class="zmdi zmdi-chart"></i>
                    </div>
                    <div class="text">
                        <h2>RM {{ number_format($yearSales, 2) }}</h2>
                        <span>this year</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c4">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon">
                        <i class="zmdi zmdi-calendar-check"></i>
                    </div>
                    <div class="text">
                        <h2>{{ $todayBookings }}</h2>
                        <span>today's bookings</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STAT CARDS ROW -->
<div class="row m-t-25">
    <div class="col-sm-6 col-lg-3">
        <div class="au-card m-b-30" style="min-height: 120px;">
            <div class="au-card-inner">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-building text-primary fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $totalBranches }}</h3>
                        <span class="text-muted">Active Branches</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="au-card m-b-30" style="min-height: 120px;">
            <div class="au-card-inner">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-futbol text-success fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $totalFacilities }}</h3>
                        <span class="text-muted">Active Facilities</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="au-card m-b-30" style="min-height: 120px;">
            <div class="au-card-inner">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $pendingCount }}</h3>
                        <span class="text-muted">Pending Bookings</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="au-card m-b-30" style="min-height: 120px;">
            <div class="au-card-inner">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-calendar-day text-info fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $activeBookings->count() }}</h3>
                        <span class="text-muted">Upcoming Bookings</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- E-COMMERCE CARDS -->
<div class="row m-t-25">
    <div class="col-md-12">
        <h2 class="title-1 m-b-25"><i class="fas fa-store"></i> E-Commerce</h2>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="au-card m-b-30" style="min-height: 120px;">
            <div class="au-card-inner">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-box-open text-primary fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $totalProducts }}</h3>
                        <span class="text-muted">Active Products</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="au-card m-b-30" style="min-height: 120px;">
            <div class="au-card-inner">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-shopping-cart text-success fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $totalOrders }}</h3>
                        <span class="text-muted">Total Orders</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="au-card m-b-30" style="min-height: 120px;">
            <div class="au-card-inner">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-clock text-warning fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-0">{{ $pendingOrders }}</h3>
                        <span class="text-muted">Pending Orders</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="au-card m-b-30" style="min-height: 120px;">
            <div class="au-card-inner">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                            <i class="fas fa-money-bill-wave text-info fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-0">RM {{ number_format($orderRevenue, 2) }}</h3>
                        <span class="text-muted">Order Revenue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RECENT ORDERS -->
@if($recentOrders->count())
<div class="row">
    <div class="col-md-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25">Recent Orders</h3>
                <div class="table-responsive">
                    <table class="table table-borderless table-striped">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Branch</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td><a href="{{ route('orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a></td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->branch->name ?? '-' }}</td>
                                <td>RM {{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    @php $sc = ['pending'=>'secondary','paid'=>'info','processing'=>'primary','shipped'=>'warning','completed'=>'success','cancelled'=>'danger']; @endphp
                                    <span class="badge bg-{{ $sc[$order->status] ?? 'secondary' }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td>{{ $order->created_at->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- CHARTS ROW -->
<div class="row">
    <div class="col-lg-8">
        <div class="au-card recent-report m-b-40">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25">Monthly Revenue ({{ date('Y') }})</h3>
                <div class="recent-report__chart">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="au-card chart-percent-card m-b-40">
            <div class="au-card-inner">
                <h3 class="title-2 tm-b-5">Bookings by Branch</h3>
                <div class="percent-chart">
                    <canvas id="branchChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BOOKING CALENDAR -->
<div class="row">
    <div class="col-md-12">
        <div class="dashboard-calendar-card m-b-30">
            <div class="au-card-inner">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div class="cal-title">
                        <div class="cal-title-icon"><i class="fas fa-calendar-alt"></i></div>
                        <h3>Booking Calendar</h3>
                    </div>
                    <div class="cal-legend">
                        <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#f0ad4e"></span> Pending</span>
                        <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#2dd489"></span> Approved</span>
                        <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#e74a5a"></span> Rejected</span>
                        <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#6b7190"></span> Cancelled</span>
                    </div>
                </div>
                <div id="dashboard-calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- PENDING BOOKINGS -->
@if($pendingBookings->count())
<div class="row">
    <div class="col-md-12">
        <h2 class="title-1 m-b-25">
            <i class="zmdi zmdi-time-restore"></i> Pending Bookings
            <span class="badge bg-warning text-dark ms-2" style="font-size:14px;">{{ $pendingCount }}</span>
        </h2>
        <div class="table-responsive table--no-card m-b-40">
            <table class="table table-borderless table-striped table-earning" data-datatable>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Facility</th>
                        <th>Branch</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th class="text-end">Amount</th>
                        <th>Pay Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingBookings as $booking)
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>
                            <strong>{{ $booking->customer_name }}</strong>
                            @if($booking->customer_phone)
                            <br><small class="text-muted">{{ $booking->customer_phone }}</small>
                            @endif
                        </td>
                        <td>
                            @if($booking->booking_type === 'match')
                                <span class="badge bg-info">Match</span>
                            @else
                                <span class="badge bg-secondary">Normal</span>
                            @endif
                        </td>
                        <td>{{ $booking->facility->name ?? '-' }}</td>
                        <td>{{ $booking->facility->branch->name ?? '-' }}</td>
                        <td>{{ $booking->booking_date->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }}</td>
                        <td class="text-end">RM {{ number_format($booking->amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ ($booking->payment_status ?? 'full_payment') === 'full_payment' ? 'success' : 'warning' }}">
                                {{ ($booking->payment_status ?? 'full_payment') === 'full_payment' ? 'Full Payment' : 'Deposit' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('bookings.approve', $booking) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                            </form>
                            <form action="{{ route('bookings.reject', $booking) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- UPCOMING APPROVED BOOKINGS -->
<div class="row">
    <div class="col-md-12">
        <h2 class="title-1 m-b-25"><i class="zmdi zmdi-calendar-check"></i> Upcoming Approved Bookings</h2>
        <div class="table-responsive table--no-card m-b-40">
            <table class="table table-borderless table-striped table-earning" data-datatable>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Facility</th>
                        <th>Branch</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th class="text-end">Amount</th>
                        <th>Pay Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeBookings as $booking)
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>
                            <strong>{{ $booking->customer_name }}</strong>
                            @if($booking->customer_phone)
                            <br><small class="text-muted">{{ $booking->customer_phone }}</small>
                            @endif
                        </td>
                        <td>
                            @if($booking->booking_type === 'match')
                                <span class="badge bg-info">Match</span>
                            @else
                                <span class="badge bg-secondary">Normal</span>
                            @endif
                        </td>
                        <td>{{ $booking->facility->name ?? '-' }}</td>
                        <td>{{ $booking->facility->branch->name ?? '-' }}</td>
                        <td>{{ $booking->booking_date->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</td>
                        <td class="text-end">RM {{ number_format($booking->amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ ($booking->payment_status ?? 'full_payment') === 'full_payment' ? 'success' : 'warning' }}">
                                {{ ($booking->payment_status ?? 'full_payment') === 'full_payment' ? 'Full Payment' : 'Deposit' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/chartjs/chart.umd.js-4.5.1.min.js') }}"></script>
<script src="{{ asset('vendor/fullcalendar-6.1.11/fullcalendar.min.js') }}"></script>
<script>
(function() {
    // Dashboard Calendar
    function buildDashPopup(p) {
        var sc = {pending:['#f0ad4e','rgba(240,173,78,.15)'],approved:['#2dd489','rgba(45,212,137,.15)'],rejected:['#e74a5a','rgba(231,74,90,.15)'],cancelled:['#6b7190','rgba(107,113,144,.15)']};
        var c = sc[p.status] || sc.cancelled;
        var statusLabel = p.status.charAt(0).toUpperCase() + p.status.slice(1);
        return '<div class="booking-detail-grid">' +
            '<div class="booking-detail-row"><div class="detail-icon" style="background:rgba(78,115,223,.15);color:#6e8ef7"><i class="fas fa-user"></i></div><div><div class="detail-label">Customer</div><div class="detail-text">' + p.customer_name + (p.customer_phone ? ' <span style="color:#6b7190">(' + p.customer_phone + ')</span>' : '') + '</div></div></div>' +
            '<div class="booking-detail-row"><div class="detail-icon" style="background:rgba(45,212,137,.15);color:#2dd489"><i class="fas fa-futbol"></i></div><div><div class="detail-label">Facility</div><div class="detail-text">' + p.facility + '</div></div></div>' +
            '<div class="booking-detail-row"><div class="detail-icon" style="background:rgba(54,185,204,.15);color:#36b9cc"><i class="fas fa-building"></i></div><div><div class="detail-label">Branch</div><div class="detail-text">' + p.branch + '</div></div></div>' +
            '<div class="booking-detail-row"><div class="detail-icon" style="background:rgba(246,194,62,.15);color:#f6c23e"><i class="fas fa-money-bill-wave"></i></div><div><div class="detail-label">Amount</div><div class="detail-text" style="font-weight:600">RM ' + p.amount + '</div></div></div>' +
            '<div class="booking-detail-row"><div class="detail-icon" style="background:' + c[1] + ';color:' + c[0] + '"><i class="fas fa-circle"></i></div><div><div class="detail-label">Status</div><div class="detail-text"><span style="display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:600;background:' + c[1] + ';color:' + c[0] + '">' + statusLabel + '</span> <span style="display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:500;background:rgba(255,255,255,.05);color:#8b92a8;margin-left:4px">' + (p.booking_type === 'match' ? 'Match' : 'Normal') + '</span>' + (p.checked_in ? ' <span style="display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:500;background:rgba(54,185,204,.15);color:#36b9cc;margin-left:4px">Checked In</span>' : '') + '</div></div></div></div>';
    }

    var dashCalEl = document.getElementById('dashboard-calendar');
    if (dashCalEl) {
        var dashCal = new FullCalendar.Calendar(dashCalEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            height: 520,
            dayMaxEvents: 3,
            events: function(info, successCallback, failureCallback) {
                var params = new URLSearchParams({ start: info.startStr, end: info.endStr });
                fetch("{{ route('dashboard.calendar-events') }}?" + params.toString())
                    .then(function(r) { return r.json(); })
                    .then(successCallback)
                    .catch(failureCallback);
            },
            eventClick: function(info) {
                var p = info.event.extendedProps;
                Swal.fire({
                    html: buildDashPopup(p),
                    confirmButtonText: 'Close',
                    confirmButtonColor: '#198754',
                    background: '#1a1e2e',
                    color: '#d1d4dc',
                    width: 480,
                    customClass: { popup: 'swal-booking-popup' }
                });
            }
        });
        dashCal.render();
    }

    // Monthly Sales Bar Chart
    var salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: @json($monthLabels),
                datasets: [{
                    label: 'Revenue (RM)',
                    data: @json($monthlySales),
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(133, 135, 150, 0.8)'
                    ],
                    borderColor: [
                        'rgb(78, 115, 223)',
                        'rgb(28, 200, 138)',
                        'rgb(54, 185, 204)',
                        'rgb(246, 194, 62)',
                        'rgb(231, 74, 59)',
                        'rgb(133, 135, 150)'
                    ],
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return 'RM ' + value.toLocaleString(); }
                        }
                    }
                }
            }
        });
    }

    // Bookings by Branch Doughnut Chart
    var branchCtx = document.getElementById('branchChart');
    if (branchCtx) {
        var branchData = @json($branchBookings);
        var hasData = branchData.some(function(v) { return v > 0; });

        new Chart(branchCtx, {
            type: 'doughnut',
            data: {
                labels: hasData ? @json($branchLabels) : ['No data'],
                datasets: [{
                    data: hasData ? branchData : [1],
                    backgroundColor: hasData ? [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(28, 200, 138, 0.8)',
                        'rgba(54, 185, 204, 0.8)',
                        'rgba(246, 194, 62, 0.8)',
                        'rgba(231, 74, 59, 0.8)',
                        'rgba(133, 135, 150, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ] : ['rgba(200, 200, 200, 0.3)'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15 }
                    }
                }
            }
        });
    }
})();
</script>
@endpush
