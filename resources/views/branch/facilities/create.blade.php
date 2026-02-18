@extends('layouts.admin')
@section('title', 'Add Facility - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Add Facility</h2>
            <a href="{{ route('branch.facilities.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
        <form action="{{ route('branch.facilities.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="football_field">Football Field</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>

            <hr>
            <h6>Pricing</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Normal Price (RM) <span class="text-danger">*</span></label>
                    <input type="number" name="normal_price" class="form-control" step="0.01" value="{{ old('normal_price', '100.00') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Peak Price (RM)</label>
                    <input type="number" name="peak_price" class="form-control" step="0.01" value="{{ old('peak_price', '150.00') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Peak Start</label>
                    <input type="time" name="peak_start" class="form-control" value="{{ old('peak_start', '18:00') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Peak End</label>
                    <input type="time" name="peak_end" class="form-control" value="{{ old('peak_end', '22:00') }}">
                </div>
            </div>

            <button type="submit" class="au-btn au-btn--green">Save Facility</button>
        </form>
            </div>
        </div>
    </div>
</div>
@endsection
