<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard based on user role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'total_departments' => Department::count(),
            'total_employees' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_managers' => User::where('role', 'manager')->count(),
            'total_staff' => User::where('role', 'employee')->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'approved_leaves' => LeaveRequest::where('status', 'approved')->count(),
        ];

        $leaveBalances = $user->getLeaveBalances();

        $recentLeaveRequests = LeaveRequest::with(['user', 'leaveType'])
            ->when(! $user->isAdmin() && ! $user->isManager(), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->take(5)
            ->get();

        $recentEmployees = User::with('department')->latest()->take(5)->get();

        return view('dashboard', compact('user', 'stats', 'leaveBalances', 'recentLeaveRequests', 'recentEmployees'));
    }
}
