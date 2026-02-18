@extends('layouts.admin')
@section('title', 'Verify Check-In - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Verify Check-In</h2>
            <a href="{{ route('checkins.scan') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back to scanner</a>
        </div>
    </div>
</div>

<div class="row m-t-25 justify-content-center">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                @if($error && !$booking)
                    <div class="alert alert-danger text-center">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ $error }}
                    </div>
                    <div class="text-center">
                        <a href="{{ route('checkins.scan') }}" class="au-btn au-btn--blue">
                            <i class="fas fa-qrcode me-1"></i> Scan Another
                        </a>
                    </div>
                @elseif($booking)
                    @if($error)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-1"></i> {{ $error }}
                    </div>
                    @endif

                    <h5 class="mb-4"><i class="fas fa-calendar-check me-2"></i>Booking #KOPA-{{ $booking->id }}</h5>

                    <table class="table table-bordered">
                        <tr>
                            <th style="width:35%;">Customer</th>
                            <td>{{ $booking->customer_name }}</td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>{{ $booking->customer_phone }}</td>
                        </tr>
                        <tr>
                            <th>Branch</th>
                            <td>{{ $booking->facility->branch->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Facility</th>
                            <td>{{ $booking->facility->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ $booking->booking_date->format('d M Y (l)') }}</td>
                        </tr>
                        <tr>
                            <th>Time</th>
                            <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>
                                @if($booking->booking_type === 'match')
                                    <span class="badge bg-info">Match</span>
                                @else
                                    <span class="badge bg-primary">Normal</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Amount</th>
                            <td>RM {{ number_format($booking->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @php
                                    $colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
                                @endphp
                                <span class="badge bg-{{ $colors[$booking->status] ?? 'secondary' }}">{{ ucfirst($booking->status) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Payment</th>
                            <td>
                                @if($booking->payment_status === 'full_payment')
                                    <span class="badge bg-success">Full Payment</span>
                                @else
                                    <span class="badge bg-warning text-dark">Deposit</span>
                                @endif
                            </td>
                        </tr>
                        @if($booking->isCheckedIn())
                        <tr>
                            <th>Checked In</th>
                            <td>
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>{{ $booking->checked_in_at->format('d M Y, g:i A') }}</span>
                                @if($booking->checkedInByUser)
                                    <small class="text-muted ms-1">by {{ $booking->checkedInByUser->name }}</small>
                                @endif
                            </td>
                        </tr>
                        @endif
                    </table>

                    <div class="text-center mt-4">
                        @if(!$error)
                        <form action="{{ route('checkins.process') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="checkin_token" value="{{ $booking->checkin_token }}">
                            <button type="submit" class="au-btn au-btn--green" onclick="return confirm('Confirm check-in for {{ $booking->customer_name }}?')">
                                <i class="fas fa-check-circle me-1"></i> Confirm Check-In
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('checkins.scan') }}" class="au-btn au-btn--blue ms-2">
                            <i class="fas fa-qrcode me-1"></i> Scan Another
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
