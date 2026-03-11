@extends('layouts.admin')
@section('title', 'Edit Facility - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Edit Facility</h2>
            <a href="{{ route('facilities.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <form action="{{ route('facilities.update', $facility) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id', $facility->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $facility->name) }}" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="football_field" {{ $facility->type === 'football_field' ? 'selected' : '' }}>Football Field</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ old('status', $facility->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="maintenance" {{ old('status', $facility->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="closed" {{ old('status', $facility->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h3 class="title-2 m-b-25">Operating Hours</h3>
                    @php $rule = $facility->slotTimeRule; @endphp
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Open From <span class="text-danger">*</span></label>
                            <input type="time" name="earliest_start" class="form-control" value="{{ old('earliest_start', substr($rule->earliest_start ?? '08:00', 0, 5)) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Open Until <span class="text-danger">*</span></label>
                            <input type="time" name="latest_start" class="form-control" value="{{ old('latest_start', substr($rule->latest_start ?? '22:00', 0, 5)) }}" required>
                            <small class="text-muted">Last available slot start time.</small>
                        </div>
                    </div>

                    <hr>
                    <h3 class="title-2 m-b-25">Pricing</h3>
                    @php $pricing = $facility->pricings->first(); @endphp
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Normal Price (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="normal_price" class="form-control" step="0.01" value="{{ old('normal_price', $pricing->normal_price ?? 0) }}" required>
                            <small class="text-muted">Peak hour pricing can be configured in Pricing Rules.</small>
                        </div>
                    </div>

                    <button type="submit" class="au-btn au-btn--green">Update Facility</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
