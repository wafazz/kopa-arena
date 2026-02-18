<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $branchUserIds = User::where('branch_id', $branchId)->pluck('id');

        $query = ActivityLog::with('user')->whereIn('user_id', $branchUserIds);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest('created_at')->paginate(50)->withQueryString();
        $users = User::where('branch_id', $branchId)->orderBy('name')->get();
        $actions = ActivityLog::whereIn('user_id', $branchUserIds)->select('action')->distinct()->orderBy('action')->pluck('action');

        return view('branch.activity-logs.index', compact('logs', 'users', 'actions'));
    }
}
