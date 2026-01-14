<?php

namespace App\Http\Controllers\Api\V1\Evaluations;

use App\Models\Student;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentGradesResource;
use App\Http\Resources\StudentsGradesCollection;
use Illuminate\Auth\Access\AuthorizationException;
use App\Services\V1\Evaluations\StudentsEvaluationService;
use App\Http\Requests\V1\Evaluations\UpdateEvaluationRequest;
use App\Http\Requests\V1\Evaluations\StoreEvaluationStudentsRequest;

class StudentEvaluationsController extends Controller
{
    public function __construct(private StudentsEvaluationService $studentsEvaluationService) {}


    public function store(StoreEvaluationStudentsRequest $request)
    {
        return self::success($this->studentsEvaluationService->addEvaluationForStudent($request->validated()), 'تم حفظ التقييمات بنجاح', 201);
    }

     public function update(UpdateEvaluationRequest $request,Evaluation $evaluation) {
        return self::success($this->studentsEvaluationService->updateEvaluationForStudent($evaluation,$request->validated()), 'تم تعديل الدرجة بنجاح');
    }

public function allGrades(Request $request)
{
    $evaluations = $this->studentsEvaluationService->getGrades(
        filters: $request->only([
            'student_id',
            'subject_id',
            'grade',
            'gender',
        ])
    );

    // طالب واحد
    if ($request->filled('student_id')) {
        return self::success(
            StudentGradesResource::make($evaluations),
            'درجات الطالب'
        );
    }

    // عدة طلاب
    return self::success(
        StudentsGradesCollection::make($evaluations),
        'درجات الطلاب'
    );
}

    public function deleteGrades(Evaluation $evaluation)
    {

       $user = auth('sanctum')->user();
        $subject = $evaluation->subject;

    if (
        $user->hasRole('teacher') &&
        $subject->teacher_id !== $user->id
    ) {
        throw new AuthorizationException(
            'غير مصرح لك بحذف درجات هذا المقرر الدراسي'
        );
    }
        $evaluation->delete();

        return self::success(null, 'تم حذف التقييم بنجاح');
    }

}
