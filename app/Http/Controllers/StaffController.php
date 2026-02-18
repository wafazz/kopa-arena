<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::with('branch')->latest()->get();
        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        return view('staff.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:superadmin,hq_staff,branch_manager,branch_staff',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'branch_id' => $request->branch_id,
            'is_active' => $request->has('is_active'),
            'email_verified_at' => now(),
        ];

        if (auth()->user()->isSuperAdmin()) {
            $data['permissions'] = in_array($request->role, ['superadmin', 'branch_manager'])
                ? null
                : ($request->permissions ?? []);
        }

        $user = User::create($data);

        ActivityLog::log('store', 'User', $user->id, $user->name);
        return redirect()->route('staff.index')->with('success', 'Staff created successfully.');
    }

    public function show(User $staff)
    {
        return redirect()->route('staff.edit', $staff);
    }

    public function edit(User $staff)
    {
        $branches = Branch::where('status', 'active')->get();
        return view('staff.edit', compact('staff', 'branches'));
    }

    public function update(Request $request, User $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:superadmin,hq_staff,branch_manager,branch_staff',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'branch_id' => $request->branch_id,
            'is_active' => $request->has('is_active'),
        ];

        if (auth()->user()->isSuperAdmin()) {
            $data['permissions'] = in_array($request->role, ['superadmin', 'branch_manager'])
                ? null
                : ($request->permissions ?? []);
        }

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $staff->update($data);

        ActivityLog::log('update', 'User', $staff->id, $staff->name);
        return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
    }

    public function destroy(User $staff)
    {
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        ActivityLog::log('destroy', 'User', $staff->id, $staff->name);
        $staff->delete();
        return redirect()->route('staff.index')->with('success', 'Staff deleted successfully.');
    }
}
