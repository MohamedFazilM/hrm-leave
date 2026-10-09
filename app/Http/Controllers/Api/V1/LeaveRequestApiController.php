<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestApiController extends Controller
{
    /**
     * Get user's leave balance summary per leave type.
     */
    public function leaveBalance(Request $request): JsonResponse
    {
        $balances = $request->user()->getLeaveBalances();

        return response()->json([
            'data' => $balances,
        ], 200);
    }

    /**
     * List leave requests with role-based scoping and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = LeaveRequest::with(['user.department', 'leaveType', 'approver']);

        if (! $user->isAdmin() && ! $user->isManager()) {
            $query->where('user_id', $user->id);
        } else if ($user->isManager() && ! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user', fn($uq) => $uq->where('department_id', $user->department_id));
            });
        }

        $leaveRequests = $query
            ->when($request->query('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->query('leave_type_id'), fn($q, $t) => $q->where('leave_type_id', $t))
            ->when($request->query('user_id'), fn($q, $u) => $q->where('user_id', $u))
            ->latest()
            ->paginate(10);

        return response()->json(LeaveRequestResource::collection($leaveRequests)->response()->getData(true), 200);
    }

    /**
     * Submit a new leave request.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $user = $request->user();
        $fromDate = Carbon::parse($request->from_date);
        $toDate = Carbon::parse($request->to_date);
        $totalDays = $fromDate->diffInDays($toDate) + 1;

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $leaveRequest->load(['user.department', 'leaveType']);

        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'data' => new LeaveRequestResource($leaveRequest),
        ], 201);
    }

    /**
     * Display a specific leave request.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $leaveRequest = LeaveRequest::with(['user.department', 'leaveType', 'approver'])->find($id);

        if (! $leaveRequest) {
            return response()->json(['message' => 'Leave request not found.'], 404);
        }

        if (! $user->isAdmin() && ! $user->isManager() && $user->id !== $leaveRequest->user_id) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return response()->json([
            'data' => new LeaveRequestResource($leaveRequest),
        ], 200);
    }

    /**
     * Cancel a pending leave request.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $leaveRequest = LeaveRequest::find($id);

        if (! $leaveRequest) {
            return response()->json(['message' => 'Leave request not found.'], 404);
        }

        if ($leaveRequest->user_id !== $user->id) {
            return response()->json(['message' => 'You can only cancel your own leave requests.'], 403);
        }

        if (! $leaveRequest->isPending()) {
            return response()->json(['message' => 'Only pending leave requests can be cancelled.'], 422);
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Leave request cancelled successfully.',
            'data' => new LeaveRequestResource($leaveRequest->fresh(['user.department', 'leaveType'])),
        ], 200);
    }

    /**
     * Approve a leave request (Manager / Admin).
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isManager()) {
            return response()->json(['message' => 'Unauthorized action. Admin or Manager role required.'], 403);
        }

        $leaveRequest = LeaveRequest::find($id);

        if (! $leaveRequest) {
            return response()->json(['message' => 'Leave request not found.'], 404);
        }

        if (! $leaveRequest->isPending()) {
            return response()->json(['message' => 'This leave request has already been processed.'], 422);
        }

        $leaveRequest->update([
            'status' => 'approved',
            'action_by' => $user->id,
            'admin_comment' => $request->input('admin_comment'),
            'action_at' => now(),
        ]);

        return response()->json([
            'message' => 'Leave request approved successfully.',
            'data' => new LeaveRequestResource($leaveRequest->fresh(['user.department', 'leaveType', 'approver'])),
        ], 200);
    }

    /**
     * Reject a leave request (Manager / Admin).
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isManager()) {
            return response()->json(['message' => 'Unauthorized action. Admin or Manager role required.'], 403);
        }

        $leaveRequest = LeaveRequest::find($id);

        if (! $leaveRequest) {
            return response()->json(['message' => 'Leave request not found.'], 404);
        }

        if (! $leaveRequest->isPending()) {
            return response()->json(['message' => 'This leave request has already been processed.'], 422);
        }

        $request->validate([
            'admin_comment' => ['required', 'string', 'max:1000'],
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'action_by' => $user->id,
            'admin_comment' => $request->input('admin_comment'),
            'action_at' => now(),
        ]);

        return response()->json([
            'message' => 'Leave request rejected successfully.',
            'data' => new LeaveRequestResource($leaveRequest->fresh(['user.department', 'leaveType', 'approver'])),
        ], 200);
    }
}
