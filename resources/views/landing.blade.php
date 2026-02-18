@extends('layouts.landing')
@section('title', 'Kopa Arena - Book Your Pitch')

@section('content')
<!-- HERO -->
<section class="hero-section" id="home">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 hero-content">
                <div class="hero-badge">FOOTBALL PITCH BOOKING</div>
                <h1 class="hero-title">Book the Pitch.<br><span>Play the Game.</span></h1>
                <p class="hero-subtitle">Your go-to platform for booking football fields across Malaysia. Find available pitches, pick your slot, and get on the field in minutes.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#book" class="btn-hero"><i class="fas fa-calendar-check me-2"></i>Book Now</a>
                    <a href="#features" class="btn-hero-outline"><i class="fas fa-info-circle me-2"></i>Learn More</a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center hero-content">
                <div style="font-size:10rem; opacity:0.15; color:#2dd881;">
                    <i class="fas fa-futbol"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SEARCH WIDGET -->
<div class="container">
    <div class="search-widget fade-up">
        <h4><i class="fas fa-search"></i> Plan Your Pitch</h4>
        <div class="row align-items-end g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Branch Location</label>
                <select class="form-select" id="search_branch">
                    <option value="">All Locations</option>
                    @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Preferred Date</label>
                <input type="date" class="form-control" id="search_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <button type="button" class="btn-find" onclick="jumpToBooking()">
                    <i class="fas fa-futbol me-2"></i>Find Your Perfect Pitch
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ABOUT -->
<section class="section-padding" id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0 fade-up">
                <div class="section-title">Why <span>Kopa Arena</span>?</div>
                <p class="text-muted mb-3">We make football pitch booking simple and hassle-free. Whether you're organizing a friendly match, a team practice, or a competitive game, Kopa Arena connects you to the best pitches in your area.</p>
                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="info-icon bg-success bg-opacity-10 me-3" style="width:40px;height:40px;min-width:40px;">
                                <i class="fas fa-check text-success"></i>
                            </div>
                            <div><strong>Instant Booking</strong></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="info-icon bg-primary bg-opacity-10 me-3" style="width:40px;height:40px;min-width:40px;">
                                <i class="fas fa-check text-primary"></i>
                            </div>
                            <div><strong>Real-time Slots</strong></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="info-icon bg-warning bg-opacity-10 me-3" style="width:40px;height:40px;min-width:40px;">
                                <i class="fas fa-check text-warning"></i>
                            </div>
                            <div><strong>Match Mode</strong></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <div class="info-icon bg-info bg-opacity-10 me-3" style="width:40px;height:40px;min-width:40px;">
                                <i class="fas fa-check text-info"></i>
                            </div>
                            <div><strong>Multiple Branches</strong></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 fade-up">
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm">
                            <div class="fs-1 fw-bold text-success">{{ $stats['branches'] }}</div>
                            <small class="text-muted">Branches</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm">
                            <div class="fs-1 fw-bold text-primary">{{ $stats['facilities'] }}</div>
                            <small class="text-muted">Facilities</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm">
                            <div class="fs-1 fw-bold text-warning">{{ $stats['bookings'] }}+</div>
                            <small class="text-muted">Bookings Made</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-4 bg-white rounded-4 shadow-sm">
                            <div class="fs-1 fw-bold text-info">24/7</div>
                            <small class="text-muted">Online Booking</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BOOKING FORM -->
<section class="section-padding bg-light-section" id="book">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <div class="section-title">Book Your <span>Pitch</span></div>
            <p class="section-subtitle">Select your preferred branch, facility, date, and time slot to book your pitch.</p>
        </div>

        <div class="row g-4">
            <!-- FORM -->
            <div class="col-lg-8 fade-up">
                <div class="booking-card">
                    <div class="booking-header">
                        <h4><i class="fas fa-calendar-plus me-2"></i> Booking Details</h4>
                    </div>
                    <div class="booking-body">
                        <form action="{{ route('public.book') }}" method="POST" id="bookingForm">
                            @csrf
                            <input type="hidden" name="match_parent_id" id="match_parent_id" value="">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <select id="branch_id" class="form-select" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Facility <span class="text-danger">*</span></label>
                                    <select name="facility_id" id="facility_id" class="form-select" required>
                                        <option value="">Select branch first</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="booking_date" id="booking_date" class="form-control" value="{{ old('booking_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Booking Type <span class="text-danger">*</span></label>
                                    <select name="booking_type" id="booking_type" class="form-select" required>
                                        <option value="normal" {{ old('booking_type') === 'match' ? '' : 'selected' }}>Normal</option>
                                        <option value="match" {{ old('booking_type') === 'match' ? 'selected' : '' }}>Match (Half Price)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Time Slot <span class="text-danger">*</span></label>
                                    <select name="start_time" id="start_time" class="form-select" required>
                                        <option value="">Select facility first</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="section-divider">
                            <h5 class="fw-bold mb-3"><i class="fas fa-user me-2 text-muted"></i> Your Details</h5>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" placeholder="Your name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" placeholder="e.g. 012-3456789" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}" placeholder="email@example.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes <small class="text-muted">(Optional)</small></label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Any special requests...">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            @if($senangpayEnabled)
                            <input type="hidden" name="payment_method" value="online">
                            @endif

                            <div class="mt-4">
                                <button type="submit" class="btn-submit-booking" id="btnSubmit">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Booking Request
                                </button>
                                <p class="text-muted text-center mt-2 mb-0" style="font-size:0.85rem;">
                                    <i class="fas fa-info-circle me-1"></i> Your booking will be reviewed and confirmed by our team.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div class="col-lg-4 fade-up">
                <div class="info-card">
                    <h5><i class="fas fa-info-circle me-2 text-primary"></i> Booking Info</h5>
                    <div class="info-item">
                        <div class="info-icon" style="background:rgba(26,135,84,0.1);">
                            <i class="fas fa-futbol" style="color:var(--ka-primary);"></i>
                        </div>
                        <div>
                            <div class="info-label">Game Duration</div>
                            <div class="info-value" id="info_duration">-</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background:rgba(13,110,253,0.1);">
                            <i class="fas fa-clock text-primary"></i>
                        </div>
                        <div>
                            <div class="info-label">Time Interval</div>
                            <div class="info-value" id="info_interval">-</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background:rgba(255,193,7,0.1);">
                            <i class="fas fa-calendar-day text-warning"></i>
                        </div>
                        <div>
                            <div class="info-label">Operating Hours</div>
                            <div class="info-value" id="info_hours">-</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background:rgba(220,53,69,0.1);">
                            <i class="fas fa-tags text-danger"></i>
                        </div>
                        <div>
                            <div class="info-label">Estimated Price</div>
                            <div class="info-value" id="info_price">-</div>
                        </div>
                    </div>
                </div>

                <div class="tips-card">
                    <h5><i class="fas fa-lightbulb me-2 text-warning"></i> Tips</h5>
                    <div class="tip-item"><i class="fas fa-check-circle text-success"></i> Select branch & facility to see available slots</div>
                    <div class="tip-item"><i class="fas fa-ban text-danger"></i> Greyed-out slots are already booked</div>
                    <div class="tip-item"><i class="fas fa-clock text-warning"></i> Each game is 90 minutes by default</div>
                    <div class="tip-item"><i class="fas fa-tags text-primary"></i> Price auto-calculates based on time slot</div>
                    <div class="tip-item"><i class="fas fa-futbol text-info"></i> Match type = half price, opponent can join</div>
                    <div class="tip-item"><i class="fas fa-star text-warning"></i> Orange slots = joinable matches</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="section-padding" id="features">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <div class="section-title">Why Choose <span>Kopa Arena</span></div>
            <p class="section-subtitle">We provide the best experience for football enthusiasts across Malaysia.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4 fade-up">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(26,135,84,0.1); color:var(--ka-primary);">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h5>Local Expertise</h5>
                    <p>We partner with the best local football venues to give you quality pitches in convenient locations.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-up">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(13,110,253,0.1); color:#0d6efd;">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h5>Safe & Secure</h5>
                    <p>Your bookings and personal data are protected with our secure booking system and verified venues.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-up">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(255,193,7,0.1); color:#ffc107;">
                        <i class="fas fa-tag"></i>
                    </div>
                    <h5>Best Prices</h5>
                    <p>Competitive and transparent pricing with no hidden fees. Match bookings let you split costs at half price.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-up">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(220,53,69,0.1); color:#dc3545;">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p>Our team is always available to assist you with any booking inquiries or special requests.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-up">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(13,202,240,0.1); color:#0dcaf0;">
                        <i class="fas fa-location-dot"></i>
                    </div>
                    <h5>Easily Accessible</h5>
                    <p>All our venues are in easily accessible locations with ample parking and great facilities.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 fade-up">
                <div class="feature-card">
                    <div class="feature-icon" style="background:rgba(111,66,193,0.1); color:#6f42c1;">
                        <i class="fas fa-gem"></i>
                    </div>
                    <h5>Premium Experience</h5>
                    <p>Enjoy well-maintained pitches, quality facilities, and a smooth booking process from start to finish.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col-6 col-lg-3 fade-up">
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['bookings'] }}+</div>
                    <div class="stat-label">Happy Players</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 fade-up">
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['branches'] }}</div>
                    <div class="stat-label">Branches</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 fade-up">
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['facilities'] }}</div>
                    <div class="stat-label">Facilities</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 fade-up">
                <div class="stat-item">
                    <div class="stat-number">5+</div>
                    <div class="stat-label">Years Experience</div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@php
$facilityList = $facilities->map(function($f) {
    return [
        'id' => $f->id,
        'name' => $f->name,
        'branch_id' => $f->branch_id,
        'rule' => $f->slotTimeRule,
        'pricing' => $f->pricings->first(),
    ];
})->values();
$facilityMap = $facilities->keyBy('id');
@endphp

@push('scripts')
<script>
var allFacilities = @json($facilityList);
var pricingRules = @json($pricingRules);
var facilityData = @json($facilityMap);

// Quick search → fill booking form
function jumpToBooking() {
    var branchVal = document.getElementById('search_branch').value;
    var dateVal = document.getElementById('search_date').value;
    if (branchVal) document.getElementById('branch_id').value = branchVal;
    if (dateVal) document.getElementById('booking_date').value = dateVal;
    filterFacilities();
    document.getElementById('book').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Filter facilities by branch
document.getElementById('branch_id').addEventListener('change', filterFacilities);

function filterFacilities() {
    var branchId = document.getElementById('branch_id').value;
    var select = document.getElementById('facility_id');
    select.innerHTML = '<option value="">Select Facility</option>';

    if (!branchId) {
        select.innerHTML = '<option value="">Select branch first</option>';
        resetInfo();
        return;
    }

    var filtered = allFacilities.filter(function(f) { return f.branch_id == branchId; });
    filtered.forEach(function(f) {
        var opt = document.createElement('option');
        opt.value = f.id;
        opt.textContent = f.name;
        opt.dataset.rule = JSON.stringify(f.rule);
        opt.dataset.pricing = JSON.stringify(f.pricing);
        select.appendChild(opt);
    });

    // Auto-select if only one facility
    if (filtered.length === 1) {
        select.value = filtered[0].id;
        buildTimeSlots();
    } else {
        resetInfo();
    }
}

document.getElementById('facility_id').addEventListener('change', buildTimeSlots);
document.getElementById('booking_date').addEventListener('change', buildTimeSlots);
document.getElementById('start_time').addEventListener('change', function() {
    updateMatchParent();
    updatePrice();
});
document.getElementById('booking_type').addEventListener('change', function() {
    buildTimeSlots();
    updatePrice();
});

function buildTimeSlots() {
    var facilitySelect = document.getElementById('facility_id');
    var opt = facilitySelect.options[facilitySelect.selectedIndex];
    var rule = opt && opt.dataset.rule ? JSON.parse(opt.dataset.rule) : null;
    var select = document.getElementById('start_time');
    var bookingDate = document.getElementById('booking_date').value;
    var bookingType = document.getElementById('booking_type').value;
    select.innerHTML = '';
    document.getElementById('match_parent_id').value = '';

    if (!rule || !opt.value) {
        select.innerHTML = '<option value="">Select facility first</option>';
        resetInfo();
        return;
    }

    var earliest = rule.earliest_start.substring(0, 5);
    var latest = rule.latest_start.substring(0, 5);
    var interval = rule.slot_interval;
    var duration = rule.slot_duration;

    document.getElementById('info_duration').textContent = duration + ' minutes';
    document.getElementById('info_interval').textContent = 'Every ' + interval + ' minutes';
    document.getElementById('info_hours').textContent = formatTime12(earliest) + ' - ' + formatTime12(latest);

    var parts_e = earliest.split(':').map(Number);
    var parts_l = latest.split(':').map(Number);
    var startMin = parts_e[0] * 60 + parts_e[1];
    var endMin = parts_l[0] * 60 + parts_l[1];

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

    fetch('{{ route("public.booked-slots") }}?facility_id=' + opt.value + '&booking_date=' + bookingDate)
        .then(function(res) { return res.json(); })
        .then(function(booked) {
            var parentBookings = booked.filter(function(b) { return !b.is_child; });

            var bookedRanges = parentBookings.map(function(b) {
                var sp = b.start_time.substring(0, 5).split(':').map(Number);
                var ep = b.end_time.substring(0, 5).split(':').map(Number);
                return {
                    start: sp[0] * 60 + sp[1],
                    end: ep[0] * 60 + ep[1],
                    type: b.type,
                    status: b.status,
                    match_id: b.match_id,
                    team_a_name: b.team_a_name,
                    start_time: b.start_time.substring(0, 5)
                };
            });

            slots.forEach(function(slot) {
                var slotStart = slot.minutes;
                var slotEnd = slotStart + duration;

                var exactBooking = null;
                for (var i = 0; i < bookedRanges.length; i++) {
                    if (bookedRanges[i].start_time === slot.value) {
                        exactBooking = bookedRanges[i];
                        break;
                    }
                }

                var isExact = exactBooking !== null;
                var isOverlap = !isExact && bookedRanges.some(function(r) {
                    return slotStart < r.end && slotEnd > r.start;
                });

                if (isExact) {
                    if (exactBooking.type === 'match' && exactBooking.status === 'match_open') {
                        if (bookingType === 'match') {
                            select.innerHTML += '<option value="' + slot.value + '" data-match-id="' + exactBooking.match_id + '" style="color:#e67e22;font-weight:bold;">' + slot.label + ' (Match vs ' + exactBooking.team_a_name + ')</option>';
                        } else {
                            select.innerHTML += '<option value="' + slot.value + '" disabled style="color:#999;text-decoration:line-through;">' + slot.label + ' (Match in progress)</option>';
                        }
                    } else {
                        select.innerHTML += '<option value="' + slot.value + '" disabled style="color:#999;text-decoration:line-through;">' + slot.label + ' (Booked)</option>';
                    }
                } else if (isOverlap) {
                    select.innerHTML += '<option value="' + slot.value + '" disabled style="color:#999;text-decoration:line-through;">' + slot.label + ' (Overlap)</option>';
                } else {
                    select.innerHTML += '<option value="' + slot.value + '">' + slot.label + '</option>';
                }
            });

            var firstAvailable = select.querySelector('option:not([disabled])');
            if (firstAvailable) firstAvailable.selected = true;

            updateMatchParent();
            updatePrice();
        });
}

function updateMatchParent() {
    var select = document.getElementById('start_time');
    var selectedOpt = select.options[select.selectedIndex];
    var matchParentInput = document.getElementById('match_parent_id');

    if (selectedOpt && selectedOpt.dataset.matchId) {
        matchParentInput.value = selectedOpt.dataset.matchId;
    } else {
        matchParentInput.value = '';
    }
}

function updatePrice() {
    var facilitySelect = document.getElementById('facility_id');
    var opt = facilitySelect.options[facilitySelect.selectedIndex];
    var startTime = document.getElementById('start_time').value;
    var bookingDate = document.getElementById('booking_date').value;
    var bookingType = document.getElementById('booking_type').value;
    var infoPrice = document.getElementById('info_price');

    if (!opt || !opt.value || !startTime || !bookingDate) {
        infoPrice.textContent = '-';
        return;
    }

    var facility = facilityData[opt.value];
    if (!facility) { infoPrice.textContent = '-'; return; }

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
        if (!pricing) { infoPrice.textContent = '-'; return; }
        price = parseFloat(pricing.normal_price);
        if (pricing.peak_start && pricing.peak_end) {
            var ps = pricing.peak_start.substring(0, 5);
            var pe = pricing.peak_end.substring(0, 5);
            if (startTime >= ps && startTime <= pe) {
                price = parseFloat(pricing.peak_price);
            }
        }
    }

    if (bookingType === 'match') {
        price = price / 2;
    }

    infoPrice.textContent = 'RM ' + price.toFixed(2);
}

function formatTime12(time24) {
    var parts = time24.split(':').map(Number);
    var h = parts[0] % 12 || 12;
    var min = String(parts[1]).padStart(2, '0');
    var ampm = parts[0] < 12 ? 'AM' : 'PM';
    return h + ':' + min + ' ' + ampm;
}

function resetInfo() {
    document.getElementById('info_duration').textContent = '-';
    document.getElementById('info_interval').textContent = '-';
    document.getElementById('info_hours').textContent = '-';
    document.getElementById('info_price').textContent = '-';
    document.getElementById('start_time').innerHTML = '<option value="">Select facility first</option>';
}

// Confirm before submit
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;

    var facilityText = document.getElementById('facility_id').options[document.getElementById('facility_id').selectedIndex].textContent;
    var dateText = document.getElementById('booking_date').value;
    var timeText = document.getElementById('start_time').options[document.getElementById('start_time').selectedIndex].textContent;
    var priceText = document.getElementById('info_price').textContent;
    var nameText = form.querySelector('[name="customer_name"]').value;

    var paymentEl = form.querySelector('[name="payment_method"]');
    var paymentMethod = paymentEl ? paymentEl.value : 'cash';
    var paymentLabel = paymentMethod === 'online' ? 'Pay Online (FPX/Card/eWallet)' : 'Pay at Venue';

    Swal.fire({
        title: 'Confirm Booking',
        html: '<div style="text-align:left;font-size:0.95rem;">' +
            '<p><strong>Name:</strong> ' + nameText + '</p>' +
            '<p><strong>Facility:</strong> ' + facilityText + '</p>' +
            '<p><strong>Date:</strong> ' + dateText + '</p>' +
            '<p><strong>Time:</strong> ' + timeText + '</p>' +
            '<p><strong>Price:</strong> ' + priceText + '</p>' +
            '<p><strong>Payment:</strong> ' + paymentLabel + '</p>' +
            '</div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1a8754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: paymentMethod === 'online' ? 'Proceed to Payment' : 'Yes, Book It!',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) form.submit();
    });
});

// Restore form state from old() values
@if(old('facility_id'))
document.addEventListener('DOMContentLoaded', function() {
    var branchId = '{{ old("branch_id", "") }}';
    if (branchId) {
        document.getElementById('branch_id').value = branchId;
        filterFacilities();
        setTimeout(function() {
            document.getElementById('facility_id').value = '{{ old("facility_id") }}';
            buildTimeSlots();
        }, 100);
    }
});
@endif
</script>
@endpush
