@extends('layouts.app')

@section('title', 'Department Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Departments
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-custom p-4 bg-white h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary-subtle p-3 text-primary fs-3">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0">{{ $department->name }}</h4>
                    <span class="badge bg-secondary font-monospace">{{ $department->code }}</span>
                </div>
            </div>
            <p class="text-muted mb-4">{{ $department->description ?? 'No description available for this department.' }}</p>
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <span class="text-muted">Total Employees:</span>
                <span class="fw-bold fs-5 text-primary">{{ $department->employees->count() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card card-custom p-4 bg-white">
            <h5 class="fw-bold mb-3"><i class="bi bi-people text-primary me-2"></i> Department Staff Members</h5>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Role</th>
                            <th>Phone</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($department->employees as $emp)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $emp->name }}</div>
                                    <div class="text-muted small">{{ $emp->email }}</div>
                                </td>
                                <td>{{ $emp->designation ?? 'N/A' }}</td>
                                <td><span class="badge badge-{{ $emp->role }} text-capitalize">{{ $emp->role }}</span></td>
                                <td>{{ $emp->phone ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No employees assigned to this department yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
