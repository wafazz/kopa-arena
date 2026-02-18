@extends('layouts.admin')
@section('title', 'Pricing Rules - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Pricing Rules</h2>
            <a href="{{ route('pricing-rules.create') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-plus"></i>new rule</a>
        </div>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-md-12">
        <div class="table-responsive table--no-card m-b-40">
            <table class="table table-borderless table-striped table-earning" data-datatable>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Day</th>
                        <th>Normal Price</th>
                        <th>Peak Hours</th>
                        <th>Peak Price</th>
                        <th>Branches</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    @endphp
                    @foreach($pricingRules as $rule)
                    <tr>
                        <td>{{ $rule->id }}</td>
                        <td>{{ $rule->name }}</td>
                        <td>{{ $rule->day_of_week !== null ? $days[$rule->day_of_week] : 'All Days' }}</td>
                        <td>RM {{ number_format($rule->normal_price, 2) }}</td>
                        <td>
                            @if($rule->peak_start && $rule->peak_end)
                                {{ \Carbon\Carbon::parse($rule->peak_start)->format('h:i A') }} - {{ \Carbon\Carbon::parse($rule->peak_end)->format('h:i A') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($rule->peak_price)
                                RM {{ number_format($rule->peak_price, 2) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @foreach($rule->branches as $branch)
                                <span class="badge bg-info">{{ $branch->name }}</span>
                            @endforeach
                            @if($rule->branches->isEmpty())
                                <span class="text-muted">None</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pricing-rules.edit', $rule) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('pricing-rules.destroy', $rule) }}" method="POST" class="d-inline" data-confirm-delete>
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
