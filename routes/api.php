<?php

use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LeaveRequestApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // Public auth endpoint
    Route::post('login', [AuthController::class, 'login']);

    // Authenticated Sanctum Routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth profile & logout
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        // Leave Balances
        Route::get('leave-balance', [LeaveRequestApiController::class, 'leaveBalance']);

        // Leave Requests
        Route::get('leave-requests', [LeaveRequestApiController::class, 'index']);
        Route::post('leave-requests', [LeaveRequestApiController::class, 'store']);
        Route::get('leave-requests/{id}', [LeaveRequestApiController::class, 'show']);
        Route::post('leave-requests/{id}/cancel', [LeaveRequestApiController::class, 'cancel']);

        // Manager / Admin Leave Approval Endpoints
        Route::middleware('role:admin,manager')->group(function () {
            Route::post('leave-requests/{id}/approve', [LeaveRequestApiController::class, 'approve']);
            Route::post('leave-requests/{id}/reject', [LeaveRequestApiController::class, 'reject']);
        });

        // Attendance Endpoints
        Route::get('attendance', [AttendanceApiController::class, 'index']);
        Route::post('attendance/check-in', [AttendanceApiController::class, 'checkIn']);
        Route::post('attendance/check-out', [AttendanceApiController::class, 'checkOut']);
    });
});
