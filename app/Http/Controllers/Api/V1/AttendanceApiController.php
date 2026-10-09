<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    /**
     * Get attendance history (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Attendance::with(['user.department']);

        if (! $user->isAdmin() && ! $user->isManager()) {
            $query->where('user_id', $user->id);
        } else {
            $query->when($request->query('date'), fn($q, $d) => $q->where('date', $d))
                  ->when($request->query('user_id'), fn($q, $u) => $q->where('user_id', $u))
                  ->when($request->query('department_id'), fn($q, $dept) => $q->whereHas('user', fn($uq) => $uq->where('department_id', $dept)));
        }

        $attendances = $query->latest('date')->latest('check_in')->paginate(15);

        return response()->json(AttendanceResource::collection($attendances)->response()->getData(true), 200);
    }

    /**
     * Employee daily check-in.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            $formattedTime = $existing->check_in ? $existing->check_in->format('h:i A') : '';
            return response()->json([
                'message' => "You have already checked in today at {$formattedTime}.",
                'data' => new AttendanceResource($existing->load('user.department')),
            ], 422);
        }

        $now = Carbon::now();
        $lateCutoff = config('hrm.late_cutoff', '09:30');
        $currentTime = $now->format('H:i');

        $status = ($currentTime > $lateCutoff) ? 'late' : 'present';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $now,
            'status' => $status,
            'notes' => $request->input('notes'),
        ]);

        return response()->json([
            'message' => 'Check-in successful.',
            'data' => new AttendanceResource($attendance->load('user.department')),
        ], 201);
    }

    /**
     * Employee daily check-out.
     */
    public function checkOut(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (! $attendance) {
            return response()->json([
                'message' => 'You must check-in first before checking out.',
            ], 422);
        }

        if ($attendance->check_out) {
            return response()->json([
                'message' => 'You have already checked out today at ' . $attendance->check_out->format('h:i A'),
                'data' => new AttendanceResource($attendance->load('user.department')),
            ], 422);
        }

        $now = Carbon::now();
        $attendance->update([
            'check_out' => $now,
        ]);

        return response()->json([
            'message' => 'Check-out successful.',
            'data' => new AttendanceResource($attendance->fresh(['user.department'])),
        ], 200);
    }
}
