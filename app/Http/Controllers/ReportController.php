<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * View Monthly Attendance Report per employee.
     */
    public function monthlyAttendance(Request $request): View
    {
        $user = $request->user();
        $month = (int) $request->query('month', date('n'));
        $year = (int) $request->query('year', date('Y'));
        $departmentId = $request->query('department_id');
        $employeeFilter = $request->query('user_id');

        // If simple employee, scope to self
        if (! $user->isAdmin() && ! $user->isManager()) {
            $employeeFilter = $user->id;
        }

        $reportData = $this->getMonthlyReportData($month, $year, $departmentId, $employeeFilter);
        $departments = Department::orderBy('name')->get();
        $employees = User::orderBy('name')->get();

        return view('reports.monthly_attendance', compact(
            'reportData',
            'month',
            'year',
            'departmentId',
            'employeeFilter',
            'departments',
            'employees'
        ));
    }

    /**
     * CSV Export of Monthly Attendance Report.
     */
    public function exportMonthlyAttendanceCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $month = (int) $request->query('month', date('n'));
        $year = (int) $request->query('year', date('Y'));
        $departmentId = $request->query('department_id');
        $employeeFilter = $request->query('user_id');

        if (! $user->isAdmin() && ! $user->isManager()) {
            $employeeFilter = $user->id;
        }

        $reportData = $this->getMonthlyReportData($month, $year, $departmentId, $employeeFilter);
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
        $filename = "monthly_attendance_report_{$monthName}_{$year}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($reportData, $monthName, $year) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Employee Name',
                'Email',
                'Department',
                'Designation',
                'Month & Year',
                'Total Working Days',
                'Present Days',
                'Late Days',
                'Approved Leave Days',
                'Absent Days',
            ]);

            foreach ($reportData as $row) {
                fputcsv($file, [
                    $row['employee']->name,
                    $row['employee']->email,
                    $row['employee']->department?->name ?? 'Unassigned',
                    $row['employee']->designation ?? 'N/A',
                    "{$monthName} {$year}",
                    $row['total_working_days'],
                    $row['present_days'],
                    $row['late_days'],
                    $row['leave_days'],
                    $row['absent_days'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper to compute attendance & leave summary per employee for a month.
     */
    protected function getMonthlyReportData(int $month, int $year, ?int $departmentId = null, ?int $userId = null): array
    {
        $query = User::with('department');

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        if ($userId) {
            $query->where('id', $userId);
        }

        $employees = $query->orderBy('name')->get();

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        if ($startDate->isCurrentMonth()) {
            $endDate = Carbon::now();
        }

        // Count working business days (Mon-Fri)
        $totalWorkingDays = 0;
        if ($startDate->lte($endDate)) {
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                if (! $date->isWeekend()) {
                    $totalWorkingDays++;
                }
            }
        }

        $report = [];

        foreach ($employees as $employee) {
            $presentDays = Attendance::where('user_id', $employee->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'present')
                ->count();

            $lateDays = Attendance::where('user_id', $employee->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'late')
                ->count();

            // Approved Leave Days calculation
            $approvedLeaves = LeaveRequest::where('user_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('from_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                      ->orWhereBetween('to_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                })
                ->get();

            $leaveDays = 0;
            foreach ($approvedLeaves as $leave) {
                $leaveFrom = Carbon::parse($leave->from_date)->max($startDate);
                $leaveTo = Carbon::parse($leave->to_date)->min($endDate);

                if ($leaveFrom->lte($leaveTo)) {
                    $leavePeriod = CarbonPeriod::create($leaveFrom, $leaveTo);
                    foreach ($leavePeriod as $ld) {
                        if (! $ld->isWeekend()) {
                            $leaveDays++;
                        }
                    }
                }
            }

            $attendedOrLeave = $presentDays + $lateDays + $leaveDays;
            $absentDays = max(0, $totalWorkingDays - $attendedOrLeave);

            $report[] = [
                'employee' => $employee,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'leave_days' => $leaveDays,
                'absent_days' => $absentDays,
                'total_working_days' => $totalWorkingDays,
            ];
        }

        return $report;
    }
}
