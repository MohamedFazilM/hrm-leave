@extends('layouts.app')

@section('title', 'Employee Profile')

@section('content')
<div class="mb-4">
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Employee Directory
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom p-4 bg-white">
            <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold fs-2" style="width: 80px; height: 80px;">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="fw-bold mb-1">{{ $employee->name }}</h3>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-{{ $employee->role }} text-capitalize px-3 py-1 fs-6">
                            {{ $employee->role }}
                        </span>
                        <span class="text-muted">• {{ $employee->designation ?? 'No Designation' }}</span>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">EMAIL ADDRESS</div>
                        <div class="fw-bold text-dark fs-6">{{ $employee->email }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">PHONE NUMBER</div>
                        <div class="fw-bold text-dark fs-6">{{ $employee->phone ?? 'Not provided' }}</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">DEPARTMENT</div>
                        <div class="fw-bold text-dark fs-6">
                            {{ $employee->department?->name ?? 'Unassigned' }}
                            @if($employee->department)
                                <span class="badge bg-secondary font-monospace ms-1">{{ $employee->department->code }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3">
                        <div class="text-muted small fw-semibold">JOIN DATE</div>
                        <div class="fw-bold text-dark fs-6">
                            {{ $employee->join_date ? $employee->join_date->format('F d, Y') : 'Not specified' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary px-4">
                    <i class="bi bi-pencil me-1"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
