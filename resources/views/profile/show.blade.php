@extends('layouts.admin')
@section('title', 'My Profile - Kopa Arena')

@push('styles')
<style>
.profile-card {
    background: linear-gradient(135deg, #1a2332 0%, #0d1b2a 100%);
    border: 1px solid rgba(45, 212, 137, 0.15);
    border-radius: 16px;
    overflow: hidden;
}
.profile-header {
    background: linear-gradient(135deg, rgba(45, 212, 137, 0.1) 0%, rgba(49, 104, 242, 0.1) 100%);
    padding: 40px 30px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 3px solid rgba(45, 212, 137, 0.4);
    object-fit: cover;
    background: #1f2940;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    overflow: hidden;
}
.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.profile-avatar .avatar-icon {
    font-size: 48px;
    color: #8e9ab5;
}
.profile-name {
    font-size: 1.5rem;
    font-weight: 600;
    color: #fff;
    margin-bottom: 4px;
}
.profile-email {
    color: #8e9ab5;
    font-size: 0.95rem;
}
.profile-body {
    padding: 30px;
}
.info-row {
    display: flex;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.info-row:last-child { border-bottom: none; }
.info-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 14px;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.info-icon.role { background: rgba(49, 104, 242, 0.15); color: #3168f2; }
.info-icon.branch { background: rgba(45, 212, 137, 0.15); color: #2dd489; }
.info-icon.status { background: rgba(240, 173, 78, 0.15); color: #f0ad4e; }
.info-icon.date { background: rgba(142, 154, 181, 0.15); color: #8e9ab5; }
.info-label {
    font-size: 0.8rem;
    color: #8e9ab5;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}
.info-value {
    color: #fff;
    font-size: 0.95rem;
    font-weight: 500;
}
.profile-actions {
    padding: 0 30px 30px;
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.btn-profile {
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 500;
    font-size: 0.9rem;
    border: none;
    transition: all 0.2s;
}
.btn-profile:hover { transform: translateY(-1px); }
.btn-profile-edit {
    background: linear-gradient(135deg, #2dd489, #1a8754);
    color: #fff;
}
.btn-profile-edit:hover { color: #fff; box-shadow: 0 4px 15px rgba(45, 212, 137, 0.3); }
.btn-profile-password {
    background: rgba(49, 104, 242, 0.15);
    color: #5b8af9;
    border: 1px solid rgba(49, 104, 242, 0.3);
}
.btn-profile-password:hover { color: #fff; background: rgba(49, 104, 242, 0.25); }
.btn-profile-delete {
    background: rgba(231, 74, 90, 0.1);
    color: #e74a5a;
    border: 1px solid rgba(231, 74, 90, 0.2);
}
.btn-profile-delete:hover { color: #fff; background: rgba(231, 74, 90, 0.2); }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">My Profile</h2>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-8 col-xl-6 mx-auto">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    @if($user->profile_image)
                        <img src="{{ asset($user->profile_image) }}" alt="{{ $user->name }}">
                    @else
                        <i class="fas fa-user avatar-icon"></i>
                    @endif
                </div>
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-email">{{ $user->email }}</div>
            </div>

            <div class="profile-body">
                <div class="info-row">
                    <div class="info-icon role"><i class="fas fa-shield-halved"></i></div>
                    <div>
                        <div class="info-label">Role</div>
                        <div class="info-value">
                            @php
                                $roleLabels = [
                                    'superadmin' => 'Super Admin',
                                    'hq_staff' => 'HQ Staff',
                                    'branch_manager' => 'Branch Manager',
                                    'branch_staff' => 'Branch Staff',
                                ];
                            @endphp
                            <span class="badge bg-primary">{{ $roleLabels[$user->role] ?? ucfirst($user->role) }}</span>
                        </div>
                    </div>
                </div>

                @if($user->branch)
                <div class="info-row">
                    <div class="info-icon branch"><i class="fas fa-building"></i></div>
                    <div>
                        <div class="info-label">Branch</div>
                        <div class="info-value">{{ $user->branch->name }}</div>
                    </div>
                </div>
                @endif

                <div class="info-row">
                    <div class="info-icon status"><i class="fas fa-circle-check"></i></div>
                    <div>
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon date"><i class="fas fa-calendar"></i></div>
                    <div>
                        <div class="info-label">Member Since</div>
                        <div class="info-value">{{ $user->created_at->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <a href="{{ route('profile.edit') }}" class="btn btn-profile btn-profile-edit">
                    <i class="fas fa-pen me-1"></i> Edit Profile
                </a>
                <a href="{{ route('profile.password') }}" class="btn btn-profile btn-profile-password">
                    <i class="fas fa-lock me-1"></i> Change Password
                </a>
                <button type="button" class="btn btn-profile btn-profile-delete" id="delete-account-btn">
                    <i class="fas fa-trash me-1"></i> Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-account-form" method="post" action="{{ route('profile.destroy') }}">
    @csrf
    @method('delete')
    <input type="hidden" name="password" id="delete_password_hidden">
</form>
@endsection

@push('scripts')
<script>
document.getElementById('delete-account-btn').addEventListener('click', function() {
    Swal.fire({
        title: 'Delete Account?',
        text: "This action cannot be undone. Please enter your password to confirm.",
        icon: 'warning',
        input: 'password',
        inputPlaceholder: 'Enter your password',
        inputAttributes: { autocomplete: 'current-password' },
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete my account!',
        inputValidator: (value) => {
            if (!value) return 'Password is required!';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_password_hidden').value = result.value;
            document.getElementById('delete-account-form').submit();
        }
    });
});
</script>
@endpush
