<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        $leaveTypes = LeaveType::withCount('leaveRequests')->orderBy('name')->get();
        return view('leave_types.index', compact('leaveTypes'));
    }

    public function create(): View
    {
        return view('leave_types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:leave_types,code'],
            'max_days_per_year' => ['required', 'integer', 'min:1', 'max:365'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        LeaveType::create($validated);

        return redirect()->route('leave-types.index')->with('success', 'Leave type created successfully!');
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('leave_types.edit', compact('leaveType'));
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('leave_types', 'code')->ignore($leaveType->id)],
            'max_days_per_year' => ['required', 'integer', 'min:1', 'max:365'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $leaveType->update($validated);

        return redirect()->route('leave-types.index')->with('success', 'Leave type updated successfully!');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        if ($leaveType->leaveRequests()->count() > 0) {
            return back()->with('error', 'Cannot delete leave type that has existing leave requests.');
        }

        $leaveType->delete();

        return redirect()->route('leave-types.index')->with('success', 'Leave type deleted successfully!');
    }
}
