<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\authController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\TeacherController;

Route::prefix('V1')->group(function () {


// Auth
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('change-password', [AuthController::class, 'changePassword']);

        // Admin only
        Route::middleware('role:admin')->group(function () {

            Route::apiResource('teachers', TeacherController::class);
            Route::apiResource('subjects', SubjectController::class);
            Route::apiResource('students', StudentController::class);
            Route::patch('subscriptions/{subscription_id}/pay',[StudentController::class, 'pay']
        );

        });
    });
});
