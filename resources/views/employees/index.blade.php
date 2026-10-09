@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1">Employee Directory</h3>
        <p class="text-muted mb-0">Manage staff members, roles, and departmental assignments</p>
    </div>
    <a href="{{ route('employees.create') }}" class="btn btn-gradient">
        <i class="bi bi-person-plus me-1"></i> Add New Employee
    </a>
</div>

<div class="card card-custom p-4">
    <!-- Filter and Search Form -->
    <form method="GET" action="{{ route('employees.index') }}" class="row g-3 mb-4">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, designation..." value="{{ $search }}">
            </div>
        </div>

        <div class="col-md-3">
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
            <select name="role" class="form-select">
                <option value="">-- All Roles --</option>
                <option value="admin" {{ $roleFilter == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ $roleFilter == 'manager' ? 'selected' : '' }}>Manager</option>
                <option value="employee" {{ $roleFilter == 'employee' ? 'selected' : '' }}>Employee</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
            @if($search || $departmentId || $roleFilter)
                <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Employee Name</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Phone</th>
                    <th>Join Date</th>
                    <th class="text-end" style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td>
                            <div class="fw-bold text-primary">
                                <a href="{{ route('employees.show', $emp) }}" class="text-decoration-none">
                                    {{ $emp->name }}
                                </a>
                            </div>
                            <div class="text-muted small">{{ $emp->email }}</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $emp->role }} text-capitalize">
                                {{ $emp->role }}
                            </span>
                        </td>
                        <td>
                            @if($emp->department)
                                <span class="badge bg-light text-dark border">{{ $emp->department->name }}</span>
                            @else
                                <span class="text-muted small">Unassigned</span>
                            @endif
                        </td>
                        <td>{{ $emp->designation ?? 'N/A' }}</td>
                        <td>{{ $emp->phone ?? 'N/A' }}</td>
                        <td>{{ $emp->join_date ? $emp->join_date->format('M d, Y') : 'N/A' }}</td>
                        <td class="text-end">
                            <a href="{{ route('employees.show', $emp) }}" class="btn btn-sm btn-outline-info me-1" title="View Profile">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if(auth()->id() !== $emp->id)
                                <form action="{{ route('employees.destroy', $emp) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-people fs-2 d-block mb-2"></i> No employees found matching criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $employees->links() }}
    </div>
</div>
@endsection
