@extends('layouts.admin')
@section('title', 'Edit Facility - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Edit Facility</h2>
            <a href="{{ route('branch.facilities.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
        <form action="{{ route('branch.facilities.update', $facility) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $facility->name) }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="football_field" {{ $facility->type === 'football_field' ? 'selected' : '' }}>Football Field</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $facility->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="maintenance" {{ old('status', $facility->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="closed" {{ old('status', $facility->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>
            </div>

            <hr>
            <h6>Slot Time Rules</h6>
            @php $rule = $facility->slotTimeRule; @endphp
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Slot Duration (min)</label>
                    <input type="number" name="slot_duration" class="form-control" value="{{ old('slot_duration', $rule->slot_duration ?? 90) }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Slot Interval (min)</label>
                    <input type="number" name="slot_interval" class="form-control" value="{{ old('slot_interval', $rule->slot_interval ?? 30) }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Earliest Start</label>
                    <input type="time" name="earliest_start" class="form-control" value="{{ old('earliest_start', $rule ? substr($rule->earliest_start, 0, 5) : '08:00') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Latest Start</label>
                    <input type="time" name="latest_start" class="form-control" value="{{ old('latest_start', $rule ? substr($rule->latest_start, 0, 5) : '22:00') }}" required>
                </div>
            </div>

            <hr>
            <h6>Pricing</h6>
            @php $pricing = $facility->pricings->first(); @endphp
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Normal Price (RM) <span class="text-danger">*</span></label>
                    <input type="number" name="normal_price" class="form-control" step="0.01" value="{{ old('normal_price', $pricing->normal_price ?? 0) }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Peak Price (RM)</label>
                    <input type="number" name="peak_price" class="form-control" step="0.01" value="{{ old('peak_price', $pricing->peak_price ?? 0) }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Peak Start</label>
                    <input type="time" name="peak_start" class="form-control" value="{{ old('peak_start', $pricing ? substr($pricing->peak_start, 0, 5) : '') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Peak End</label>
                    <input type="time" name="peak_end" class="form-control" value="{{ old('peak_end', $pricing ? substr($pricing->peak_end, 0, 5) : '') }}">
                </div>
            </div>

            <button type="submit" class="au-btn au-btn--green">Update Facility</button>
        </form>
            </div>
        </div>
    </div>
</div>
@endsection
