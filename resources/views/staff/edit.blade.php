@extends('layouts.admin')
@section('title', 'Edit Staff - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Edit Staff</h2>
            <a href="{{ route('staff.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
        <form action="{{ route('staff.update', $staff) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $staff->name) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        <option value="hq_staff" {{ old('role', $staff->role) === 'hq_staff' ? 'selected' : '' }}>HQ Staff</option>
                        <option value="branch_manager" {{ old('role', $staff->role) === 'branch_manager' ? 'selected' : '' }}>Branch Manager</option>
                        <option value="branch_staff" {{ old('role', $staff->role) === 'branch_staff' ? 'selected' : '' }}>Branch Staff</option>
                        <option value="superadmin" {{ old('role', $staff->role) === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">None (HQ)</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $staff->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if(auth()->user()->isSuperAdmin())
            @php $staffPerms = old('permissions', $staff->permissions ?? []); @endphp
            <div class="mb-3" id="permissions-section" style="display:none;">
                <label class="form-label">Permissions</label>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_branches" class="form-check-input hq-perm" id="perm_branches" {{ in_array('manage_branches', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_branches">Manage Branches</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_facilities" class="form-check-input" id="perm_facilities" {{ in_array('manage_facilities', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_facilities">Manage Facilities</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_pricing_rules" class="form-check-input hq-perm" id="perm_pricing" {{ in_array('manage_pricing_rules', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_pricing">Manage Pricing Rules</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_bookings" class="form-check-input" id="perm_bookings" {{ in_array('manage_bookings', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_bookings">Manage Bookings</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_staff" class="form-check-input" id="perm_staff" {{ in_array('manage_staff', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_staff">Manage Staff</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="view_reports" class="form-check-input" id="perm_reports" {{ in_array('view_reports', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_reports">View Reports</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_close_sales" class="form-check-input" id="perm_close_sales" {{ in_array('manage_close_sales', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_close_sales">Manage Close Sales</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_products" class="form-check-input" id="perm_products" {{ in_array('manage_products', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_products">Manage Products</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_orders" class="form-check-input" id="perm_orders" {{ in_array('manage_orders', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_orders">Manage Orders</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="manage_checkins" class="form-check-input" id="perm_checkins" {{ in_array('manage_checkins', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_checkins">Manage Check-Ins</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <div class="form-check">
                            <input type="checkbox" name="permissions[]" value="view_activity_logs" class="form-check-input" id="perm_activity_logs" {{ in_array('view_activity_logs', $staffPerms) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_activity_logs">View Activity Logs</label>
                        </div>
                    </div>
                </div>
                <small class="text-muted">Super Admin & Branch Manager have full access by default.</small>
            </div>
            @endif
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
            <button type="submit" class="au-btn au-btn--green">Update Staff</button>
        </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var roleSelect = document.querySelector('select[name="role"]');
    var permSection = document.getElementById('permissions-section');
    var hqPerms = document.querySelectorAll('.hq-perm');

    function togglePerms() {
        var role = roleSelect.value;
        if (role === 'superadmin' || role === 'branch_manager') {
            permSection.style.display = 'none';
        } else {
            permSection.style.display = 'block';
        }
        var showHq = (role === 'hq_staff');
        hqPerms.forEach(function(el) {
            el.closest('.col-md-4').style.display = showHq ? 'block' : 'none';
            if (!showHq) el.checked = false;
        });
    }

    roleSelect.addEventListener('change', togglePerms);
    togglePerms();
});
</script>
@endpush
