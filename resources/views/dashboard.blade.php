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
                <div>
                    <span class="badge badge-{{ $user->role }} fs-6 px-3 py-2 text-capitalize shadow-sm">
                        <i class="bi bi-shield-lock me-1"></i> {{ $user->role }} Role
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Row -->
@if($user->isAdmin() || $user->isManager())
<div class="row g-3 mb-4">
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
        <div class="card card-custom p-3 bg-white stat-card" style="border-left-color: #3b82f6;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Managers</div>
                    <div class="fs-3 fw-bold text-dark">{{ $stats['total_managers'] }}</div>
                </div>
                <div class="rounded-circle bg-info-subtle p-3 text-info fs-3">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom p-3 bg-white stat-card" style="border-left-color: #f59e0b;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Staff Employees</div>
                    <div class="fs-3 fw-bold text-dark">{{ $stats['total_staff'] }}</div>
                </div>
                <div class="rounded-circle bg-warning-subtle p-3 text-warning fs-3">
                    <i class="bi bi-person-workspace"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row g-4">
    <!-- User Information Card -->
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
                    <th class="text-muted">Phone:</th>
                    <td>{{ $user->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="text-muted">Joined Date:</th>
                    <td>{{ $user->join_date ? $user->join_date->format('M d, Y') : 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Quick Navigation / Recent Staff -->
    <div class="col-md-7">
        <div class="card card-custom p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-lightning-charge text-warning me-2"></i> Quick Actions & Directory</h5>
            </div>

            @if($user->isAdmin())
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <a href="{{ route('departments.create') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-plus-circle me-1"></i> Add Department
                    </a>
                    <a href="{{ route('employees.create') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        <i class="bi bi-person-plus me-1"></i> Add Employee
                    </a>
                    <a href="{{ route('departments.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-building me-1"></i> Manage Departments
                    </a>
                    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-people me-1"></i> Manage Employees
                    </a>
                </div>
            @endif

            <h6 class="fw-bold text-muted mb-2">Recently Joined Staff</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentEmployees as $emp)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $emp->name }}</div>
                                    <div class="text-muted small">{{ $emp->email }}</div>
                                </td>
                                <td>{{ $emp->department?->name ?? 'Unassigned' }}</td>
                                <td><span class="badge badge-{{ $emp->role }} text-capitalize">{{ $emp->role }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
