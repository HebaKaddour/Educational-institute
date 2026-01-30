<?php
namespace App\Http\Controllers\Api\V1\Evaluations;
use App\Models\Subject;
use App\Http\Controllers\Controller;
use App\Models\SubjectEvaluationSetting;
use App\Http\Resources\SubjectEvaluationSettingResource;
use App\Services\V1\Evaluations\SubjectEvaluationSettingService;
use App\Http\Requests\V1\Evaluations\StoreSubjectEvaluationSettingRequest;
use App\Http\Requests\V1\Evaluations\UpdateSubjectEvaluationSettingRequest;

class SubjectEvaluationSettingController extends Controller
{

    public function __construct(private SubjectEvaluationSettingService $subjectEvaluationSettingService)
    {
    }

    public function index(Subject $subject)
    {
       return self::success( $subject->evaluationSettings()->get(),'إعدادات التقييمات',200);
    }

    // POST /subjects/{subject}/evaluation-settings
    public function store(StoreSubjectEvaluationSettingRequest $request, Subject $subject)
    {
        $setting = $this->subjectEvaluationSettingService->create($subject->id, $request->validated());
        return self::success($setting,'تم إنشاء إعداد التقييم بنجاح',
            201
        );
    }

    public function update(UpdateSubjectEvaluationSettingRequest $request, SubjectEvaluationSetting $subjectEvaluationSetting)
    {
       $updated_settings =  $this->subjectEvaluationSettingService->update($subjectEvaluationSetting, $request->validated());
        return self::success($updated_settings,
            'تم تحديث إعدادات التقييم للمادة المحددة بنجاح'
        );
    }

     public function destroy(SubjectEvaluationSetting $subjectEvaluationSetting)
    {
        $this->subjectEvaluationSettingService->delete($subjectEvaluationSetting);
       return self::success(message: 'تم حذف إعداد التقييم بنجاح');
    }
}
