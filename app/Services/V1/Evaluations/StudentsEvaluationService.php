<?php

namespace App\Services\V1\Evaluations;
use App\Models\Subject;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Evaluation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\SubjectEvaluationSetting;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;


class StudentsEvaluationService
{
    public function addEvaluationForStudent(array $data)
    {

    $user = auth('sanctum')->user();

    $subject = Subject::findOrFail($data['subject_id']);

    if ($subject->teacher_id !== $user->id) {
        throw new AuthorizationException(
            'غير مصرح لك بإضافة درجات لهذا المقرر الدراسي'
        );
    }

    $settings = SubjectEvaluationSetting::where('subject_id', $subject->id)
        ->get()
        ->keyBy('evaluation_type_id');

    DB::transaction(function () use ($data, $user, $subject, $settings) {

        foreach ($data['grades'] as $grade) {

            foreach ($grade['evaluations'] as $evaluation) {

                $setting = $settings->get($evaluation['evaluation_type_id']);

                if (!$setting) {
                      throw ValidationException::withMessages([
                        'evaluation_type_id' => ['نوع التقييم غير معرف لهذه المادة']]);
                }


                if ($evaluation['score'] > $setting->max_score) {

                     throw ValidationException::withMessages([
                        'max_score' => ["الدرجة ({$evaluation['score']}) تتجاوز الحد الأعلى ({$setting->max_score})"]]);
                }

                if ($setting->max_count) {
                    if (!isset($evaluation['evaluation_number'])) {
                 throw ValidationException::withMessages([
                'evaluation_number' => ['يجب تحديد رقم التقييم لهذا النوع لأنه يملك حد أقصى للتقييمات']
            ]);
        }

                if ($evaluation['evaluation_number'] > $setting->max_count) {

                    throw ValidationException::withMessages([
    'evaluation_number' => [
        "رقم التقييم المدخل ({$evaluation['evaluation_number']}) غير صالح، الحد الأقصى المسموح هو ({$setting->max_count})"
    ]
]);

                    }

                    $exists = Evaluation::where([
                        'student_id' => $grade['student_id'],
                        'subject_id' => $subject->id,
                        'evaluation_type_id' => $evaluation['evaluation_type_id'],
                        'evaluation_number' => $evaluation['evaluation_number'],
                    ])->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
    'evaluation_number' => [
        "تم تسجيل هذا التقييم مسبقًا لهذا الطالب في هذه المادة، لا يمكن إضافة تقييم مكرر بنفس الرقم"
    ]
]);
                    }
                }

                Evaluation::create([
                    'student_id' => $grade['student_id'],
                    'subject_id' => $subject->id,
                    'evaluation_type_id' => $evaluation['evaluation_type_id'],
                    'score' => $evaluation['score'],
                    'teacher_id' => $user->id,
                    'evaluation_number' => $evaluation['evaluation_number'] ?? null,
                    'evaluation_date' => $evaluation['evaluation_date'] ?? now()->toDateString(),
                ]);
            }
        }
    });
 }


 //update evaluation
public function updateEvaluationForStudent(Evaluation $evaluation, array $data): void
{
    $user = auth('sanctum')->user();
    $subject = $evaluation->subject;

    if (
        $user->hasRole('teacher') &&
        $subject->teacher_id !== $user->id
    ) {
        throw new AuthorizationException(
            'غير مصرح لك بتعديل درجات هذا المقرر الدراسي'
        );
    }

    $setting = SubjectEvaluationSetting::where([
        'subject_id' => $subject->id,
        'evaluation_type_id' => $evaluation->evaluation_type_id
    ])->first();

    if (!$setting) {
        throw ValidationException::withMessages([
            'evaluation_type_id' => ['نوع التقييم غير معرف لهذه المادة']
        ]);
    }

    // max_score
    if (isset($data['score']) && $data['score'] > $setting->max_score) {
        throw ValidationException::withMessages([
            'score' => [
                "الدرجة ({$data['score']}) تتجاوز الحد الأعلى ({$setting->max_score})"
            ]
        ]);
    }

    // max_count
    if ($setting->max_count) {

        if (!isset($data['evaluation_number'])) {
            throw ValidationException::withMessages([
                'evaluation_number' => ['يجب تحديد رقم التقييم لهذا النوع']
            ]);
        }

        if ($data['evaluation_number'] > $setting->max_count) {
            throw ValidationException::withMessages([
                'evaluation_number' => [
                    "رقم التقييم ({$data['evaluation_number']}) يتجاوز الحد الأقصى ({$setting->max_count})"
                ]
            ]);
        }

        $exists = Evaluation::where('id', '!=', $evaluation->id)
            ->where('student_id', $evaluation->student_id)
            ->where('subject_id', $subject->id)
            ->where('evaluation_type_id', $evaluation->evaluation_type_id)
            ->where('evaluation_number', $data['evaluation_number'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'evaluation_number' => [
                    'يوجد تقييم آخر بنفس الرقم لهذا الطالب'
                ]
            ]);
        }
    }

    $evaluation->update([
        'score' => $data['score'] ?? $evaluation->score,
        'evaluation_number' => $data['evaluation_number'] ?? $evaluation->evaluation_number,
        'evaluation_date' => $data['evaluation_date'] ?? $evaluation->evaluation_date,
    ]);
}

 public function getGrades(array $filters = []) : Collection|LengthAwarePaginator
{
    $user = auth('sanctum')->user();


    $query = Evaluation::query()
        ->with([
            'student:id,full_name,gender,grade',
            'subject:id,name,teacher_id',
            'evaluationType:id,label'
        ]);

   if ($user->hasRole('teacher')) {

            $query->whereHas('subject', fn ($q) =>
                $q->where('teacher_id', $user->id)
            );

            if (!empty($filters['subject_id'])) {
                $subject = Subject::find($filters['subject_id']);

                if (!$subject) {
                    throw ValidationException::withMessages([
                        'subject_id' => ['المادة المحددة غير موجودة'],
                    ]);
                }

                if ($subject->teacher_id !== $user->id) {
                    throw new AuthorizationException(
                        'غير مصرح لك بعرض درجات هذه المادة'
                    );
                }
            }
        }

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (!empty($filters['grade'])) {
            $query->whereHas('student', fn ($q) =>
                $q->where('grade', $filters['grade'])
            );
        }

        if (!empty($filters['gender'])) {
            $query->whereHas('student', fn ($q) =>
                $q->where('gender', $filters['gender'])
            );
        }

        if (!empty($filters['student_id'])) {
            return $query->get();
        }

        return $query->paginate(
            perPage: request('per_page', 10)
        );
}

}
