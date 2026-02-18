@extends('layouts.admin')
@section('title', 'Edit Pricing Rule - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Edit Pricing Rule</h2>
            <a href="{{ route('pricing-rules.index') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-arrow-left"></i>back</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-lg-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <form action="{{ route('pricing-rules.update', $pricingRule) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rule Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $pricingRule->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Day of Week</label>
                            <select name="day_of_week" class="form-select">
                                <option value="">All Days</option>
                                @for($d = 0; $d <= 6; $d++)
                                @php $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; @endphp
                                <option value="{{ $d }}" {{ old('day_of_week', $pricingRule->day_of_week) !== null && (int)old('day_of_week', $pricingRule->day_of_week) === $d ? 'selected' : '' }}>{{ $dayNames[$d] }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Normal Price (RM) <span class="text-danger">*</span></label>
                            <input type="number" name="normal_price" class="form-control" step="0.01" min="0" value="{{ old('normal_price', $pricingRule->normal_price) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peak Price (RM)</label>
                            <input type="number" name="peak_price" class="form-control" step="0.01" min="0" value="{{ old('peak_price', $pricingRule->peak_price) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peak Start Time</label>
                            <input type="time" name="peak_start" class="form-control" value="{{ old('peak_start', $pricingRule->peak_start ? substr($pricingRule->peak_start, 0, 5) : '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Peak End Time</label>
                            <input type="time" name="peak_end" class="form-control" value="{{ old('peak_end', $pricingRule->peak_end ? substr($pricingRule->peak_end, 0, 5) : '') }}">
                        </div>
                    </div>

                    <hr>
                    <h3 class="title-2 m-b-25">Assign to Branches</h3>
                    <div class="row">
                        @foreach($branches as $branch)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="branches[]" value="{{ $branch->id }}" id="branch_{{ $branch->id }}"
                                    {{ in_array($branch->id, old('branches', $assignedBranches)) ? 'checked' : '' }}>
                                <label class="form-check-label" for="branch_{{ $branch->id }}">{{ $branch->name }}</label>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="submit" class="au-btn au-btn--green mt-3">Update Rule</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
