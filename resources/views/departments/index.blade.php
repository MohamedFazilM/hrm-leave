@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">Departments Management</h3>
        <p class="text-muted mb-0">Create and manage organization departments</p>
    </div>
    <a href="{{ route('departments.create') }}" class="btn btn-gradient">
        <i class="bi bi-plus-lg me-1"></i> Add New Department
    </a>
</div>

<div class="card card-custom p-4">
    <!-- Search Bar -->
    <form method="GET" action="{{ route('departments.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by department name or code..." value="{{ $search }}">
                <button type="submit" class="btn btn-primary">Search</button>
                @if($search)
                    <a href="{{ route('departments.index') }}" class="btn btn-outline-secondary">Clear</a>
                @endif
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 80px;">#</th>
                    <th>Code</th>
                    <th>Department Name</th>
                    <th>Description</th>
                    <th>Employees</th>
                    <th class="text-end" style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $department)
                    <tr>
                        <td>{{ $department->id }}</td>
                        <td><span class="badge bg-secondary font-monospace">{{ $department->code }}</span></td>
                        <td class="fw-bold text-primary">
                            <a href="{{ route('departments.show', $department) }}" class="text-decoration-none">
                                {{ $department->name }}
                            </a>
                        </td>
                        <td class="text-muted">{{ Str::limit($department->description ?? 'No description provided', 50) }}</td>
                        <td>
                            <span class="badge bg-info-subtle text-info border border-info px-2.5 py-1 rounded-pill">
                                <i class="bi bi-people me-1"></i> {{ $department->employees_count }} Staff
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('departments.show', $department) }}" class="btn btn-sm btn-outline-info me-1" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this department?');">
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
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i> No departments found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $departments->links() }}
    </div>
</div>
@endsection
