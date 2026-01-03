<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\authController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\EvaluationController;

Route::prefix('V1')->group(function () {
// Auth
    Route::post('login', [authController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {

     Route::post('logout', [authController::class, 'logout']);
     Route::post('change-password', [authController::class, 'changePassword']);

   // Teacher only store and update attendances/evaluations
    Route::middleware(['role:teacher'])->group(function () {
        Route::post('teacher/attendances', [AttendanceController::class, 'store']);
        Route::patch('teacher/attendances/{attendance}', [AttendanceController::class, 'update']);
        Route::post('teacher/evaluations', [EvaluationController::class, 'store']);
    });
        // Admin only
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('teachers', TeacherController::class);
            Route::apiResource('subjects', SubjectController::class);
             Route::get('students/search', [StudentController::class, 'search']);
            Route::apiResource('students', StudentController::class);
            Route::get('admin/attendances', [AttendanceController::class, 'index']);
            Route::patch('subscriptions/{subscription_id}/pay',[StudentController::class, 'pay']);
            Route::post('students/{student}/subscriptions',[StudentController::class, 'addSubscription']);
    });
    });
});
