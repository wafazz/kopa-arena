@extends('layouts.admin')
@section('title', 'Walk-in Booking - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Walk-in Booking</h2>
            <a href="{{ route('close-sales.index', ['branch_id' => $branchId]) }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back to close sales</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-walk"></i> Quick Booking</h3>
                <form action="{{ route('close-sales.walkin.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Facility <span class="text-danger">*</span></label>
                            <select name="facility_id" id="facility_id" class="form-select" required>
                                <option value="">Select Facility</option>
                                @php $grouped = $facilities->groupBy(fn($f) => $f->branch->name); @endphp
                                @foreach($grouped as $branchName => $branchFacilities)
                                <optgroup label="{{ $branchName }}">
                                    @foreach($branchFacilities as $facility)
                                    <option value="{{ $facility->id }}"
                                        data-rule='@json($facility->slotTimeRule)'
                                        data-pricing='@json($facility->pricings->first())'
                                        data-branch-id="{{ $facility->branch_id }}"
                                        {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                                        {{ $facility->name }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="booking_date" class="form-control" value="{{ old('booking_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Start Time <span class="text-danger">*</span></label>
                            <select name="start_time" id="start_time" class="form-select" required>
                                <option value="">Select facility first</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h3 class="title-2 m-b-25"><i class="zmdi zmdi-account"></i> Customer</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Phone</label>
                            <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
                        </div>
                    </div>

                    <hr class="my-4">
                    <h3 class="title-2 m-b-25"><i class="zmdi zmdi-money"></i> Payment</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Payment Type <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="online" {{ old('payment_type') === 'online' ? 'selected' : '' }}>Online</option>
                                <option value="bank_transfer" {{ old('payment_type') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Estimated Price</label>
                            <input type="text" id="estimated_price" class="form-control bg-light" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Notes</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Walk-in customer">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="au-btn au-btn-icon au-btn--green">
                            <i class="zmdi zmdi-check"></i>create walk-in booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SIDEBAR INFO -->
    <div class="col-lg-4">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-info-outline"></i> Booking Info</h3>
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="fas fa-futbol text-primary"></i>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Game Duration</small>
                            <div class="fw-bold" id="info_duration">-</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="fas fa-clock text-success"></i>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Time Interval</small>
                            <div class="fw-bold" id="info_interval">-</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="fas fa-calendar-day text-warning"></i>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Operating Hours</small>
                            <div class="fw-bold" id="info_hours">-</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <div class="bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="fas fa-tags text-info"></i>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Estimated Price</small>
                            <div class="fw-bold" id="info_price">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-help-outline"></i> Walk-in Info</h3>
                <ul class="list-unstyled mb-0" style="font-size:13px;">
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Booking is auto-approved & fully paid</li>
                    <li class="mb-2"><i class="zmdi zmdi-block text-danger me-2"></i>Greyed-out slots are already booked</li>
                    <li class="mb-2"><i class="zmdi zmdi-time text-warning me-2"></i>Price auto-calculates from pricing rules</li>
                    <li><i class="fas fa-cash-register text-primary me-2"></i>Appears immediately in Close Sales</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var pricingRules = @json($pricingRules);

document.getElementById('facility_id').addEventListener('change', buildTimeSlots);
document.querySelector('input[name="booking_date"]').addEventListener('change', buildTimeSlots);
document.getElementById('start_time').addEventListener('change', updatePrice);

function buildTimeSlots() {
    var facilitySelect = document.getElementById('facility_id');
    var opt = facilitySelect.options[facilitySelect.selectedIndex];
    var rule = opt.dataset.rule ? JSON.parse(opt.dataset.rule) : null;
    var select = document.getElementById('start_time');
    var bookingDate = document.querySelector('input[name="booking_date"]').value;
    select.innerHTML = '';

    if (!rule || !opt.value) {
        select.innerHTML = '<option value="">Select facility first</option>';
        document.getElementById('info_duration').textContent = '-';
        document.getElementById('info_interval').textContent = '-';
        document.getElementById('info_hours').textContent = '-';
        document.getElementById('info_price').textContent = '-';
        updatePrice();
        return;
    }

    var earliest = rule.earliest_start.substring(0, 5);
    var latest = rule.latest_start.substring(0, 5);
    var interval = rule.slot_interval;
    var duration = rule.slot_duration;

    document.getElementById('info_duration').textContent = duration + ' minutes';
    document.getElementById('info_interval').textContent = 'Every ' + interval + ' minutes';
    document.getElementById('info_hours').textContent = formatTime12(earliest) + ' - ' + formatTime12(latest);

    var [eh, em] = earliest.split(':').map(Number);
    var [lh, lm] = latest.split(':').map(Number);
    var startMin = eh * 60 + em;
    var endMin = lh * 60 + lm;

    var slots = [];
    for (var m = startMin; m <= endMin; m += interval) {
        var h = String(Math.floor(m / 60)).padStart(2, '0');
        var min = String(m % 60).padStart(2, '0');
        var val = h + ':' + min;
        var ampm = m < 720 ? 'AM' : 'PM';
        var h12 = Math.floor(m / 60) % 12 || 12;
        var label = h12 + ':' + min + ' ' + ampm;
        slots.push({ value: val, label: label, minutes: m });
    }

    if (!bookingDate) {
        slots.forEach(function(slot) {
            select.innerHTML += '<option value="' + slot.value + '">' + slot.label + '</option>';
        });
        updatePrice();
        return;
    }

    fetch('{{ route("bookings.booked-slots") }}?facility_id=' + opt.value + '&booking_date=' + bookingDate)
        .then(function(res) { return res.json(); })
        .then(function(booked) {
            var parentBookings = booked.filter(function(b) { return !b.is_child; });
            var bookedRanges = parentBookings.map(function(b) {
                var sp = b.start_time.substring(0, 5).split(':').map(Number);
                var ep = b.end_time.substring(0, 5).split(':').map(Number);
                return { start: sp[0] * 60 + sp[1], end: ep[0] * 60 + ep[1], start_time: b.start_time.substring(0, 5) };
            });

            slots.forEach(function(slot) {
                var slotStart = slot.minutes;
                var slotEnd = slotStart + duration;
                var isExact = bookedRanges.some(function(r) { return r.start_time === slot.value; });
                var isOverlap = !isExact && bookedRanges.some(function(r) { return slotStart < r.end && slotEnd > r.start; });

                if (isExact || isOverlap) {
                    select.innerHTML += '<option value="' + slot.value + '" disabled style="color:#999;text-decoration:line-through;">' + slot.label + ' (Booked)</option>';
                } else {
                    select.innerHTML += '<option value="' + slot.value + '">' + slot.label + '</option>';
                }
            });

            var firstAvailable = select.querySelector('option:not([disabled])');
            if (firstAvailable) firstAvailable.selected = true;
            updatePrice();
        });
}

function formatTime12(time24) {
    var parts = time24.split(':').map(Number);
    var h = parts[0] % 12 || 12;
    var min = String(parts[1]).padStart(2, '0');
    var ampm = parts[0] < 12 ? 'AM' : 'PM';
    return h + ':' + min + ' ' + ampm;
}

function updatePrice() {
    var facilitySelect = document.getElementById('facility_id');
    var opt = facilitySelect.options[facilitySelect.selectedIndex];
    var startTime = document.getElementById('start_time').value;
    var bookingDate = document.querySelector('input[name="booking_date"]').value;
    var priceField = document.getElementById('estimated_price');
    var infoPrice = document.getElementById('info_price');

    if (!opt || !opt.value || !startTime || !bookingDate) {
        priceField.value = '';
        infoPrice.textContent = '-';
        return;
    }

    var facilityData = @json($facilities->keyBy('id'));
    var facility = facilityData[opt.value];
    if (!facility) { priceField.value = ''; infoPrice.textContent = '-'; return; }

    var branchId = facility.branch_id;
    var dayOfWeek = new Date(bookingDate).getDay();

    var matchedRule = null;
    var fallbackRule = null;
    for (var i = 0; i < pricingRules.length; i++) {
        var rule = pricingRules[i];
        var branchIds = rule.branches.map(function(b) { return b.id; });
        if (branchIds.indexOf(branchId) === -1) continue;
        if (rule.day_of_week !== null && rule.day_of_week === dayOfWeek) {
            matchedRule = rule;
            break;
        }
        if (rule.day_of_week === null && !fallbackRule) {
            fallbackRule = rule;
        }
    }

    var activeRule = matchedRule || fallbackRule;
    var price = 0;

    if (activeRule) {
        price = parseFloat(activeRule.normal_price);
        if (activeRule.peak_start && activeRule.peak_end && activeRule.peak_price) {
            var ps = activeRule.peak_start.substring(0, 5);
            var pe = activeRule.peak_end.substring(0, 5);
            if (startTime >= ps && startTime <= pe) {
                price = parseFloat(activeRule.peak_price);
            }
        }
    } else {
        var pricing = opt.dataset.pricing ? JSON.parse(opt.dataset.pricing) : null;
        if (!pricing) { priceField.value = ''; infoPrice.textContent = '-'; return; }
        price = parseFloat(pricing.normal_price);
        if (pricing.peak_start && pricing.peak_end) {
            var ps = pricing.peak_start.substring(0, 5);
            var pe = pricing.peak_end.substring(0, 5);
            if (startTime >= ps && startTime <= pe) {
                price = parseFloat(pricing.peak_price);
            }
        }
    }

    var formatted = 'RM ' + price.toFixed(2);
    priceField.value = formatted;
    infoPrice.textContent = formatted;
}
</script>
@endpush
