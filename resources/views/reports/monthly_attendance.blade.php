@extends('layouts.app')

@section('title', 'Monthly Attendance Report')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">Monthly Attendance Report</h3>
        <p class="text-muted mb-0">Monthly summary of present, late, leave, and absent days per employee</p>
    </div>
    <div>
        <a href="{{ route('reports.monthly-attendance.export', request()->query()) }}" class="btn btn-gradient">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export to CSV
        </a>
    </div>
</div>

<!-- Filters Form -->
<div class="card card-custom p-4 mb-4">
    <form method="GET" action="{{ route('reports.monthly-attendance') }}" class="row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted mb-1">Select Month</label>
            <select name="month" class="form-select">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Select Year</label>
            <select name="year" class="form-select">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">-- All Departments --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Employee</label>
                <select name="user_id" class="form-select">
                    <option value="">-- All Staff --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeFilter == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary w-100">Generate Report</button>
        </div>
    </form>
</div>

<!-- Summary Cards -->
@php
    $sumPresent = collect($reportData)->sum('present_days');
    $sumLate = collect($reportData)->sum('late_days');
    $sumLeave = collect($reportData)->sum('leave_days');
    $sumAbsent = collect($reportData)->sum('absent_days');
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-custom p-3 bg-white border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Present Days</div>
                    <div class="fs-3 fw-bold text-success">{{ $sumPresent }}</div>
                </div>
                <div class="rounded-circle bg-success-subtle p-3 text-success fs-3">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Late Arrivals</div>
                    <div class="fs-3 fw-bold text-warning">{{ $sumLate }}</div>
                </div>
                <div class="rounded-circle bg-warning-subtle p-3 text-warning fs-3">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-3 bg-white border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Approved Leaves</div>
                    <div class="fs-3 fw-bold text-info">{{ $sumLeave }}</div>
                </div>
                <div class="rounded-circle bg-info-subtle p-3 text-info fs-3">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom p-3 bg-white border-start border-4 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Absent Days</div>
                    <div class="fs-3 fw-bold text-danger">{{ $sumAbsent }}</div>
                </div>
                <div class="rounded-circle bg-danger-subtle p-3 text-danger fs-3">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table -->
<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th class="text-center">Working Days</th>
                    <th class="text-center">Present Days</th>
                    <th class="text-center">Late Days</th>
                    <th class="text-center">Approved Leave</th>
                    <th class="text-center">Absent Days</th>
                    <th class="text-center">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    @php
                        $effectiveAttended = $row['present_days'] + $row['late_days'] + $row['leave_days'];
                        $attendancePct = $row['total_working_days'] > 0 ? round(($effectiveAttended / $row['total_working_days']) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $row['employee']->name }}</div>
                            <div class="text-muted small">{{ $row['employee']->email }}</div>
                        </td>
                        <td>
                            @if($row['employee']->department)
                                <span class="badge bg-light text-dark border">{{ $row['employee']->department->name }}</span>
                            @else
                                <span class="text-muted small">Unassigned</span>
                            @endif
                        </td>
                        <td class="text-center fw-semibold">{{ $row['total_working_days'] }}</td>
                        <td class="text-center"><span class="badge bg-success-subtle text-success border px-2.5 py-1 fs-6">{{ $row['present_days'] }}</span></td>
                        <td class="text-center"><span class="badge bg-warning-subtle text-warning border px-2.5 py-1 fs-6">{{ $row['late_days'] }}</span></td>
                        <td class="text-center"><span class="badge bg-info-subtle text-info border px-2.5 py-1 fs-6">{{ $row['leave_days'] }}</span></td>
                        <td class="text-center"><span class="badge bg-danger-subtle text-danger border px-2.5 py-1 fs-6">{{ $row['absent_days'] }}</span></td>
                        <td class="text-center">
                            <span class="fw-bold {{ $attendancePct >= 85 ? 'text-success' : 'text-danger' }}">
                                {{ $attendancePct }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No attendance report data found for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
