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
            <h6>Slot & Operating Hours</h6>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Slot Duration (min) <span class="text-danger">*</span></label>
                    <input type="number" name="slot_duration" class="form-control" value="{{ old('slot_duration', 90) }}" min="15" required>
                    <small class="text-muted">Game length per booking.</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Default Slot Interval (min) <span class="text-danger">*</span></label>
                    <input type="number" name="slot_interval" class="form-control" value="{{ old('slot_interval', 30) }}" min="5" required>
                    <small class="text-muted">Default time between each slot.</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Open From <span class="text-danger">*</span></label>
                    <input type="time" name="earliest_start" class="form-control" value="{{ old('earliest_start', '08:00') }}" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Open Until <span class="text-danger">*</span></label>
                    <input type="time" name="latest_start" class="form-control" value="{{ old('latest_start', '22:00') }}" required>
                    <small class="text-muted">Last available slot start time.</small>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Interval Overrides <small class="text-muted">(optional — override default interval for specific time ranges)</small></label>
                <div id="interval-overrides-container">
                    @if(old('override_start'))
                        @foreach(old('override_start') as $i => $v)
                        <div class="row mb-2 interval-override-row">
                            <div class="col-md-3"><input type="time" name="override_start[]" class="form-control" value="{{ old('override_start.'.$i) }}" required></div>
                            <div class="col-md-3"><input type="time" name="override_end[]" class="form-control" value="{{ old('override_end.'.$i) }}" required></div>
                            <div class="col-md-3"><input type="number" name="override_interval[]" class="form-control" value="{{ old('override_interval.'.$i) }}" min="5" placeholder="Interval (min)" required></div>
                            <div class="col-md-3"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.interval-override-row').remove()"><i class="zmdi zmdi-close"></i> Remove</button></div>
                        </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOverrideRow()"><i class="zmdi zmdi-plus"></i> Add Override</button>
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

@push('scripts')
<script>
function addOverrideRow() {
    var row = document.createElement('div');
    row.className = 'row mb-2 interval-override-row';
    row.innerHTML = '<div class="col-md-3"><input type="time" name="override_start[]" class="form-control" required></div>'
        + '<div class="col-md-3"><input type="time" name="override_end[]" class="form-control" required></div>'
        + '<div class="col-md-3"><input type="number" name="override_interval[]" class="form-control" min="5" placeholder="Interval (min)" required></div>'
        + '<div class="col-md-3"><button type="button" class="btn btn-sm btn-danger" onclick="this.closest(\'.interval-override-row\').remove()"><i class="zmdi zmdi-close"></i> Remove</button></div>';
    document.getElementById('interval-overrides-container').appendChild(row);
}
</script>
@endpush
