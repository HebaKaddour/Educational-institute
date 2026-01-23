<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Session;
use App\Models\workingDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\V1\SettingsSchedulerService;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\V1\Settings\StoreSessionRequest;
use App\Http\Requests\V1\Settings\StorescheduleRequest;
use App\Http\Requests\V1\Settings\UpdateSessionRequest;

class SettingsSchedulerController extends Controller
{
    public function __construct(private  SettingsSchedulerService $settingsSchedulerService)
    {}

    //get all working days with periods
    public function index()
    {
        $workingDays = $this->settingsSchedulerService->getAllWorkingDays();
        return self::success($workingDays, 'قائمة أيام الدوام والحصص');
    }

    public function storeWorkingDay(StorescheduleRequest $request)
    {
        return self::success(
            $this->settingsSchedulerService->creatWorkingDay($request->validated()),
            'تم إنشاء أيام الدوام بنجاح',
            201
        );
    }

     //get schedule by gender
    public function getScheduleByGender(Request $request)
    {
    $gender = $request->query('gender');

    if (!$gender) {
        return self::error(
            ['gender' => ['الجنس مطلوب']],
            'خطأ في البيانات',
            422
        );
    }

    $schedule = $this->settingsSchedulerService->getScheduleByGender($gender);

    return self::success($schedule, 'الجدول حسب الجنس');
    }

     //create working day
    public function schedule(string $gender)
    {
        return self::success(
            $this->settingsSchedulerService->getScheduleByGender($gender),
            'الجدول حسب الجنس'
        );
    }

    //add session to working day
   public function storeSession(StoreSessionRequest $request,WorkingDay $workingDay) {
    $session = $workingDay->sessions()->create($request->validated());
    $session->load('workingDay');

    return self::success(
        [
            'session' => [
                'id'          => $session->id,
                'name'        => $session->session_name,
                'start_time'  => $session->start_time,
                'end_time'    => $session->end_time,
            ],
            'working_day' => [
                'id'       => $workingDay->id,
                'day_name' => $workingDay->day_name,
                'gender'   => $workingDay->gender,
            ]
        ],
        'تم إضافة الحصة بنجاح',
        201
    );
}
public function updateSession(UpdateSessionRequest $request, WorkingDay $workingDay, Session $session)
{
    if ($session->working_day_id !== $workingDay->id) {
        return self::error(
            'هذه الحصة لا تنتمي إلى يوم الدوام المحدد',
            403
        );
    }

    // تحديث الحصة
    $session->update($request->validated());

    $session->load('workingDay');

    return self::success(
        [
            'session' => [
                'id'          => $session->id,
                'name'        => $session->session_name,
                'start_time'  => $session->start_time,
                'end_time'    => $session->end_time,
            ],
            'working_day' => [
                'id'       => $workingDay->id,
                'day_name' => $workingDay->day_name,
                'gender'   => $workingDay->gender,
            ]
        ],
        'تم تعديل الحصة بنجاح',
        200
    );
}
public function deleteScheduler( WorkingDay $workingDay){

    $workingDay->delete();
    return self::success($workingDay,'تم حذف يوم الدوام بنجاح',200);

}

public function deleteSession(WorkingDay $workingDay, Session $session){
    if ($session->working_day_id !== $workingDay->id) {
        throw ValidationException::withMessages([
            'session' => 'هذه الحصة لا تنتمي إلى يوم الدوام المحدد'
        ]);
    }

    $session->delete();
    return self::success($session,'تم حذف هذه الحصة بنجاح',200);


}


}
