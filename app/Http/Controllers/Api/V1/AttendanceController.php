<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\V1\AttendanceService;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\AttendanceStatisticResource;
use App\Http\Requests\V1\Attendance\StoreAttendanceRequest;
use App\Http\Requests\V1\Attendance\AttendanceFilterRequest;
use App\Http\Requests\V1\Attendance\UpdateAttendanceRequest;

class AttendanceController extends Controller
{
   public function __construct(
        private AttendanceService $attendanceService
    ) {}

    // Teacher
    public function store(StoreAttendanceRequest $request)
    {
        $attendances = $this->attendanceService->storeDailyAttendance($request->validated());
           $attendances->load(['student', 'subject', 'evaluations.evaluationType']);
        return self::success(AttendanceResource::collection($attendances), 'تم تسجيل الحضور بنجاح', 201);

    }

    public function updateDailyAttendance(UpdateAttendanceRequest $request , Attendance $attendance){
        $updated_attendance = $this->attendanceService->updateAttendance($request->validated(),$attendance);
        return self::success(new AttendanceResource($updated_attendance),'تم تحديث الحضور بنجاح',200);
    }


 public function daily(AttendanceFilterRequest $request)
    {
    $students = $this->attendanceService->daily($request->validated()
    );

           return self::success(AttendanceStatisticResource::collection($students),'تم جلب الحضور بنجاح',200);

    }

    public function destroy(Attendance $attendance)
{
    $this->attendanceService->deleteAttendance($attendance);

    return self::success(
        null,
        'تم حذف سجل الحضور بنجاح',
        200
    );
}
}
