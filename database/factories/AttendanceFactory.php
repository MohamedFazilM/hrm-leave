<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => Carbon::today()->format('Y-m-d'),
            'check_in' => Carbon::now()->setTime(9, 0),
            'check_out' => Carbon::now()->setTime(17, 0),
            'status' => 'present',
            'notes' => null,
        ];
    }
}
