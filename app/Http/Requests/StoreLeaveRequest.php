<?php

namespace App\Http\Requests;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom validation for overlapping requests and leave balance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->from_date && $this->to_date && $this->leave_type_id) {
                $user = $this->user();
                $fromDate = Carbon::parse($this->from_date);
                $toDate = Carbon::parse($this->to_date);

                // 1. Calculate requested total days (inclusive)
                $requestedDays = $fromDate->diffInDays($toDate) + 1;

                // 2. Check for overlapping requests (pending or approved)
                $hasOverlap = LeaveRequest::where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where(function ($query) use ($fromDate, $toDate) {
                        $query->where('from_date', '<=', $toDate->format('Y-m-d'))
                              ->where('to_date', '>=', $fromDate->format('Y-m-d'));
                    })
                    ->exists();

                if ($hasOverlap) {
                    $validator->errors()->add('from_date', 'You already have an active (pending or approved) leave request during these dates.');
                }

                // 3. Check leave balance
                $leaveType = LeaveType::find($this->leave_type_id);
                if ($leaveType) {
                    $balances = $user->getLeaveBalances($fromDate->year);
                    $targetBalance = collect($balances)->firstWhere('leave_type.id', $leaveType->id);

                    $available = $targetBalance['remaining_balance'] ?? 0;

                    if ($requestedDays > $available) {
                        $validator->errors()->add('leave_type_id', "Insufficient leave balance. You requested {$requestedDays} day(s), but only have {$available} day(s) available for {$leaveType->name}.");
                    }
                }
            }
        });
    }
}
