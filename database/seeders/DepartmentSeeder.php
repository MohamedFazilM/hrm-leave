<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Handles recruitment, employee relations, leave policies, and payroll operations.',
            ],
            [
                'name' => 'Engineering & Development',
                'code' => 'ENG',
                'description' => 'Responsible for software product design, system engineering, and tech infrastructure.',
            ],
            [
                'name' => 'Marketing & Sales',
                'code' => 'MKT',
                'description' => 'Manages brand campaigns, digital marketing, client acquisition, and growth strategies.',
            ],
            [
                'name' => 'Finance & Accounting',
                'code' => 'FIN',
                'description' => 'Oversees financial planning, budgeting, corporate auditing, and accounting affairs.',
            ],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}
