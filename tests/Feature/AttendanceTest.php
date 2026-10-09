<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_once_per_day_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/attendances/check-in');

        $response->assertSessionHas('success');
        $this->assertTrue(
            Attendance::where('user_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->exists()
        );
    }

    public function test_duplicate_check_in_rejected(): void
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'check_in' => Carbon::now()->setTime(9, 0),
        ]);

        $response = $this->actingAs($user)->post('/attendances/check-in');

        $response->assertSessionHas('error');
        $this->assertEquals(
            1,
            Attendance::where('user_id', $user->id)
                ->whereDate('date', Carbon::today())
                ->count()
        );
    }

    public function test_check_out_before_check_in_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/attendances/check-out');

        $response->assertSessionHas('error');
    }

    public function test_check_in_after_cutoff_is_marked_late(): void
    {
        $user = User::factory()->create();

        // Travel time to 09:45 AM (after 09:30 AM cutoff)
        Carbon::setTestNow(Carbon::today()->setTime(9, 45));

        $response = $this->actingAs($user)->post('/attendances/check-in');

        $response->assertSessionHas('success');
        $this->assertTrue(
            Attendance::where('user_id', $user->id)
                ->where('status', 'late')
                ->whereDate('date', Carbon::today())
                ->exists()
        );

        Carbon::setTestNow(); // Reset test time
    }
}
