@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom p-4 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h2 class="fw-bold mb-1">Welcome back, {{ $user->name }} 👋</h2>
                    <p class="text-muted mb-0">
                        Department: <span class="fw-semibold text-dark">{{ $user->department?->name ?? 'Unassigned' }}</span> |
                        Designation: <span class="fw-semibold text-dark">{{ $user->designation ?? 'Not specified' }}</span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('leave-requests.create') }}" class="btn btn-gradient">
                        <i class="bi bi-calendar-plus me-1"></i> Apply for Leave
                    </a>
                    <span class="badge badge-{{ $user->role }} fs-6 px-3 py-2 text-capitalize shadow-sm align-self-center">
                        <i class="bi bi-shield-lock me-1"></i> {{ $user->role }} Role
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    @if($user->isAdmin() || $user->isManager())
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white stat-card" style="border-left-color: #4f46e5;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Departments</div>
                        <div class="fs-3 fw-bold text-dark">{{ $stats['total_departments'] }}</div>
                    </div>
                    <div class="rounded-circle bg-indigo-subtle p-3 text-primary fs-3">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white stat-card" style="border-left-color: #10b981;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Employees</div>
                        <div class="fs-3 fw-bold text-dark">{{ $stats['total_employees'] }}</div>
                    </div>
                    <div class="rounded-circle bg-success-subtle p-3 text-success fs-3">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white stat-card" style="border-left-color: #f59e0b;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Pending Leaves</div>
                        <div class="fs-3 fw-bold text-dark">{{ $stats['pending_leaves'] }}</div>
                    </div>
                    <div class="rounded-circle bg-warning-subtle p-3 text-warning fs-3">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white stat-card" style="border-left-color: #3b82f6;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Approved Leaves</div>
                        <div class="fs-3 fw-bold text-dark">{{ $stats['approved_leaves'] }}</div>
                    </div>
                    <div class="rounded-circle bg-info-subtle p-3 text-info fs-3">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- My Leave Balances Cards -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <h6 class="fw-bold text-muted mb-1"><i class="bi bi-pie-chart text-primary me-1"></i> My Leave Balances ({{ date('Y') }})</h6>
    </div>
    @foreach($leaveBalances as $balance)
        <div class="col-md-4">
            <div class="card card-custom p-3 bg-white border-top border-4" style="border-top-color: #6366f1 !important;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark fs-6">{{ $balance['leave_type']->name }}</span>
                    <span class="badge bg-light text-dark border">{{ $balance['leave_type']->code }}</span>
                </div>
                <div class="row text-center g-2 pt-2 border-top">
                    <div class="col-4">
                        <div class="text-muted small">Allowed</div>
                        <div class="fw-bold fs-6 text-dark">{{ $balance['max_days'] }} d</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Used</div>
                        <div class="fw-bold fs-6 text-danger">{{ $balance['approved_used'] }} d</div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small">Remaining</div>
                        <div class="fw-bold fs-6 text-success">{{ $balance['remaining_balance'] }} d</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <!-- User Profile Overview -->
    <div class="col-md-5">
        <div class="card card-custom p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-card-heading text-primary me-2"></i> My Profile Overview</h5>
            <table class="table table-borderless table-sm fs-6">
                <tr>
                    <th class="text-muted" style="width: 35%;">Full Name:</th>
                    <td class="fw-semibold">{{ $user->name }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Email:</th>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Role:</th>
                    <td><span class="badge badge-{{ $user->role }} text-capitalize">{{ $user->role }}</span></td>
                </tr>
                <tr>
                    <th class="text-muted">Department:</th>
                    <td>{{ $user->department?->name ?? 'Unassigned' }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Designation:</th>
                    <td>{{ $user->designation ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Joined Date:</th>
                    <td>{{ $user->join_date ? $user->join_date->format('M d, Y') : 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Recent Leave Requests Table -->
    <div class="col-md-7">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-calendar-event text-primary me-2"></i> Recent Leave Requests</h5>
                <a href="{{ route('leave-requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Applicant</th>
                            <th>Type</th>
                            <th>Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLeaveRequests as $req)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $req->user->name }}</div>
                                    <div class="text-muted small">{{ $req->from_date->format('M d') }} - {{ $req->to_date->format('M d, Y') }}</div>
                                </td>
                                <td>{{ $req->leaveType->name }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $req->total_days }} d</span></td>
                                <td>
                                    @if($req->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($req->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($req->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No recent leave requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
