<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\workingDay;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\V1\SettingsSchedulerService;
use App\Http\Requests\V1\Settings\StorePeriodRequest;
use App\Http\Requests\V1\Settings\StoreSessionRequest;
use App\Http\Requests\V1\Settings\StorescheduleRequest;

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
}
