@extends('layouts.admin')
@section('title', 'New Pricing Rule - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">New Pricing Rule</h2>
            <a href="{{ route('pricing-rules.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <form action="{{ route('pricing-rules.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Weekend Peak" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Day of Week <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3">
                            @php $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="day_all" value="all"
                                    {{ in_array('all', old('days', [])) ? 'checked' : '' }} onchange="toggleAllDays(this)">
                                <label class="form-check-label fw-bold" for="day_all">All Days</label>
                            </div>
                            @for($d = 0; $d <= 6; $d++)
                            <div class="form-check">
                                <input class="form-check-input day-checkbox" type="checkbox" name="days[]" value="{{ $d }}" id="day_{{ $d }}"
                                    {{ in_array((string)$d, old('days', [])) ? 'checked' : '' }} onchange="onDayChange()">
                                <label class="form-check-label" for="day_{{ $d }}">{{ $dayNames[$d] }}</label>
                            </div>
                            @endfor
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Peak Price (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="peak_price" class="form-control" step="0.01" min="0" value="{{ old('peak_price') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Peak Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="peak_start" class="form-control" id="peak_start" value="{{ old('peak_start') }}" required onchange="onPeakChange()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Peak End Time <span class="text-danger">*</span></label>
                            <input type="time" name="peak_end" class="form-control" id="peak_end" value="{{ old('peak_end') }}" required onchange="onPeakChange()">
                        </div>
                    </div>

                    <hr>
                    <h3 class="title-2 m-b-25">Assign to Facilities</h3>
                    @foreach($facilities as $branchId => $branchFacilities)
                    <div class="mb-3">
                        <h5 class="mb-2"><i class="zmdi zmdi-store"></i> {{ $branches[$branchId]->name }}</h5>
                        <div class="row ms-2">
                            @foreach($branchFacilities as $facility)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input facility-checkbox" type="checkbox" name="facilities[]" value="{{ $facility->id }}" id="facility_{{ $facility->id }}"
                                        data-facility-id="{{ $facility->id }}"
                                        {{ in_array($facility->id, old('facilities', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="facility_{{ $facility->id }}">
                                        {{ $facility->name }}
                                        <small class="text-danger d-none conflict-msg" id="conflict_{{ $facility->id }}"></small>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    <button type="submit" class="au-btn au-btn--green mt-3">Create Rule</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var existingRules = @json($existingRules);

function toggleAllDays(el) {
    var checks = document.querySelectorAll('.day-checkbox');
    checks.forEach(function(c) { c.checked = el.checked; });
    onDayChange();
}

function onDayChange() {
    var allCheck = document.getElementById('day_all');
    var checks = document.querySelectorAll('.day-checkbox');
    var allChecked = Array.from(checks).every(function(c) { return c.checked; });
    allCheck.checked = allChecked;
    updateFacilityConflicts();
}

function onPeakChange() {
    updateFacilityConflicts();
}

function getSelectedDays() {
    var allCheck = document.getElementById('day_all');
    if (allCheck.checked) return [null, 0, 1, 2, 3, 4, 5, 6];
    var days = [];
    document.querySelectorAll('.day-checkbox:checked').forEach(function(c) {
        days.push(parseInt(c.value));
    });
    return days;
}

function timeToMin(t) {
    var p = t.split(':').map(Number);
    return p[0] * 60 + p[1];
}

function timeOverlaps(s1, e1, s2, e2) {
    if (!s1 || !e1 || !s2 || !e2) return false;
    var a1 = timeToMin(s1), b1 = timeToMin(e1);
    var a2 = timeToMin(s2), b2 = timeToMin(e2);
    if (b1 <= a1) b1 += 1440;
    if (b2 <= a2) b2 += 1440;
    return a1 < b2 && a2 < b1;
}

function updateFacilityConflicts() {
    var selectedDays = getSelectedDays();
    var newPeakStart = document.getElementById('peak_start').value;
    var newPeakEnd = document.getElementById('peak_end').value;

    document.querySelectorAll('.facility-checkbox').forEach(function(cb) {
        var fid = parseInt(cb.dataset.facilityId);
        var conflict = null;

        for (var i = 0; i < existingRules.length; i++) {
            var rule = existingRules[i];
            if (rule.facility_ids.indexOf(fid) === -1) continue;
            if (!rule.peak_start || !rule.peak_end) continue;

            var daysMatch = false;
            for (var j = 0; j < selectedDays.length; j++) {
                var sd = selectedDays[j];
                if (sd === null || rule.day_of_week === null || sd === rule.day_of_week) {
                    daysMatch = true;
                    break;
                }
            }

            if (daysMatch && timeOverlaps(newPeakStart, newPeakEnd, rule.peak_start, rule.peak_end)) {
                conflict = rule;
                break;
            }
        }

        var msg = document.getElementById('conflict_' + fid);
        if (conflict) {
            cb.disabled = true;
            cb.checked = false;
            msg.textContent = '(conflict: ' + conflict.name + ')';
            msg.classList.remove('d-none');
        } else {
            cb.disabled = false;
            msg.classList.add('d-none');
        }
    });
}

updateFacilityConflicts();
</script>
@endpush
