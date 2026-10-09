@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">Leave Requests & Management</h3>
        <p class="text-muted mb-0">Apply for leaves, track balances, and manage approvals</p>
    </div>
    <a href="{{ route('leave-requests.create') }}" class="btn btn-gradient">
        <i class="bi bi-calendar-plus me-1"></i> Apply for Leave
    </a>
</div>

<!-- Leave Balance Summary Cards for Current User -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <h6 class="fw-bold text-muted mb-2"><i class="bi bi-pie-chart text-primary me-1"></i> My Leave Balances ({{ date('Y') }})</h6>
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

<div class="card card-custom p-4">
    <!-- Filter Bar -->
    <form method="GET" action="{{ route('leave-requests.index') }}" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- All Statuses --</option>
                <option value="pending" {{ $statusFilter == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $statusFilter == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ $statusFilter == 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="cancelled" {{ $statusFilter == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="leave_type_id" class="form-select">
                <option value="">-- All Leave Types --</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}" {{ $typeFilter == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <div class="col-md-3">
                <select name="user_id" class="form-select">
                    <option value="">-- All Employees --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ $employeeFilter == $emp->id ? 'selected' : '' }}>
                            {{ $emp->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
            @if($statusFilter || $typeFilter || $departmentFilter || $employeeFilter)
                <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Dates</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th class="text-end" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveRequests as $req)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $req->user->name }}</div>
                            <div class="text-muted small">{{ $req->user->department?->name ?? 'Unassigned' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-semibold">
                                {{ $req->leaveType->name }}
                            </span>
                        </td>
                        <td>
                            <div class="small fw-semibold">{{ $req->from_date->format('M d, Y') }} - {{ $req->to_date->format('M d, Y') }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                                {{ $req->total_days }} {{ Str::plural('day', $req->total_days) }}
                            </span>
                        </td>
                        <td class="text-muted">
                            {{ Str::limit($req->reason, 35) }}
                        </td>
                        <td>
                            @if($req->status === 'pending')
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                            @elseif($req->status === 'approved')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Approved</span>
                            @elseif($req->status === 'rejected')
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i> Cancelled</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('leave-requests.show', $req) }}" class="btn btn-sm btn-outline-info me-1" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>

                            @if($req->isPending() && auth()->id() === $req->user_id)
                                <form action="{{ route('leave-requests.cancel', $req) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Cancel Request">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i> No leave requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $leaveRequests->links() }}
    </div>
</div>
@endsection
