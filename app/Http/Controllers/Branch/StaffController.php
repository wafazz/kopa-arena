<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    private function branchId()
    {
        return auth()->user()->branch_id;
    }

    public function index()
    {
        $staff = User::where('branch_id', $this->branchId())
            ->where('role', 'branch_staff')
            ->latest()
            ->get();
        return view('branch.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('branch.staff.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'branch_staff',
            'branch_id' => $this->branchId(),
            'is_active' => $request->has('is_active'),
            'email_verified_at' => now(),
        ]);

        ActivityLog::log('store', 'User', $user->id, $user->name);
        return redirect()->route('branch.staff.index')->with('success', 'Staff created successfully.');
    }

    public function edit(User $staff)
    {
        if ($staff->branch_id !== $this->branchId() || $staff->role !== 'branch_staff') {
            abort(403);
        }
        return view('branch.staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        if ($staff->branch_id !== $this->branchId() || $staff->role !== 'branch_staff') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'password' => 'nullable|string|min:6|confirmed',
            'is_active' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $staff->update($data);

        ActivityLog::log('update', 'User', $staff->id, $staff->name);
        return redirect()->route('branch.staff.index')->with('success', 'Staff updated successfully.');
    }

    public function destroy(User $staff)
    {
        if ($staff->branch_id !== $this->branchId() || $staff->role !== 'branch_staff') {
            abort(403);
        }

        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        ActivityLog::log('destroy', 'User', $staff->id, $staff->name);
        $staff->delete();
        return redirect()->route('branch.staff.index')->with('success', 'Staff deleted successfully.');
    }
}
