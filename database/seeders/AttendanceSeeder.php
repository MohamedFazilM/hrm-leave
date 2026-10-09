<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $lateCutoff = config('hrm.late_cutoff', '09:30');
        [$cutoffHour, $cutoffMin] = explode(':', $lateCutoff);

        // Seed attendance for the last 10 days
        for ($i = 10; $i >= 1; $i--) {
            $date = Carbon::now()->subDays($i);

            // Skip weekends (Saturday and Sunday)
            if ($date->isWeekend()) {
                continue;
            }

            foreach ($users as $index => $user) {
                // Vary check-in time slightly for different users
                $isLate = ($index % 3 === 0); // Every 3rd user on some days is late
                $checkInHour = $isLate ? (int)$cutoffHour : ((int)$cutoffHour - 1);
                $checkInMinute = $isLate ? rand(35, 55) : rand(5, 25);

                $checkIn = Carbon::parse($date->format('Y-m-d') . " {$checkInHour}:{$checkInMinute}:00");
                $checkOut = Carbon::parse($date->format('Y-m-d') . " 17:" . rand(30, 59) . ":00");

                $status = ($checkIn->format('H:i') > $lateCutoff) ? 'late' : 'present';

                Attendance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'status' => $status,
                        'notes' => $isLate ? 'Traffic congestion delay' : null,
                    ]
                );
            }
        }
    }
}
