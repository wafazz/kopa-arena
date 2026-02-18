@extends('layouts.admin')
@section('title', 'Check-In History - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Check-In History</h2>
            <a href="{{ route('branch.checkins.scan') }}" class="au-btn au-btn-icon au-btn--green">
                <i class="zmdi zmdi-camera"></i>scan QR</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="au-btn au-btn--blue me-2">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('branch.checkins.index') }}" class="au-btn au-btn--blue2">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-borderless table-striped" data-datatable>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Facility</th>
                                <th>Booking Date</th>
                                <th>Time</th>
                                <th>Checked In At</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($checkins as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->customer_name }}</td>
                                <td>{{ $booking->facility->name ?? '-' }}</td>
                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('g:i A') }}</td>
                                <td>{{ $booking->checked_in_at->format('d M Y, g:i A') }}</td>
                                <td>{{ $booking->checkedInByUser->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
