<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Casual Leave',
                'code' => 'CASUAL',
                'max_days_per_year' => 12,
                'description' => 'For personal work, family events, or unforeseen short notice absences.',
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SICK',
                'max_days_per_year' => 10,
                'description' => 'For medical reasons, illness, health appointments, and recovery.',
            ],
            [
                'name' => 'Earned Leave',
                'code' => 'EARNED',
                'max_days_per_year' => 15,
                'description' => 'Paid vacation leave accrued over service tenure for planned holidays.',
            ],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
