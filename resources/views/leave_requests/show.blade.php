@extends('layouts.app')

@section('title', 'Leave Request Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Leave Requests
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom p-4 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold mb-1">Leave Request #{{ $leaveRequest->id }}</h4>
                    <span class="text-muted">Submitted on {{ $leaveRequest->created_at->format('M d, Y h:i A') }}</span>
                </div>
                <div>
                    @if($leaveRequest->status === 'pending')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> Pending Approval</span>
                    @elseif($leaveRequest->status === 'approved')
                        <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-check-circle me-1"></i> Approved</span>
                    @elseif($leaveRequest->status === 'rejected')
                        <span class="badge bg-danger fs-6 px-3 py-2"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                    @else
                        <span class="badge bg-secondary fs-6 px-3 py-2"><i class="bi bi-slash-circle me-1"></i> Cancelled</span>
                    @endif
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">APPLICANT</div>
                        <div class="fw-bold text-dark fs-6">{{ $leaveRequest->user->name }}</div>
                        <div class="text-muted small">{{ $leaveRequest->user->department?->name ?? 'Unassigned' }} • {{ $leaveRequest->user->email }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">LEAVE TYPE</div>
                        <div class="fw-bold text-dark fs-6">{{ $leaveRequest->leaveType->name }}</div>
                        <div class="text-muted small">Allowance: {{ $leaveRequest->leaveType->max_days_per_year }} days/year</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">LEAVE DATES</div>
                        <div class="fw-bold text-dark fs-6">
                            {{ $leaveRequest->from_date->format('M d, Y') }} — {{ $leaveRequest->to_date->format('M d, Y') }}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">DURATION</div>
                        <div class="fw-bold text-primary fs-6">
                            {{ $leaveRequest->total_days }} {{ Str::plural('Day', $leaveRequest->total_days) }}
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">REASON FOR LEAVE</div>
                        <div class="text-dark fs-6 mt-1">{{ $leaveRequest->reason }}</div>
                    </div>
                </div>

                @if($leaveRequest->action_by)
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-info">
                            <div class="text-muted small fw-semibold">ACTION TAKEN BY</div>
                            <div class="fw-bold text-dark fs-6">
                                {{ $leaveRequest->approver?->name }} ({{ ucfirst($leaveRequest->approver?->role) }})
                                <span class="text-muted fw-normal font-monospace">@ {{ $leaveRequest->action_at?->format('M d, Y h:i A') }}</span>
                            </div>
                            @if($leaveRequest->admin_comment)
                                <div class="mt-2 text-muted"><strong>Comment:</strong> "{{ $leaveRequest->admin_comment }}"</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Approval Action Box for Managers & Admins -->
            @if($leaveRequest->isPending() && (auth()->user()->isAdmin() || auth()->user()->isManager()))
                <div class="card card-custom p-4 bg-light border border-warning mt-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-warning me-2"></i> Manager / Admin Decision</h5>
                    
                    <form method="POST" action="{{ route('leave-requests.approve-reject', $leaveRequest) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="admin_comment" class="form-label fw-semibold">Manager / Admin Comment <span class="text-muted fw-normal">(Required if rejecting)</span></label>
                            <textarea class="form-control @error('admin_comment') is-invalid @enderror" id="admin_comment" name="admin_comment" rows="3" placeholder="Add remarks or justification..."></textarea>
                            @error('admin_comment')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" name="status" value="approved" class="btn btn-success px-4">
                                <i class="bi bi-check-lg me-1"></i> Approve Request
                            </button>
                            <button type="submit" name="status" value="rejected" class="btn btn-danger px-4">
                                <i class="bi bi-x-lg me-1"></i> Reject Request
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Cancel Button for Applicant Employee -->
            @if($leaveRequest->isPending() && auth()->id() === $leaveRequest->user_id)
                <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                    <form action="{{ route('leave-requests.cancel', $leaveRequest) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i> Cancel Leave Request
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
