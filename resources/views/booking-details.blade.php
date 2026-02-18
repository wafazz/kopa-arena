@extends('layouts.landing')
@section('title', 'Booking Details - Kopa Arena')

@section('content')
<section style="padding-top:120px; padding-bottom:80px; min-height:80vh; background:var(--ka-light);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="booking-card">
                    <div class="booking-header text-center">
                        <h4><i class="fas fa-calendar-check me-2"></i> Booking Confirmation</h4>
                        <p class="mb-0 mt-1" style="font-size:0.85rem; opacity:0.8;">Reference: #KOPA-{{ $booking->id }}</p>
                    </div>
                    <div class="booking-body">
                        <!-- Status Badge -->
                        <div class="text-center mb-4">
                            @php
                                $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
                                $statusColor = $statusColors[$booking->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} px-3 py-2" style="font-size:0.9rem;">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>

                        <!-- Customer Info -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-user me-2"></i>Customer Information</h6>
                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Name</small>
                                    <strong>{{ $booking->customer_name }}</strong>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Phone</small>
                                    <strong>{{ $booking->customer_phone }}</strong>
                                </div>
                                @if($booking->customer_email)
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Email</small>
                                    <strong>{{ $booking->customer_email }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>

                        <hr>

                        <!-- Booking Details -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-futbol me-2"></i>Booking Details</h6>
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Branch</small>
                                    <strong>{{ $booking->facility->branch->name ?? '-' }}</strong>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Facility</small>
                                    <strong>{{ $booking->facility->name ?? '-' }}</strong>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Date</small>
                                    <strong>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y (l)') }}</strong>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Time</small>
                                    <strong>{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}</strong>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Type</small>
                                    <strong>{{ ucfirst($booking->booking_type) }}</strong>
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Amount</small>
                                    <strong class="text-success" style="font-size:1.1rem;">RM {{ number_format($booking->amount, 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Payment Info -->
                        <div class="mb-3">
                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-credit-card me-2"></i>Payment</h6>
                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Payment Status</small>
                                    @if($booking->payment_status === 'full_payment')
                                        <span class="badge bg-success">Full Payment</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Deposit</span>
                                    @endif
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Payment Type</small>
                                    <strong>{{ ucfirst($booking->payment_type) }}</strong>
                                </div>
                            </div>
                        </div>

                        @if($booking->notes)
                        <hr>
                        <div>
                            <h6 class="fw-bold text-muted mb-2"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                            <p class="mb-0">{{ $booking->notes }}</p>
                        </div>
                        @endif

                        <hr>

                        <!-- QR Code Check-In -->
                        <div class="text-center mb-4">
                            <h6 class="fw-bold text-muted mb-3"><i class="fas fa-qrcode me-2"></i>Check-In QR Code</h6>

                            @if($booking->isCheckedIn())
                                <div id="qr-canvas" class="mb-2" style="opacity:0.3; filter:grayscale(100%);"></div>
                                <div class="alert alert-success py-2 px-3 d-inline-block">
                                    <i class="fas fa-check-circle me-1"></i> Checked in at {{ $booking->checked_in_at->format('d M Y, g:i A') }}
                                </div>
                            @elseif(in_array($booking->status, ['rejected', 'cancelled']))
                                <div id="qr-canvas" class="mb-2" style="opacity:0.3; filter:grayscale(100%);"></div>
                                <div class="alert alert-secondary py-2 px-3 d-inline-block">
                                    <i class="fas fa-ban me-1"></i> Booking {{ $booking->status }}
                                </div>
                            @else
                                <div id="qr-canvas" class="mb-2"></div>
                                <div id="qr-status-msg" class="mb-3"></div>

                                @if($booking->status === 'approved')
                                <!-- Self Check-In -->
                                <div class="mt-3" id="self-checkin-section">
                                    <p class="text-muted mb-2" style="font-size:0.85rem;">Or check in yourself:</p>
                                    <div id="checkin-countdown" class="mb-2" style="display:none;">
                                        <small class="text-muted">Self check-in opens in <strong id="countdown-timer"></strong></small>
                                    </div>
                                    <form action="{{ route('public.booking.self-checkin', $booking) }}" method="POST" id="self-checkin-form" style="display:none;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Confirm self check-in?')">
                                            <i class="fas fa-check-circle me-1"></i> Self Check-In
                                        </button>
                                    </form>
                                    <div id="checkin-not-today" style="display:none;">
                                        <small class="text-muted">Self check-in available on booking date</small>
                                    </div>
                                </div>
                                @endif
                            @endif
                        </div>

                        <hr>
                        <div class="text-center">
                            <a href="{{ route('landing') }}" class="btn btn-outline-success">
                                <i class="fas fa-arrow-left me-1"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('qr-canvas');
    if (container) {
        new QRCode(container, {
            text: '{{ url("/checkin/verify/" . $booking->checkin_token) }}',
            width: 200,
            height: 200,
            colorDark: '#1a1a2e',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    @if(!$booking->isCheckedIn() && !in_array($booking->status, ['rejected', 'cancelled']))
    var bookingDate = '{{ $booking->booking_date->format("Y-m-d") }}';
    var startTime = '{{ $booking->start_time }}';
    var startDateTime = new Date(bookingDate + 'T' + startTime);
    var windowOpen = new Date(startDateTime.getTime() - 30 * 60000);
    var statusMsg = document.getElementById('qr-status-msg');

    // QR active window message
    function updateQrStatus() {
        var now = new Date();
        var today = now.toISOString().split('T')[0];

        if (today !== bookingDate) {
            statusMsg.innerHTML = '<small class="text-muted"><i class="fas fa-clock me-1"></i>QR code can be used on ' + new Date(bookingDate).toLocaleDateString('en-GB', {day:'numeric',month:'short',year:'numeric'}) + ', 30 minutes before your booking</small>';
            return;
        }

        if (now >= windowOpen) {
            statusMsg.innerHTML = '<small class="text-success"><i class="fas fa-check-circle me-1"></i>QR code is active — show to staff at the venue</small>';
            return;
        }

        var diff = Math.ceil((windowOpen - now) / 1000);
        var h = Math.floor(diff / 3600);
        var m = Math.floor((diff % 3600) / 60);
        var s = diff % 60;
        var parts = [];
        if (h > 0) parts.push(h + 'h');
        parts.push(m + 'm');
        parts.push(s + 's');
        statusMsg.innerHTML = '<small class="text-muted"><i class="fas fa-clock me-1"></i>QR code activates in <strong>' + parts.join(' ') + '</strong></small>';
        setTimeout(updateQrStatus, 1000);
    }
    updateQrStatus();

    @if($booking->status === 'approved')
    // Self check-in countdown logic
    var windowClose = new Date(startDateTime.getTime() + 30 * 60000);
    var form = document.getElementById('self-checkin-form');
    var countdown = document.getElementById('checkin-countdown');
    var timer = document.getElementById('countdown-timer');
    var notToday = document.getElementById('checkin-not-today');

    function updateCheckin() {
        var now = new Date();
        var today = now.toISOString().split('T')[0];

        if (today !== bookingDate) {
            notToday.style.display = 'block';
            return;
        }

        if (now >= windowOpen && now <= windowClose) {
            form.style.display = 'block';
            countdown.style.display = 'none';
        } else if (now < windowOpen) {
            countdown.style.display = 'block';
            form.style.display = 'none';
            var diff = Math.ceil((windowOpen - now) / 1000);
            var h = Math.floor(diff / 3600);
            var m = Math.floor((diff % 3600) / 60);
            var s = diff % 60;
            var parts = [];
            if (h > 0) parts.push(h + 'h');
            parts.push(m + 'm');
            parts.push(s + 's');
            timer.textContent = parts.join(' ');
        } else {
            document.getElementById('self-checkin-section').innerHTML = '<small class="text-muted">Self check-in window has expired. Please contact staff.</small>';
            return;
        }
        setTimeout(updateCheckin, 1000);
    }
    updateCheckin();
    @endif
    @endif
});
</script>
@endpush
