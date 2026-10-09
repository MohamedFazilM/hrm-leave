<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'apiuser@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'apiuser@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'user' => ['id', 'name', 'email', 'role'],
        ]);
    }

    public function test_api_login_with_wrong_password_fails(): void
    {
        $user = User::factory()->create([
            'email' => 'apiuser@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'apiuser@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_me_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_employee_approve_via_api_returns_403(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $leaveRequest = LeaveRequest::factory()->create(['status' => 'pending']);

        $token = $employee->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson("/api/v1/leave-requests/{$leaveRequest->id}/approve", [
                             'admin_comment' => 'Unauthorized approve.',
                         ]);

        $response->assertStatus(403);
    }

    public function test_leave_apply_via_api_returns_201(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $leaveType = LeaveType::factory()->create(['max_days_per_year' => 12]);

        $token = $employee->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/v1/leave-requests', [
                             'leave_type_id' => $leaveType->id,
                             'from_date' => Carbon::tomorrow()->format('Y-m-d'),
                             'to_date' => Carbon::tomorrow()->addDays(2)->format('Y-m-d'),
                             'reason' => 'API leave application.',
                         ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.total_days', 3);
        $response->assertJsonPath('data.status', 'pending');
    }

    public function test_duplicate_check_in_via_api_returns_422(): void
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'check_in' => Carbon::now()->setTime(9, 0),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/v1/attendance/check-in');

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'You have already checked in today at 09:00 AM.');
    }
}
