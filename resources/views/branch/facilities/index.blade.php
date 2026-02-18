@extends('layouts.admin')
@section('title', 'Facilities - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Facilities</h2>
            <a href="{{ route('branch.facilities.create') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-plus"></i>add facility</a>
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
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($facilities as $facility)
                    <tr>
                        <td>{{ $facility->id }}</td>
                        <td>{{ $facility->name }}</td>
                        <td>{{ str_replace('_', ' ', ucfirst($facility->type)) }}</td>
                        <td>
                            @php
                                $colors = ['active' => 'success', 'maintenance' => 'warning', 'closed' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $colors[$facility->status] ?? 'secondary' }}">
                                {{ ucfirst($facility->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('branch.facilities.edit', $facility) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('branch.facilities.destroy', $facility) }}" method="POST" class="d-inline" data-confirm-delete>
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
