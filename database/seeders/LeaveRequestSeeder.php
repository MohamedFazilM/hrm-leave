<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $manager = User::where('role', 'manager')->first();
        $employee1 = User::where('email', 'employee1@hrm.test')->first();
        $employee2 = User::where('email', 'employee2@hrm.test')->first();

        $casual = LeaveType::where('code', 'CASUAL')->first();
        $sick = LeaveType::where('code', 'SICK')->first();
        $earned = LeaveType::where('code', 'EARNED')->first();

        if ($employee1 && $casual && $sick) {
            // Approved Sick Leave
            LeaveRequest::create([
                'user_id' => $employee1->id,
                'leave_type_id' => $sick->id,
                'from_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'to_date' => Carbon::now()->subDays(9)->format('Y-m-d'),
                'total_days' => 2,
                'reason' => 'Viral fever and rest advised by doctor.',
                'status' => 'approved',
                'action_by' => $manager?->id,
                'admin_comment' => 'Approved. Get well soon.',
                'action_at' => Carbon::now()->subDays(10),
            ]);

            // Pending Casual Leave
            LeaveRequest::create([
                'user_id' => $employee1->id,
                'leave_type_id' => $casual->id,
                'from_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
                'total_days' => 3,
                'reason' => 'Family function in native town.',
                'status' => 'pending',
            ]);
        }

        if ($employee2 && $earned) {
            // Pending Earned Leave
            LeaveRequest::create([
                'user_id' => $employee2->id,
                'leave_type_id' => $earned->id,
                'from_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'to_date' => Carbon::now()->addDays(9)->format('Y-m-d'),
                'total_days' => 5,
                'reason' => 'Annual vacation trip.',
                'status' => 'pending',
            ]);
        }
    }
}
