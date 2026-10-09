@extends('layouts.app')

@section('title', 'Apply for Leave')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0"><i class="bi bi-calendar-plus text-primary me-2"></i> Apply for Leave</h4>
                <a href="{{ route('leave-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Requests
                </a>
            </div>

            <!-- Balance Quick Cards -->
            <div class="row g-2 mb-4">
                @foreach($leaveBalances as $balance)
                    <div class="col-md-4">
                        <div class="p-2.5 bg-light rounded-3 border text-center">
                            <div class="fw-bold text-dark small">{{ $balance['leave_type']->name }}</div>
                            <div class="text-success fw-bold fs-6">{{ $balance['remaining_balance'] }} / {{ $balance['max_days'] }} Days Available</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('leave-requests.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="leave_type_id" class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('leave_type_id') is-invalid @enderror" id="leave_type_id" name="leave_type_id" required>
                        <option value="">-- Select Leave Type --</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ $type->max_days_per_year }} days/year)
                            </option>
                        @endforeach
                    </select>
                    @error('leave_type_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="from_date" class="form-label fw-semibold">From Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('from_date') is-invalid @enderror" id="from_date" name="from_date" value="{{ old('from_date', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                        @error('from_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="to_date" class="form-label fw-semibold">To Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('to_date') is-invalid @enderror" id="to_date" name="to_date" value="{{ old('to_date', date('Y-m-d')) }}" required min="{{ date('Y-m-d') }}">
                        @error('to_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="reason" class="form-label fw-semibold">Reason for Leave <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="4" required placeholder="Describe the reason for your leave request...">{{ old('reason') }}</textarea>
                    @error('reason')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-gradient px-4">
                        <i class="bi bi-send me-1"></i> Submit Leave Request
                    </button>
                    <a href="{{ route('leave-requests.index') }}" class="btn btn-light px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
