<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display attendance records.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $dateFilter = $request->query('date', Carbon::today()->format('Y-m-d'));
        $departmentFilter = $request->query('department_id');
        $employeeFilter = $request->query('user_id');

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        $query = Attendance::with(['user.department']);

        if (! $user->isAdmin() && ! $user->isManager()) {
            // Employee sees only their own history
            $query->where('user_id', $user->id);
        } else {
            // Admin/Manager can filter by date, department, employee
            $query->when($dateFilter, fn($q) => $q->whereDate('date', $dateFilter))
                  ->when($departmentFilter, fn($q) => $q->whereHas('user', fn($uq) => $uq->where('department_id', $departmentFilter)))
                  ->when($employeeFilter, fn($q) => $q->where('user_id', $employeeFilter));
        }

        $attendances = $query->latest('date')->latest('check_in')->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $employees = User::orderBy('name')->get();

        return view('attendances.index', compact(
            'attendances',
            'todayAttendance',
            'departments',
            'employees',
            'dateFilter',
            'departmentFilter',
            'employeeFilter'
        ));
    }

    /**
     * Handle employee daily Check-In.
     */
    public function checkIn(Request $request): RedirectResponse
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        // Check if employee already checked in today using whereDate
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if ($existing) {
            $formattedTime = $existing->check_in ? $existing->check_in->format('h:i A') : '';
            return back()->with('error', "You have already checked in today at {$formattedTime}.");
        }

        $now = Carbon::now();
        $lateCutoff = config('hrm.late_cutoff', '09:30');
        $currentTime = $now->format('H:i');

        $status = ($currentTime > $lateCutoff) ? 'late' : 'present';

        try {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => $now,
                'status' => $status,
                'notes' => $request->input('notes'),
            ]);
        } catch (UniqueConstraintViolationException | QueryException $e) {
            return back()->with('error', 'You have already checked in today.');
        }

        $statusMsg = $status === 'late' ? ' (Marked as Late)' : ' (On Time)';
        return back()->with('success', "Checked in successfully at {$now->format('h:i A')}{$statusMsg}.");
    }

    /**
     * Handle employee daily Check-Out.
     */
    public function checkOut(Request $request): RedirectResponse
    {
        $user = $request->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if (! $attendance) {
            return back()->with('error', 'You must check-in first before checking out.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'You have already checked out today at ' . $attendance->check_out->format('h:i A'));
        }

        $now = Carbon::now();
        $attendance->update([
            'check_out' => $now,
        ]);

        return back()->with('success', 'Checked out successfully at ' . $now->format('h:i A') . '. Have a great evening!');
    }
}
