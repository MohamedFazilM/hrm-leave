@extends('layouts.app')

@section('title', 'Leave Types')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">Leave Types Management</h3>
        <p class="text-muted mb-0">Configure leave categories and yearly allowances</p>
    </div>
    <a href="{{ route('leave-types.create') }}" class="btn btn-gradient">
        <i class="bi bi-plus-lg me-1"></i> Add Leave Type
    </a>
</div>

<div class="card card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Code</th>
                    <th>Leave Type Name</th>
                    <th>Yearly Allowance</th>
                    <th>Description</th>
                    <th>Total Requests</th>
                    <th class="text-end" style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes as $type)
                    <tr>
                        <td><span class="badge bg-secondary font-monospace">{{ $type->code }}</span></td>
                        <td class="fw-bold text-primary">{{ $type->name }}</td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success px-2.5 py-1 rounded-pill">
                                {{ $type->max_days_per_year }} Days / Year
                            </span>
                        </td>
                        <td class="text-muted">{{ Str::limit($type->description ?? 'No description', 60) }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $type->leave_requests_count }} requests</span></td>
                        <td class="text-end">
                            <a href="{{ route('leave-types.edit', $type) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('leave-types.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this leave type?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No leave types found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
