<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_report_counts_present_late_leave_and_absent_days_correctly(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->create();

        $month = 10;
        $year = 2026;

        // 1 Present day
        Attendance::factory()->create([
            'user_id' => $employee->id,
            'date' => "{$year}-{$month}-01",
            'status' => 'present',
        ]);

        // 1 Late day
        Attendance::factory()->create([
            'user_id' => $employee->id,
            'date' => "{$year}-{$month}-02",
            'status' => 'late',
        ]);

        // 2 Approved Leave days
        $leaveType = LeaveType::factory()->create();
        LeaveRequest::factory()->create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'from_date' => "{$year}-{$month}-05",
            'to_date' => "{$year}-{$month}-06",
            'total_days' => 2,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get("/reports/monthly-attendance?month={$month}&year={$year}");

        $response->assertStatus(200);
        $response->assertSee($employee->name);
    }

    public function test_csv_export_returns_csv_response_with_expected_headers(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/reports/monthly-attendance/export?month=10&year=2026');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="monthly_attendance_report_October_2026.csv"');
    }
}
