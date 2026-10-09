<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $fromDate = Carbon::tomorrow();
        $toDate = (clone $fromDate)->addDays(2);

        return [
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'from_date' => $fromDate->format('Y-m-d'),
            'to_date' => $toDate->format('Y-m-d'),
            'total_days' => 3,
            'reason' => 'Personal work.',
            'status' => 'pending',
        ];
    }
}
