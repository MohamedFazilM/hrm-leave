<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_approve_or_reject_leave_request(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $leaveRequest = LeaveRequest::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($employee)->post("/leave-requests/{$leaveRequest->id}/approve-reject", [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_and_admin_can_approve_leave_request(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $admin = User::factory()->create(['role' => 'admin']);

        $leaveRequest1 = LeaveRequest::factory()->create(['status' => 'pending']);
        $leaveRequest2 = LeaveRequest::factory()->create(['status' => 'pending']);

        // Manager approves
        $res1 = $this->actingAs($manager)->post("/leave-requests/{$leaveRequest1->id}/approve-reject", [
            'status' => 'approved',
            'admin_comment' => 'Approved by manager.',
        ]);
        $res1->assertSessionHas('success');
        $this->assertEquals('approved', $leaveRequest1->fresh()->status);

        // Admin approves
        $res2 = $this->actingAs($admin)->post("/leave-requests/{$leaveRequest2->id}/approve-reject", [
            'status' => 'approved',
            'admin_comment' => 'Approved by admin.',
        ]);
        $res2->assertSessionHas('success');
        $this->assertEquals('approved', $leaveRequest2->fresh()->status);
    }

    public function test_employee_cannot_access_admin_pages(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $res1 = $this->actingAs($employee)->get('/departments');
        $res1->assertStatus(403);

        $res2 = $this->actingAs($employee)->get('/employees');
        $res2->assertStatus(403);
    }

    public function test_employee_cannot_see_another_employees_attendance(): void
    {
        $emp1 = User::factory()->create(['role' => 'employee']);
        $emp2 = User::factory()->create(['role' => 'employee']);

        $att1 = Attendance::factory()->create(['user_id' => $emp1->id]);
        $att2 = Attendance::factory()->create(['user_id' => $emp2->id]);

        $response = $this->actingAs($emp1)->get('/attendances');

        $response->assertStatus(200);
        $response->assertSee($emp1->name);
        $response->assertDontSee($emp2->name);
    }
}
