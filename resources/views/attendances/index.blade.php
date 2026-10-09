@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">Attendance Management</h3>
        <p class="text-muted mb-0">Daily check-in, check-out, and employee attendance logs</p>
    </div>
</div>

<!-- Daily Check-In / Check-Out Widget -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom p-4 bg-white border-start border-4 border-primary">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle p-3 text-primary fs-2">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Today's Attendance Status ({{ date('D, M d, Y') }})</h5>
                        <p class="text-muted mb-0">
                            Cutoff Time for Late Arrival: <span class="badge bg-warning text-dark">{{ config('hrm.late_cutoff', '09:30') }} AM</span>
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    @if(! $todayAttendance)
                        <!-- Check In Form -->
                        <form action="{{ route('attendances.check-in') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-gradient btn-lg px-4 shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Check In Now
                            </button>
                        </form>
                    @elseif(! $todayAttendance->check_out)
                        <!-- Check Out Form -->
                        <div class="text-end me-2">
                            <span class="d-block small text-muted">Checked in at:</span>
                            <span class="fw-bold text-success">{{ $todayAttendance->check_in->format('h:i A') }}</span>
                            @if($todayAttendance->isLate())
                                <span class="badge bg-danger ms-1">Late</span>
                            @endif
                        </div>
                        <form action="{{ route('attendances.check-out') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-lg px-4 shadow-sm">
                                <i class="bi bi-box-arrow-right me-1"></i> Check Out Now
                            </button>
                        </form>
                    @else
                        <!-- Completed Attendance -->
                        <div class="text-end">
                            <span class="badge bg-success-subtle text-success border border-success fs-6 px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle-fill me-1"></i> Attendance Completed Today
                            </span>
                            <div class="small text-muted mt-1">
                                In: {{ $todayAttendance->check_in->format('h:i A') }} | Out: {{ $todayAttendance->check_out->format('h:i A') }} ({{ $todayAttendance->work_duration }})
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom p-4">
    <!-- Filters for Admin/Manager -->
    @if(auth()->user()->isAdmin() || auth()->user()->isManager())
        <form method="GET" action="{{ route('attendances.index') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Select Date</label>
                <input type="date" name="date" class="form-control" value="{{ $dateFilter }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">-- All Departments --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentFilter == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Employee</label>
                <select name="user_id" class="form-select">
                    <option value="">-- All Employees --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeFilter == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    @endif

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $att)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $att->user->name }}</div>
                            <div class="text-muted small">{{ $att->user->department?->name ?? 'Unassigned' }}</div>
                        </td>
                        <td class="fw-semibold">{{ $att->date->format('M d, Y') }}</td>
                        <td>
                            @if($att->check_in)
                                <span class="text-dark fw-semibold"><i class="bi bi-box-arrow-in-right text-success me-1"></i> {{ $att->check_in->format('h:i A') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($att->check_out)
                                <span class="text-dark fw-semibold"><i class="bi bi-box-arrow-right text-danger me-1"></i> {{ $att->check_out->format('h:i A') }}</span>
                            @else
                                <span class="badge bg-light text-muted border">Active Session</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary font-monospace">{{ $att->work_duration ?? 'In Progress' }}</span>
                        </td>
                        <td>
                            @if($att->status === 'present')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Present</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i> Late</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $att->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-clock-history fs-2 d-block mb-2"></i> No attendance logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $attendances->links() }}
    </div>
</div>
@endsection
