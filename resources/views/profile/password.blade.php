@extends('layouts.admin')
@section('title', 'Change Password - Kopa Arena')

@push('styles')
<style>
.password-card {
    background: linear-gradient(135deg, #1a2332 0%, #0d1b2a 100%);
    border: 1px solid rgba(45, 212, 137, 0.15);
    border-radius: 16px;
    padding: 30px;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Change Password</h2>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="password-card">
            <p class="text-muted mb-4">Ensure your account is using a long, random password to stay secure.</p>

            <form method="post" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="update_password_current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" id="update_password_current_password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="update_password_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" id="update_password_password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="update_password_password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" id="update_password_password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="au-btn au-btn--green">Update Password</button>
                    <a href="{{ route('profile.show') }}" class="btn btn-secondary">Back</a>
                </div>

                @if (session('status') === 'password-updated')
                    <div class="alert alert-success mt-3 mb-0">Password updated successfully.</div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
