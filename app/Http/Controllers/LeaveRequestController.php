<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApproveRejectLeaveRequest;
use App\Http\Requests\StoreLeaveRequest;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests with role-based scoping and filters.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $statusFilter = $request->query('status');
        $typeFilter = $request->query('leave_type_id');
        $departmentFilter = $request->query('department_id');
        $employeeFilter = $request->query('user_id');

        $query = LeaveRequest::with(['user.department', 'leaveType', 'approver']);

        // Scope query based on user role
        if (! $user->isAdmin() && ! $user->isManager()) {
            $query->where('user_id', $user->id);
        } else if ($user->isManager() && ! $user->isAdmin()) {
            // Manager can see requests from employees in their department or own requests
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', function ($uq) use ($user) {
                      $uq->where('department_id', $user->department_id);
                  });
            });
        }

        // Apply filters
        $leaveRequests = $query
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($typeFilter, fn($q) => $q->where('leave_type_id', $typeFilter))
            ->when($departmentFilter, fn($q) => $q->whereHas('user', fn($uq) => $uq->where('department_id', $departmentFilter)))
            ->when($employeeFilter, fn($q) => $q->where('user_id', $employeeFilter))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $leaveBalances = $user->getLeaveBalances();
        $leaveTypes = LeaveType::all();
        $departments = Department::orderBy('name')->get();
        $employees = User::orderBy('name')->get();

        return view('leave_requests.index', compact(
            'leaveRequests',
            'leaveBalances',
            'leaveTypes',
            'departments',
            'employees',
            'statusFilter',
            'typeFilter',
            'departmentFilter',
            'employeeFilter'
        ));
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $leaveTypes = LeaveType::all();
        $leaveBalances = $user->getLeaveBalances();

        return view('leave_requests.create', compact('leaveTypes', 'leaveBalances'));
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $user = $request->user();
        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);
        $totalDays = $fromDate->diffInDays($toDate) + 1;

        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request submitted successfully! Pending approval.');
    }

    /**
     * Display the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest): View
    {
        $user = auth()->user();

        // Authorization check
        if (! $user->isAdmin() && ! $user->isManager() && $user->id !== $leaveRequest->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $leaveRequest->load(['user.department', 'leaveType', 'approver']);

        return view('leave_requests.show', compact('leaveRequest'));
    }

    /**
     * Approve or reject a leave request (Manager or Admin).
     */
    public function approveReject(ApproveRejectLeaveRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'This leave request has already been processed.');
        }

        $leaveRequest->update([
            'status' => $request->status,
            'action_by' => auth()->id(),
            'admin_comment' => $request->admin_comment,
            'action_at' => now(),
        ]);

        $statusLabel = ucfirst($request->status);
        return back()->with('success', "Leave request has been {$statusLabel} successfully.");
    }

    /**
     * Cancel a pending leave request (Employee).
     */
    public function cancel(LeaveRequest $leaveRequest): RedirectResponse
    {
        $user = auth()->user();

        if ($leaveRequest->user_id !== $user->id) {
            abort(403, 'You can only cancel your own leave requests.');
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be cancelled.');
        }

        $leaveRequest->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Leave request cancelled successfully.');
    }
}
