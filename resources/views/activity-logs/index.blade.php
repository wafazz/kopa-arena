@extends('layouts.admin')
@section('title', 'Activity Logs - Kopa Arena')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2 class="title-1">Activity Logs</h2>
    </div>
</div>

<div class="row m-t-25">
    <div class="col-md-12">
        <div class="au-card m-b-30">
            <div class="au-card-inner">
                <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="action" class="form-select form-select-sm">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-responsive table--no-card m-b-40">
            <table class="table table-borderless table-striped table-earning">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Details</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->id }}</td>
                        <td>{{ $log->user->name ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</span></td>
                        <td>{{ $log->model ? class_basename($log->model) . ($log->model_id ? ' #' . $log->model_id : '') : '-' }}</td>
                        <td>{{ Str::limit($log->details, 60) }}</td>
                        <td>{{ $log->created_at->format('d M Y, g:i A') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No activity logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
