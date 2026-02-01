<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\authController;
use App\Http\Controllers\Api\V1\paymentController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\SubjectController;
use App\Http\Controllers\Api\V1\TeacherController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\AttendanceReportController;
use App\Http\Controllers\Api\V1\SettingsSchedulerController;
use App\Http\Controllers\Api\V1\Reports\StudentReportController;
use App\Http\Controllers\Api\V1\Evaluations\EvaluationTypeController;
use App\Http\Controllers\Api\V1\Evaluations\StudentEvaluationsController;
use App\Http\Controllers\Api\V1\Evaluations\SubjectEvaluationSettingController;

Route::prefix('V1')->group(function () {
// Auth
    Route::post('login', [authController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {

     Route::post('logout', [authController::class, 'logout']);
     Route::post('change-password', [authController::class, 'changePassword']);

   // Teacher and admin manage attendances/Settings Subject Evaluation
    Route::middleware(['role:teacher|admin'])->group(function () {

        // attendance
       Route::get('attendance/daily', [AttendanceController::class, 'daily']);
       Route::post('attendance/daily', [AttendanceController::class, 'store']);
        Route::put('/attendance/daily/{attendance}', [AttendanceController::class, 'updateDailyAttendance']);
       Route::delete('attendances/{attendance}',[AttendanceController::class, 'destroy']);

         // Settings Subject Evaluation
        Route::get('subjects/{subject}/evaluation-settings', [SubjectEvaluationSettingController::class, 'index']);
        Route::post('subjects/{subject}/evaluation-settings', [SubjectEvaluationSettingController::class, 'store']);
        Route::put('subject/evaluation-settings/{subjectEvaluationSetting}',[SubjectEvaluationSettingController::class, 'update']);

        Route::delete('subject/evaluation-settings/{subjectEvaluationSetting}',[SubjectEvaluationSettingController::class, 'destroy']);


        // Student Evaluations
       Route::post('students/evaluations', [StudentEvaluationsController::class, 'store']);
       Route::get('grades', [StudentEvaluationsController::class, 'index']);
       Route::put('evaluations/{evaluation}', [StudentEvaluationsController::class, 'update']);
       Route::delete('evaluations/{evaluation}', [StudentEvaluationsController::class, 'deleteGrades']);
    });
        // Admin only
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('teachers', TeacherController::class);
            Route::apiResource('subjects', SubjectController::class);
             Route::get('students/search', [StudentController::class, 'search']);
            Route::apiResource('students', StudentController::class);
            Route::get('allSubscriptions/details',[StudentController::class,'allSubscriptions']);
            Route::patch('/students-profile/{student}', [StudentController::class, 'updateProfile']);
            Route::get('admin/attendances', [AttendanceController::class, 'index']);


           Route::patch('update/subscriptions/{subscription}',
            [StudentController::class, 'updateStudentSubscription']
        );
            Route::patch('students/{student}/change-status', [StudentController::class, 'changeStatus']);
            Route::post('students/{student}/subscriptions',[StudentController::class, 'addSubscription']);
    // PAYMENT
     Route::post('/subscriptions/{subscription}/payments',[paymentController::class, 'store']);
     Route::put('/payments/{payment}', [PaymentController::class, 'update']);
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);
     Route::get(
'/subscriptions/{subscription}/monthly-payments',
    [paymentController::class, 'monthlyPayments']
);

    //settings scheduler
    Route::post('settings/working-days', [SettingsSchedulerController::class, 'storeWorkingDay']);
    Route::post('settings/working-days/{workingDay}/sessions',[SettingsSchedulerController::class, 'storeSession']);
    Route::put(
    'working-days/{workingDay}/sessions/{session}',
    [SettingsSchedulerController::class, 'updateSession']);
    Route::delete('settings/working-days/{workingDay}',[SettingsSchedulerController::class,'deleteScheduler']);
    Route::delete('settings/working-days/{workingDay}/sessions/{session}',[SettingsSchedulerController::class,'deleteSession']);


    Route::get('settings/working-days', [SettingsSchedulerController::class, 'index']);
    Route::get('settings/schedule/',[SettingsSchedulerController::class, 'getScheduleByGender']);


    // settings Evaluations Types
     Route::get('settings/evaluation-types', [EvaluationTypeController::class, 'index']);
    Route::post('settings/evaluation-types', [EvaluationTypeController::class, 'store']);
    Route::put('settings/evaluation-types/{evaluationType}', [EvaluationTypeController::class, 'update']);
    Route::delete('settings/evaluation-types/{evaluationType}', [EvaluationTypeController::class, 'destroy']);



    // Reports
      Route::get('reports/students', [StudentReportController::class, 'index']);
      Route::get('grades/print/pdf', [StudentReportController::class, 'print']);


    Route::get('attendance/daily', [AttendanceReportController::class,'daily']);
    Route::get('attendance/daily/print', [AttendanceReportController::class,'dailyPrint']);

    Route::get('attendance/student', [AttendanceReportController::class,'byStudent']);
    Route::get('attendance/student/print', [AttendanceReportController::class,'byStudentPrint']);

    Route::get('attendance/grade', [AttendanceReportController::class,'byGrade']);
    Route::get('attendance/grade/print', [AttendanceReportController::class,'byGradePrint']);
      });
    });
    });
