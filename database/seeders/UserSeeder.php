<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hrDept = Department::where('code', 'HR')->first();
        $engDept = Department::where('code', 'ENG')->first();
        $mktDept = Department::where('code', 'MKT')->first();
        $finDept = Department::where('code', 'FIN')->first();

        $password = Hash::make('password');

        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@hrm.test'],
            [
                'name' => 'System Admin',
                'password' => $password,
                'role' => 'admin',
                'department_id' => $hrDept?->id,
                'designation' => 'HR Director & Administrator',
                'join_date' => '2023-01-15',
                'phone' => '+1 555-0100',
            ]
        );

        // Manager User
        User::updateOrCreate(
            ['email' => 'manager@hrm.test'],
            [
                'name' => 'Alex Morgan',
                'password' => $password,
                'role' => 'manager',
                'department_id' => $engDept?->id,
                'designation' => 'Engineering Manager',
                'join_date' => '2023-03-01',
                'phone' => '+1 555-0101',
            ]
        );

        // Employee 1
        User::updateOrCreate(
            ['email' => 'employee1@hrm.test'],
            [
                'name' => 'Sarah Connor',
                'password' => $password,
                'role' => 'employee',
                'department_id' => $engDept?->id,
                'designation' => 'Senior Full Stack Developer',
                'join_date' => '2023-06-10',
                'phone' => '+1 555-0102',
            ]
        );

        // Employee 2
        User::updateOrCreate(
            ['email' => 'employee2@hrm.test'],
            [
                'name' => 'David Miller',
                'password' => $password,
                'role' => 'employee',
                'department_id' => $mktDept?->id,
                'designation' => 'Growth & Marketing Lead',
                'join_date' => '2023-08-20',
                'phone' => '+1 555-0103',
            ]
        );

        // Employee 3
        User::updateOrCreate(
            ['email' => 'employee3@hrm.test'],
            [
                'name' => 'Emily Watson',
                'password' => $password,
                'role' => 'employee',
                'department_id' => $finDept?->id,
                'designation' => 'Senior Accountant',
                'join_date' => '2024-01-05',
                'phone' => '+1 555-0104',
            ]
        );
    }
}
