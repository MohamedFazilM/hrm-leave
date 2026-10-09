<?php

namespace Tests\Feature;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_leave_application_succeeds(): void
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create(['max_days_per_year' => 12]);

        $response = $this->actingAs($user)->post('/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'from_date' => Carbon::tomorrow()->format('Y-m-d'),
            'to_date' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
            'reason' => 'Family vacation.',
        ]);

        $response->assertRedirect('/leave-requests');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'total_days' => 3,
            'status' => 'pending',
        ]);
    }

    public function test_past_dates_rejected(): void
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create();

        $response = $this->actingAs($user)->post('/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'from_date' => Carbon::yesterday()->format('Y-m-d'),
            'to_date' => Carbon::today()->format('Y-m-d'),
            'reason' => 'Past leave.',
        ]);

        $response->assertSessionHasErrors(['from_date']);
        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_to_date_before_from_date_rejected(): void
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create();

        $response = $this->actingAs($user)->post('/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'from_date' => Carbon::tomorrow()->addDays(3)->format('Y-m-d'),
            'to_date' => Carbon::tomorrow()->format('Y-m-d'),
            'reason' => 'Invalid dates.',
        ]);

        $response->assertSessionHasErrors(['to_date']);
        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_overlapping_request_rejected(): void
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create(['max_days_per_year' => 15]);

        LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'from_date' => Carbon::tomorrow()->format('Y-m-d'),
            'to_date' => Carbon::tomorrow()->addDays(4)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        // Try applying overlapping date range
        $response = $this->actingAs($user)->post('/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'from_date' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
            'to_date' => Carbon::tomorrow()->addDays(6)->format('Y-m-d'),
            'reason' => 'Overlapping test.',
        ]);

        $response->assertSessionHasErrors(['from_date']);
    }

    public function test_request_over_remaining_balance_rejected(): void
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create(['max_days_per_year' => 2]);

        $response = $this->actingAs($user)->post('/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'from_date' => Carbon::tomorrow()->format('Y-m-d'),
            'to_date' => Carbon::tomorrow()->addDays(4)->format('Y-m-d'), // 5 days requested vs 2 max
            'reason' => 'Exceeding balance.',
        ]);

        $response->assertSessionHasErrors(['leave_type_id']);
    }

    public function test_employee_can_cancel_own_pending_request_but_not_approved_one(): void
    {
        $user = User::factory()->create();
        $leaveType = LeaveType::factory()->create();

        $pendingRequest = LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'pending',
        ]);

        $approvedRequest = LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'status' => 'approved',
        ]);

        // Cancel pending request succeeds
        $response = $this->actingAs($user)->post("/leave-requests/{$pendingRequest->id}/cancel");
        $response->assertSessionHas('success');
        $this->assertEquals('cancelled', $pendingRequest->fresh()->status);

        // Cancel approved request fails
        $response2 = $this->actingAs($user)->post("/leave-requests/{$approvedRequest->id}/cancel");
        $response2->assertSessionHas('error');
        $this->assertEquals('approved', $approvedRequest->fresh()->status);
    }
}
