@extends('layouts.admin')
@section('title', 'Scan QR Check-In - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Scan QR Check-In</h2>
            <a href="{{ route('checkins.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-format-list-bulleted"></i>history</a>
        </div>
    </div>
</div>

<div class="row m-t-25 justify-content-center">
    <div class="col-lg-6">
        <div class="au-card m-b-30">
            <div class="au-card-inner text-center">
                <h5 class="mb-3"><i class="fas fa-camera me-2"></i>Camera Scanner</h5>
                <div id="qr-reader" style="width:100%; max-width:400px; margin:0 auto;"></div>
                <div id="qr-result" class="mt-3" style="display:none;">
                    <div class="alert alert-info">
                        <i class="fas fa-spinner fa-spin me-1"></i> Processing...
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h5 class="mb-3"><i class="fas fa-keyboard me-2"></i>Manual Entry</h5>
                <form action="{{ route('checkins.verify', ':token') }}" method="GET" id="manual-form">
                    <div class="mb-3">
                        <label class="form-label">Enter Check-In Token</label>
                        <input type="text" id="manual-token" class="form-control" placeholder="Paste or type token here..." required>
                    </div>
                    <button type="submit" class="au-btn au-btn--green w-100">
                        <i class="fas fa-search me-1"></i> Verify Token
                    </button>
                </form>
            </div>
        </div>
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Instructions</h5>
                <ol class="mb-0" style="padding-left:1.2rem;">
                    <li class="mb-2">Point camera at customer's QR code</li>
                    <li class="mb-2">Or paste the check-in token manually</li>
                    <li class="mb-2">Verify booking details on next page</li>
                    <li>Click "Confirm Check-In" to complete</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var scanner = new Html5QrcodeScanner("qr-reader", {
        fps: 10,
        qrbox: { width: 250, height: 250 },
        rememberLastUsedCamera: true
    });

    scanner.render(function onScanSuccess(decodedText) {
        scanner.clear();
        document.getElementById('qr-result').style.display = 'block';

        // Extract token from URL or use as-is
        var token = decodedText;
        var match = decodedText.match(/\/checkin\/verify\/([a-zA-Z0-9]+)/);
        if (match) token = match[1];

        window.location.href = '{{ url("checkins/verify") }}/' + token;
    });

    document.getElementById('manual-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var token = document.getElementById('manual-token').value.trim();
        if (token) {
            // Extract token from full URL if pasted
            var match = token.match(/\/checkin\/verify\/([a-zA-Z0-9]+)/);
            if (match) token = match[1];
            window.location.href = '{{ url("checkins/verify") }}/' + token;
        }
    });
});
</script>
@endpush
