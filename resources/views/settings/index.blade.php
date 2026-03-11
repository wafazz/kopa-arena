@use('App\Models\Setting')
@extends('layouts.admin')
@section('title', 'Settings - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Site Settings</h2>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-image"></i> Logo Settings</h3>
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- LOGO (Admin Sidebar) -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Logo (Admin Panel)</label>
                            <p class="text-muted" style="font-size:0.85rem;">Used in admin sidebar. Recommended: transparent PNG, max width 200px.</p>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center mb-2" style="background:#1f2940; min-height:80px; display:flex; align-items:center; justify-content:center;">
                                <img src="{{ asset($logo ?? 'images/icon/logo.png') }}" alt="Current Logo" style="max-height:50px; max-width:180px;" id="logoPreview">
                            </div>
                            <small class="text-muted">Current logo</small>
                        </div>
                        <div class="col-md-4">
                            <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewImage(this, 'logoPreview')">
                            @error('logo')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <!-- LOGO WHITE (Landing Page) -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Logo White (Landing Page)</label>
                            <p class="text-muted" style="font-size:0.85rem;">Used in landing page navbar & footer. Recommended: white/light PNG, transparent background.</p>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 text-center mb-2" style="background:#0d1b2a; min-height:80px; display:flex; align-items:center; justify-content:center;">
                                <img src="{{ asset($logoWhite ?? 'images/icon/logo-white.png') }}" alt="Current Logo White" style="max-height:50px; max-width:180px;" id="logoWhitePreview">
                            </div>
                            <small class="text-muted">Current logo (white)</small>
                        </div>
                        <div class="col-md-4">
                            <input type="file" name="logo_white" class="form-control" accept="image/*" onchange="previewImage(this, 'logoWhitePreview')">
                            @error('logo_white')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="au-btn au-btn-icon au-btn--green">
                            <i class="zmdi zmdi-check"></i>save settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- INFO SIDEBAR -->
    <div class="col-lg-4">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-info-outline"></i> Guidelines</h3>
                <ul class="list-unstyled mb-0" style="font-size:13px;">
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Supported formats: PNG, JPG, SVG, WebP</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Max file size: 2MB</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Use transparent PNG for best results</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Admin logo appears on dark sidebar</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>White logo appears on landing page</li>
                    <li><i class="zmdi zmdi-alert-circle text-warning me-2"></i>Leave empty to keep current logo</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- PAYMENT GATEWAY SELECTOR -->
<div class="row">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-card"></i> Payment Gateway</h3>
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Active Gateway</label>
                            <p class="text-muted" style="font-size:0.85rem;">Select which payment gateway to use for online payments, or None for cash-only.</p>
                        </div>
                        <div class="col-md-8">
                            <select name="payment_gateway" class="form-select">
                                <option value="none" {{ Setting::get('payment_gateway', 'none') === 'none' ? 'selected' : '' }}>None (Cash Only)</option>
                                <option value="senangpay" {{ Setting::get('payment_gateway') === 'senangpay' ? 'selected' : '' }}>SenangPay</option>
                                <option value="toyyibpay" {{ Setting::get('payment_gateway') === 'toyyibpay' ? 'selected' : '' }}>ToyyibPay</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Deposit Percentage (%)</label>
                            <p class="text-muted" style="font-size:0.85rem;">Set the deposit percentage for online bookings. Customers can choose to pay deposit or full amount.</p>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="number" name="deposit_percentage" class="form-control" value="{{ Setting::get('deposit_percentage', 50) }}" min="1" max="100" placeholder="50">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">e.g. 50 = customer pays 50% as deposit, remaining at venue</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Referee Price (RM)</label>
                            <p class="text-muted" style="font-size:0.85rem;">Set the price for optional referee add-on. Match bookings split this equally.</p>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="number" name="referee_price" class="form-control" value="{{ Setting::get('referee_price', 0) }}" min="0" step="0.01" placeholder="0.00">
                            </div>
                            <small class="text-muted">e.g. 50.00 = RM 50 for normal booking, RM 25 per team for match booking</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="au-btn au-btn-icon au-btn--green">
                            <i class="zmdi zmdi-check"></i>save gateway setting</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-info-outline"></i> Gateway Info</h3>
                <ul class="list-unstyled mb-0" style="font-size:13px;">
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Only <strong>one gateway</strong> can be active at a time</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Set to <strong>None</strong> for cash-only mode</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Deposit % applies to online bookings</li>
                    <li class="mb-2"><i class="zmdi zmdi-alert-circle text-warning me-2"></i>Configure credentials below before activating</li>
                    <li class="mb-2"><i class="zmdi zmdi-alert-circle text-warning me-2"></i>Both gateways share the same return/callback URLs</li>
                    <li><i class="zmdi zmdi-check text-success me-2"></i>Referee price: full for normal, split for match</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- SENANGPAY SETTINGS -->
<div class="row">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-card"></i> SenangPay Payment Gateway</h3>
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf

                    <!-- Mode -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mode</label>
                            <p class="text-muted" style="font-size:0.85rem;">Select sandbox for testing or production for live payments.</p>
                        </div>
                        <div class="col-md-8">
                            <select name="senangpay_mode" class="form-select">
                                <option value="sandbox" {{ Setting::get('senangpay_mode', 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                                <option value="production" {{ Setting::get('senangpay_mode') === 'production' ? 'selected' : '' }}>Production (Live)</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- Sandbox Credentials -->
                    <h5 class="mb-3"><i class="zmdi zmdi-bug me-2"></i> Sandbox Credentials</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Merchant ID</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="senangpay_sandbox_merchant_id" class="form-control" value="{{ Setting::get('senangpay_sandbox_merchant_id') }}" placeholder="Sandbox Merchant ID">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Secret Key</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="senangpay_sandbox_secret_key" class="form-control" value="{{ Setting::get('senangpay_sandbox_secret_key') }}" placeholder="Sandbox Secret Key">
                        </div>
                    </div>

                    <hr>

                    <!-- Production Credentials -->
                    <h5 class="mb-3"><i class="zmdi zmdi-shield-check me-2"></i> Production Credentials</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Merchant ID</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="senangpay_production_merchant_id" class="form-control" value="{{ Setting::get('senangpay_production_merchant_id') }}" placeholder="Production Merchant ID">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Secret Key</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="senangpay_production_secret_key" class="form-control" value="{{ Setting::get('senangpay_production_secret_key') }}" placeholder="Production Secret Key">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="au-btn au-btn-icon au-btn--green">
                            <i class="zmdi zmdi-check"></i>save senangpay settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SENANGPAY INFO SIDEBAR -->
    <div class="col-lg-4">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-info-outline"></i> SenangPay Setup</h3>
                <ul class="list-unstyled mb-0" style="font-size:13px;">
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Register at <strong>senangpay.my</strong></li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Get Merchant ID & Secret Key from dashboard</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Use Sandbox mode for testing first</li>
                    <li class="mb-3"><i class="zmdi zmdi-check text-success me-2"></i>Switch to Production when ready</li>
                    <li class="mb-2"><i class="zmdi zmdi-alert-circle text-warning me-2"></i><strong>In your SenangPay dashboard, set:</strong></li>
                    <li class="mb-2" style="padding-left:20px;"><strong>Return URL:</strong><br><code style="font-size:11px;">{{ url('/payment/return') }}</code></li>
                    <li class="mb-2" style="padding-left:20px;"><strong>Callback URL:</strong><br><code style="font-size:11px;">{{ url('/payment/callback') }}</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- TOYYIBPAY SETTINGS -->
<div class="row">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-card"></i> ToyyibPay Payment Gateway</h3>
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf

                    <!-- Mode -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mode</label>
                            <p class="text-muted" style="font-size:0.85rem;">Select sandbox for testing or production for live payments.</p>
                        </div>
                        <div class="col-md-8">
                            <select name="toyyibpay_mode" class="form-select">
                                <option value="sandbox" {{ Setting::get('toyyibpay_mode', 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                                <option value="production" {{ Setting::get('toyyibpay_mode') === 'production' ? 'selected' : '' }}>Production (Live)</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- Sandbox Credentials -->
                    <h5 class="mb-3"><i class="zmdi zmdi-bug me-2"></i> Sandbox Credentials</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Secret Key</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="toyyibpay_sandbox_secret_key" class="form-control" value="{{ Setting::get('toyyibpay_sandbox_secret_key') }}" placeholder="Sandbox Secret Key">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Category Code</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="toyyibpay_sandbox_category_code" class="form-control" value="{{ Setting::get('toyyibpay_sandbox_category_code') }}" placeholder="Sandbox Category Code">
                        </div>
                    </div>

                    <hr>

                    <!-- Production Credentials -->
                    <h5 class="mb-3"><i class="zmdi zmdi-shield-check me-2"></i> Production Credentials</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Secret Key</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="toyyibpay_production_secret_key" class="form-control" value="{{ Setting::get('toyyibpay_production_secret_key') }}" placeholder="Production Secret Key">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Category Code</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="toyyibpay_production_category_code" class="form-control" value="{{ Setting::get('toyyibpay_production_category_code') }}" placeholder="Production Category Code">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="au-btn au-btn-icon au-btn--green">
                            <i class="zmdi zmdi-check"></i>save toyyibpay settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TOYYIBPAY INFO SIDEBAR -->
    <div class="col-lg-4">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-info-outline"></i> ToyyibPay Setup</h3>
                <ul class="list-unstyled mb-0" style="font-size:13px;">
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Register at <strong>toyyibpay.com</strong></li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Get Secret Key from your account settings</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Create a Category and copy the Category Code</li>
                    <li class="mb-3"><i class="zmdi zmdi-check text-success me-2"></i>Use Sandbox (dev.toyyibpay.com) for testing</li>
                    <li class="mb-2"><i class="zmdi zmdi-alert-circle text-warning me-2"></i><strong>In your ToyyibPay category, set:</strong></li>
                    <li class="mb-2" style="padding-left:20px;"><strong>Return URL:</strong><br><code style="font-size:11px;">{{ url('/payment/return') }}</code></li>
                    <li class="mb-2" style="padding-left:20px;"><strong>Callback URL:</strong><br><code style="font-size:11px;">{{ url('/payment/callback') }}</code></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ONSEND WHATSAPP SETTINGS -->
<div class="row">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-whatsapp"></i> WhatsApp Notification (OnSend.io)</h3>
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">API Token</label>
                            <p class="text-muted" style="font-size:0.85rem;">Get this from your OnSend.io dashboard under Devices > Token.</p>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="onsend_api_token" class="form-control" value="{{ Setting::get('onsend_api_token') }}" placeholder="Your OnSend.io API Token">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="au-btn au-btn-icon au-btn--green">
                            <i class="zmdi zmdi-check"></i>save whatsapp settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ONSEND INFO SIDEBAR -->
    <div class="col-lg-4">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-info-outline"></i> OnSend Setup</h3>
                <ul class="list-unstyled mb-0" style="font-size:13px;">
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Register at <strong>onsend.io</strong></li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Add your WhatsApp device</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Scan QR code to connect</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Copy the API Token from Devices page</li>
                    <li class="mb-2"><i class="zmdi zmdi-alert-circle text-warning me-2"></i>Customer will receive WhatsApp after booking</li>
                    <li><i class="zmdi zmdi-alert-circle text-warning me-2"></i>Phone number must include country code (e.g. 60123456789)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- CONTACT US SETTINGS -->
<div class="row">
    <div class="col-lg-8">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-phone"></i> Contact Us (Footer)</h3>
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email</label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" name="contact_email" class="form-control" value="{{ Setting::get('contact_email', 'info@kopaarena.com') }}" placeholder="info@kopaarena.com">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Phone</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="contact_phone" class="form-control" value="{{ Setting::get('contact_phone', '+60 12-345 6789') }}" placeholder="+60 12-345 6789">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Operating Hours</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="contact_hours" class="form-control" value="{{ Setting::get('contact_hours', 'Daily: 8:00 AM - 11:00 PM') }}" placeholder="Daily: 8:00 AM - 11:00 PM">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="au-btn au-btn-icon au-btn--green">
                            <i class="zmdi zmdi-check"></i>save contact settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h3 class="title-2 m-b-25"><i class="zmdi zmdi-info-outline"></i> Contact Info</h3>
                <ul class="list-unstyled mb-0" style="font-size:13px;">
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Shown in the landing page footer</li>
                    <li class="mb-2"><i class="zmdi zmdi-check text-success me-2"></i>Email, phone, and operating hours</li>
                    <li><i class="zmdi zmdi-alert-circle text-warning me-2"></i>Leave defaults if not changed</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input, previewId) {
    var preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
