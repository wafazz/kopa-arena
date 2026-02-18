@extends('layouts.admin')
@section('title', 'Branches - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="overview-wrap">
            <h2 class="title-1">Branches</h2>
            <a href="{{ route('branches.create') }}" class="au-btn au-btn-icon au-btn--blue">
                <i class="zmdi zmdi-plus"></i>add branch</a>
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
                        <th>Phone</th>
                        <th>Facilities</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branches as $branch)
                    <tr>
                        <td>{{ $branch->id }}</td>
                        <td>{{ $branch->name }}</td>
                        <td>{{ $branch->phone ?? '-' }}</td>
                        <td>{{ $branch->facilities_count }}</td>
                        <td>
                            <span class="badge bg-{{ $branch->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($branch->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="d-inline" data-confirm-delete>
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
