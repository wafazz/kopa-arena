@use('App\Models\Facility')
@extends('layouts.admin')
@section('title', 'Bookings - Kopa Arena')

@push('styles')
<style>
.fc{--fc-border-color:rgba(255,255,255,.06);--fc-page-bg-color:transparent;--fc-neutral-bg-color:rgba(255,255,255,.03);--fc-list-event-hover-bg-color:rgba(25,135,84,.1);--fc-today-bg-color:rgba(25,135,84,.08);font-family:'Segoe UI',system-ui,-apple-system,sans-serif}
.fc .fc-toolbar{margin-bottom:1.2em}
.fc .fc-toolbar-title{color:#e8eaf0;font-size:1.15rem;font-weight:600;letter-spacing:.3px}
.fc .fc-button{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#a8afc2;font-size:.8rem;font-weight:500;padding:6px 14px;border-radius:8px;transition:all .2s ease;text-transform:capitalize;letter-spacing:.2px}
.fc .fc-button:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.18);color:#fff}
.fc .fc-button-active,.fc .fc-button:active{background:linear-gradient(135deg,#198754,#20c077)!important;border-color:transparent!important;color:#fff!important;box-shadow:0 4px 15px rgba(25,135,84,.35)}
.fc .fc-button-group{border-radius:10px;overflow:hidden;gap:2px}
.fc .fc-button-group .fc-button{border-radius:8px}
.fc .fc-col-header-cell{background:rgba(255,255,255,.03);color:#7c839a;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;padding:12px 0}
.fc .fc-col-header-cell-cushion{color:#7c839a;text-decoration:none}
.fc .fc-daygrid-day-number{color:#8b92a8;font-size:.82rem;font-weight:500;padding:8px 10px;transition:color .2s}
.fc .fc-daygrid-day:hover .fc-daygrid-day-number{color:#d1d5e0}
.fc .fc-daygrid-day.fc-day-today{background:rgba(25,135,84,.08)!important}
.fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number{color:#2dd489;font-weight:700;background:rgba(25,135,84,.15);border-radius:50%;width:30px;height:30px;display:inline-flex;align-items:center;justify-content:center;margin:4px}
.fc .fc-daygrid-day.fc-day-other .fc-daygrid-day-number{color:#464b5e}
.fc .fc-event{border:0;border-radius:6px;padding:3px 7px;font-size:.74rem;font-weight:500;cursor:pointer;transition:all .2s ease;border-left:3px solid rgba(255,255,255,.3);backdrop-filter:blur(4px);letter-spacing:.1px}
.fc .fc-event:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.3);filter:brightness(1.15)}
.fc .fc-timegrid-event .fc-event-main{padding:3px 6px;font-size:.74rem}
.fc .fc-timegrid-slot{height:3em;border-color:rgba(255,255,255,.04)!important}
.fc .fc-timegrid-slot-label{color:#5d6380;font-size:.72rem;font-weight:500}
.fc .fc-timegrid-now-indicator-line{border-color:#2dd489;border-width:2px}
.fc .fc-timegrid-now-indicator-arrow{border-color:#2dd489}
.fc .fc-popover{background:#1e2235;border:1px solid rgba(255,255,255,.1);border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.5)}
.fc .fc-popover-header{background:rgba(255,255,255,.05);color:#d1d4dc;border-radius:12px 12px 0 0;font-weight:600}
.fc .fc-scrollgrid{border-color:rgba(255,255,255,.06)!important;border-radius:12px;overflow:hidden}
.fc th,.fc td,.fc .fc-scrollgrid-sync-table{border-color:rgba(255,255,255,.06)!important}
.fc .fc-daygrid-more-link{color:#2dd489;font-weight:600;font-size:.72rem}
.fc .fc-daygrid-more-link:hover{color:#fff}
.view-toggle .btn{padding:8px 20px;font-size:.82rem;font-weight:500;border-radius:10px;transition:all .25s ease;letter-spacing:.2px}
.view-toggle .btn.active{background:linear-gradient(135deg,#198754,#20c077);border-color:transparent;color:#fff;box-shadow:0 4px 15px rgba(25,135,84,.35)}
.view-toggle .btn:not(.active){background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);color:#8b92a8}
.view-toggle .btn:not(.active):hover{background:rgba(255,255,255,.08);color:#d1d5e0}
.calendar-card{background:linear-gradient(145deg,rgba(30,34,53,.95),rgba(39,43,58,.9));border:1px solid rgba(255,255,255,.06);border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.2)}
.calendar-card .au-card-inner{padding:24px}
.cal-filter{background:rgba(255,255,255,.05)!important;border:1px solid rgba(255,255,255,.1)!important;color:#c4c9d8!important;border-radius:10px!important;padding:8px 14px!important;font-size:.82rem;transition:all .2s ease;backdrop-filter:blur(4px)}
.cal-filter:focus{border-color:rgba(25,135,84,.5)!important;box-shadow:0 0 0 3px rgba(25,135,84,.15)!important}
.cal-filter option{background:#1e2235;color:#c4c9d8}
.cal-legend{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.cal-legend-item{display:flex;align-items:center;gap:6px;font-size:.76rem;color:#8b92a8;font-weight:500}
.cal-legend-dot{width:8px;height:8px;border-radius:50%;display:inline-block}
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
            <h2 class="title-1">Bookings</h2>
            <a href="{{ route('branch.bookings.create') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-plus"></i>new booking</a>
        </div>
    </div>
</div>

<!-- SUMMARY CARDS -->
<div class="row m-t-25">
    <div class="col-sm-6 col-lg-3">
        <div class="overview-item overview-item--c1">
            <div class="overview__inner">
                <div class="overview-box clearfix">
                    <div class="icon">
                        <i class="zmdi zmdi-calendar-note"></i>
                    </div>
                    <div class="text">
                        <h2>{{ $totalBookings }}</h2>
                        <span>total bookings</span>
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
                        <i class="zmdi zmdi-time-restore"></i>
                    </div>
                    <div class="text">
                        <h2>{{ $pendingCount }}</h2>
                        <span>pending</span>
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
                        <i class="zmdi zmdi-check-circle"></i>
                    </div>
                    <div class="text">
                        <h2>{{ $approvedCount }}</h2>
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
                    <div class="icon">
                        <i class="zmdi zmdi-money"></i>
                    </div>
                    <div class="text">
                        <h2>RM {{ number_format($totalRevenue, 2) }}</h2>
                        <span>total revenue</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- VIEW TOGGLE -->
<div class="row m-t-25">
    <div class="col-md-12 d-flex align-items-center justify-content-between">
        <h2 class="title-1 mb-0">
            <i class="zmdi zmdi-calendar-check"></i> All Bookings
        </h2>
        <div class="view-toggle btn-group">
            <button class="btn active" id="btnTableView"><i class="fas fa-table me-1"></i> Table</button>
            <button class="btn" id="btnCalendarView"><i class="fas fa-calendar-alt me-1"></i> Calendar</button>
        </div>
    </div>
</div>

<!-- CALENDAR SECTION (hidden by default) -->
<div class="row m-t-25" id="calendarSection" style="display:none;">
    <div class="col-md-12">
        <div class="calendar-card m-b-30">
            <div class="au-card-inner">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <select id="calFacilityFilter" class="form-select form-select-sm cal-filter" style="width:auto;min-width:200px;">
                            <option value="">All Facilities</option>
                            @foreach(Facility::where('branch_id', auth()->user()->branch_id)->where('status','active')->get() as $fac)
                            <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="cal-legend">
                        <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#f0ad4e"></span> Pending</span>
                        <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#2dd489"></span> Approved</span>
                        <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#e74a5a"></span> Rejected</span>
                        <span class="cal-legend-item"><span class="cal-legend-dot" style="background:#6b7190"></span> Cancelled</span>
                    </div>
                </div>
                <div id="booking-calendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- TABLE SECTION -->
<div class="row m-t-25" id="tableSection">
    <div class="col-md-12">
        <div class="table-responsive table--no-card m-b-40">
            <table class="table table-borderless table-striped table-earning" data-datatable>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Facility</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th class="text-end">Amount</th>
                        <th>Payment</th>
                        <th>Pay Status</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                    @php
                        $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
                        $opponent = $booking->matchOpponent;
                    @endphp
                    <tr>
                        <td>{{ $booking->id }}</td>
                        <td>
                            <strong>{{ $booking->customer_name }}</strong>
                            @if($booking->booking_type === 'match')
                                @if($opponent)
                                    <br><small class="text-info">vs {{ $opponent->customer_name }}</small>
                                @else
                                    <br><small class="text-warning">Waiting for opponent</small>
                                @endif
                            @endif
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
                        <td>{{ $booking->booking_date->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('h:i A') }}</td>
                        <td class="text-end">
                            RM {{ number_format($booking->amount, 2) }}
                            @if($opponent)
                                <br><small class="text-muted">+ RM {{ number_format($opponent->amount, 2) }}</small>
                            @endif
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $booking->payment_type)) }}</td>
                        <td>
                            <span class="badge bg-{{ ($booking->payment_status ?? 'full_payment') === 'full_payment' ? 'success' : 'warning' }}">
                                {{ ($booking->payment_status ?? 'full_payment') === 'full_payment' ? 'Full Payment' : 'Deposit' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                            @if($opponent)
                                <br><small class="badge bg-{{ $statusColors[$opponent->status] ?? 'secondary' }} mt-1">Opp: {{ ucfirst($opponent->status) }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($booking->status === 'pending')
                            <form action="{{ route('branch.bookings.approve', $booking) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" title="Approve"><i class="fas fa-check"></i></button>
                            </form>
                            <form action="{{ route('branch.bookings.reject', $booking) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-danger" title="Reject"><i class="fas fa-times"></i></button>
                            </form>
                            @endif
                            @if($booking->status === 'approved')
                            <form action="{{ route('branch.bookings.cancel', $booking) }}" method="POST" class="d-inline" data-confirm-delete>
                                @csrf
                                <button class="btn btn-sm btn-outline-danger" title="Cancel"><i class="fas fa-ban"></i></button>
                            </form>
                            @endif
                            <a href="{{ route('branch.bookings.edit', $booking) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pencil-alt"></i></a>
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
<script src="{{ asset('vendor/fullcalendar-6.1.11/fullcalendar.min.js') }}"></script>
<script>
(function() {
    var calendar = null;
    var calEl = document.getElementById('booking-calendar');
    var tableSection = document.getElementById('tableSection');
    var calendarSection = document.getElementById('calendarSection');
    var btnTable = document.getElementById('btnTableView');
    var btnCal = document.getElementById('btnCalendarView');
    var facilityFilter = document.getElementById('calFacilityFilter');
    var editUrl = "{{ route('branch.bookings.edit', ':id') }}";

    btnTable.addEventListener('click', function() {
        tableSection.style.display = '';
        calendarSection.style.display = 'none';
        btnTable.classList.add('active');
        btnCal.classList.remove('active');
    });

    btnCal.addEventListener('click', function() {
        tableSection.style.display = 'none';
        calendarSection.style.display = '';
        btnCal.classList.add('active');
        btnTable.classList.remove('active');
        if (!calendar) initCalendar();
        else calendar.updateSize();
    });

    facilityFilter.addEventListener('change', function() {
        if (calendar) calendar.refetchEvents();
    });

    function buildPopup(p) {
        var sc = {pending:['#f0ad4e','rgba(240,173,78,.15)'],approved:['#2dd489','rgba(45,212,137,.15)'],rejected:['#e74a5a','rgba(231,74,90,.15)'],cancelled:['#6b7190','rgba(107,113,144,.15)']};
        var c = sc[p.status] || sc.cancelled;
        var statusLabel = p.status.charAt(0).toUpperCase() + p.status.slice(1);
        return '<div class="booking-detail-grid">' +
            '<div class="booking-detail-row"><div class="detail-icon" style="background:rgba(78,115,223,.15);color:#6e8ef7"><i class="fas fa-user"></i></div><div><div class="detail-label">Customer</div><div class="detail-text">' + p.customer_name + (p.customer_phone ? ' <span style="color:#6b7190">(' + p.customer_phone + ')</span>' : '') + '</div></div></div>' +
            '<div class="booking-detail-row"><div class="detail-icon" style="background:rgba(45,212,137,.15);color:#2dd489"><i class="fas fa-futbol"></i></div><div><div class="detail-label">Facility</div><div class="detail-text">' + p.facility + '</div></div></div>' +
            '<div class="booking-detail-row"><div class="detail-icon" style="background:rgba(246,194,62,.15);color:#f6c23e"><i class="fas fa-money-bill-wave"></i></div><div><div class="detail-label">Amount</div><div class="detail-text" style="font-weight:600">RM ' + p.amount + '</div></div></div>' +
            '<div class="booking-detail-row">' +
                '<div class="detail-icon" style="background:' + c[1] + ';color:' + c[0] + '"><i class="fas fa-circle"></i></div>' +
                '<div><div class="detail-label">Status</div><div class="detail-text"><span style="display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:600;background:' + c[1] + ';color:' + c[0] + '">' + statusLabel + '</span>' +
                    ' <span style="display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:500;background:rgba(255,255,255,.05);color:#8b92a8;margin-left:4px">' + (p.booking_type === 'match' ? 'Match' : 'Normal') + '</span>' +
                    (p.checked_in ? ' <span style="display:inline-block;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:500;background:rgba(54,185,204,.15);color:#36b9cc;margin-left:4px">Checked In</span>' : '') +
                '</div></div>' +
            '</div></div>';
    }

    function initCalendar() {
        calendar = new FullCalendar.Calendar(calEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            height: 720,
            slotMinTime: '06:00:00',
            slotMaxTime: '24:00:00',
            allDaySlot: false,
            nowIndicator: true,
            dayMaxEvents: 3,
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: true },
            events: function(info, successCallback, failureCallback) {
                var params = new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr
                });
                if (facilityFilter.value) params.append('facility_id', facilityFilter.value);

                fetch("{{ route('branch.bookings.calendar-events') }}?" + params.toString())
                    .then(function(r) { return r.json(); })
                    .then(successCallback)
                    .catch(failureCallback);
            },
            eventClick: function(info) {
                var p = info.event.extendedProps;
                Swal.fire({
                    html: buildPopup(p),
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-pencil-alt me-1"></i> Edit Booking',
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#198754',
                    background: '#1a1e2e',
                    color: '#d1d4dc',
                    width: 480,
                    customClass: { popup: 'swal-booking-popup' }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = editUrl.replace(':id', p.booking_id);
                    }
                });
            }
        });
        calendar.render();
    }
})();
</script>
@endpush
