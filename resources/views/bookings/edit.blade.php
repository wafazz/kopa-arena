@extends('layouts.admin')
@section('title', 'Edit Booking - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Edit Booking #{{ $booking->id }}</h2>
            <a href="{{ route('bookings.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

@if($booking->booking_type === 'match')
<div class="row m-t-25">
    <div class="col-md-12">
        <div class="alert alert-info d-flex align-items-center mb-0">
            <i class="fas fa-futbol me-3 fs-4"></i>
            <div>
                <strong>Match Booking</strong> — This is a match-type booking (half price per team).
                @if($booking->matchOpponent)
                    <br>Opponent: <strong>{{ $booking->matchOpponent->customer_name }}</strong>
                    (<a href="{{ route('bookings.edit', $booking->matchOpponent) }}">Edit opponent booking #{{ $booking->matchOpponent->id }}</a>)
                @elseif($booking->matchParent)
                    <br>Team A: <strong>{{ $booking->matchParent->customer_name }}</strong>
                    (<a href="{{ route('bookings.edit', $booking->matchParent) }}">Edit parent booking #{{ $booking->matchParent->id }}</a>)
                @else
                    <br><span class="text-warning">Waiting for opponent to join.</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-calendar-note"></i> Booking Details</h3>
                <form action="{{ route('bookings.update', $booking) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Facility <span class="text-danger">*</span></label>
                            <select name="facility_id" class="form-select" required>
                                @foreach($facilities as $facility)
                                <option value="{{ $facility->id }}" {{ old('facility_id', $booking->facility_id) == $facility->id ? 'selected' : '' }}>
                                    {{ $facility->name }} ({{ $facility->branch->name }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Booking Date <span class="text-danger">*</span></label>
                            <input type="date" name="booking_date" class="form-control" value="{{ old('booking_date', $booking->booking_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', substr($booking->start_time, 0, 5)) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" value="{{ old('amount', $booking->amount) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <input type="text" class="form-control" value="{{ ucfirst($booking->status) }}" readonly>
                        </div>
                    </div>

                    <hr>
                    <h3 class="title-2 m-b-25"><i class="zmdi zmdi-account"></i> Customer Details</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $booking->customer_name) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $booking->customer_phone) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $booking->customer_email) }}">
                        </div>
                    </div>

                    <hr>
                    <h3 class="title-2 m-b-25"><i class="zmdi zmdi-money"></i> Payment</h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Type</label>
                            <select name="payment_type" class="form-select">
                                <option value="cash" {{ old('payment_type', $booking->payment_type) === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="online" {{ old('payment_type', $booking->payment_type) === 'online' ? 'selected' : '' }}>Online</option>
                                <option value="bank_transfer" {{ old('payment_type', $booking->payment_type) === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="full_payment" {{ old('payment_status', $booking->payment_status) === 'full_payment' ? 'selected' : '' }}>Full Payment</option>
                                <option value="deposit" {{ old('payment_status', $booking->payment_status) === 'deposit' ? 'selected' : '' }}>Deposit</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes', $booking->notes) }}">
                        </div>
                    </div>

                    <button type="submit" class="au-btn au-btn--green">Update Booking</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
