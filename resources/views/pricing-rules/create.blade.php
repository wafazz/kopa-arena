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
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Weekend Peak" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Day of Week</label>
                            <select name="day_of_week" class="form-select">
                                <option value="">All Days</option>
                                <option value="0" {{ old('day_of_week') === '0' ? 'selected' : '' }}>Sunday</option>
                                <option value="1" {{ old('day_of_week') === '1' ? 'selected' : '' }}>Monday</option>
                                <option value="2" {{ old('day_of_week') === '2' ? 'selected' : '' }}>Tuesday</option>
                                <option value="3" {{ old('day_of_week') === '3' ? 'selected' : '' }}>Wednesday</option>
                                <option value="4" {{ old('day_of_week') === '4' ? 'selected' : '' }}>Thursday</option>
                                <option value="5" {{ old('day_of_week') === '5' ? 'selected' : '' }}>Friday</option>
                                <option value="6" {{ old('day_of_week') === '6' ? 'selected' : '' }}>Saturday</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Normal Price (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="normal_price" class="form-control" step="0.01" min="0" value="{{ old('normal_price') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peak Price (RM)</label>
                            <input type="number" name="peak_price" class="form-control" step="0.01" min="0" value="{{ old('peak_price') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peak Start Time</label>
                            <input type="time" name="peak_start" class="form-control" value="{{ old('peak_start') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peak End Time</label>
                            <input type="time" name="peak_end" class="form-control" value="{{ old('peak_end') }}">
                        </div>
                    </div>

                    <hr>
                    <h3 class="title-2 m-b-25">Assign to Branches</h3>
                    <div class="row">
                        @foreach($branches as $branch)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="branches[]" value="{{ $branch->id }}" id="branch_{{ $branch->id }}"
                                    {{ in_array($branch->id, old('branches', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="branch_{{ $branch->id }}">{{ $branch->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="submit" class="au-btn au-btn--green mt-3">Create Rule</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
