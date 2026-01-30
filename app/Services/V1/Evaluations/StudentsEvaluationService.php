<?php

namespace App\Services\V1\Evaluations;
use App\Models\Subject;
use App\Models\Evaluation;
use App\Enums\EvaluationType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\SubjectEvaluationSetting;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class StudentsEvaluationService
{
public function addEvaluationForStudent(array $data): void
    {
        $user = auth('sanctum')->user();

        $subject = Subject::findOrFail($data['subject_id']);

if (
    !$user->hasRole('admin') &&
    !(
        $user->hasRole('teacher') &&
        $subject->teacher_id === $user->id
    )
) {
    throw new AuthorizationException(
        'غير مصرح لك بإضافة درجات أو حضور لهذا المقرر الدراسي'
    );
}

        // إعدادات التقييم للمادة
        $settings = SubjectEvaluationSetting::where('subject_id', $subject->id)
            ->get()
            ->keyBy('evaluation_type');

        DB::transaction(function () use ($data, $user, $subject, $settings) {

            foreach ($data['grades'] as $grade) {

                foreach ($grade['evaluations'] as $evaluation) {

                    // ==========================
                    // 1️⃣ منع التقييمات التلقائية
                    // ==========================
                    $type = EvaluationType::tryFrom($evaluation['evaluation_type']);

                    if (!$type) {
                        throw ValidationException::withMessages([
                            'evaluation_type' => ['نوع التقييم غير صالح']
                        ]);
                    }

                    if (in_array($type, [
                        EvaluationType::ATTENDANCE,
                        EvaluationType::PARTICIPATION
                    ])) {
                        throw ValidationException::withMessages([
                            'evaluation_type' => [
                                'لا يمكن إضافة تقييم الحضور أو المشاركة يدويًا'
                            ]
                        ]);
                    }

                    // ==========================
                    // 2️⃣ التحقق من إعدادات المادة
                    // ==========================
                    $setting = $settings->get($type->value);

                    if (!$setting) {
                        throw ValidationException::withMessages([
                            'evaluation_type' => [
                                'نوع التقييم غير معرف لهذه المادة'
                            ]
                        ]);
                    }

                    // ==========================
                    // 3️⃣ max_score
                    // ==========================
                    if ($evaluation['score'] > $setting->max_score) {
                        throw ValidationException::withMessages([
                            'score' => [
                                "الدرجة ({$evaluation['score']}) تتجاوز الحد الأعلى ({$setting->max_score})"
                            ]
                        ]);
                    }

                    // ==========================
                    // 4️⃣ max_count
                    // ==========================
                    if ($setting->max_count) {

                        if (!isset($evaluation['evaluation_number'])) {
                            throw ValidationException::withMessages([
                                'evaluation_number' => [
                                    'يجب تحديد رقم التقييم لهذا النوع'
                                ]
                            ]);
                        }

                        if ($evaluation['evaluation_number'] > $setting->max_count) {
                            throw ValidationException::withMessages([
                                'evaluation_number' => [
                                    "رقم التقييم ({$evaluation['evaluation_number']}) يتجاوز الحد الأقصى ({$setting->max_count})"
                                ]
                            ]);
                        }

                        $exists = Evaluation::where([
                            'student_id'      => $grade['student_id'],
                            'subject_id'      => $subject->id,
                            'evaluation_type' => $type->value,
                            'evaluation_number' => $evaluation['evaluation_number'],
                        ])->exists();

                        if ($exists) {
                            throw ValidationException::withMessages([
                                'evaluation_number' => [
                                    'تم تسجيل هذا التقييم مسبقًا بنفس الرقم'
                                ]
                            ]);
                        }
                    }

                    // ==========================
                    // 5️⃣ إنشاء التقييم
                    // ==========================
                    Evaluation::create([
                        'student_id'       => $grade['student_id'],
                        'subject_id'       => $subject->id,
                        'evaluation_type'  => $type->value,
                        'score'            => $evaluation['score'],
                        'teacher_id'       => $user->id,
                        'evaluation_number'=> $evaluation['evaluation_number'] ?? null,
                        'evaluation_date'  => $evaluation['evaluation_date'] ?? now()->toDateString(),
                    ]);
                }
            }
        });
    }

    /**
     * تعديل تقييم يدوي
     * ❌ يمنع تعديل الحضور والمشاركة
     */
    public function updateEvaluationForStudent(
        Evaluation $evaluation,
        array $data
    ): void {
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

        // ==========================
        // منع التقييمات التلقائية
        // ==========================
        $type = EvaluationType::tryFrom($evaluation->evaluation_type);

        if (in_array($type, [
            EvaluationType::ATTENDANCE,
            EvaluationType::PARTICIPATION
        ])) {
            throw ValidationException::withMessages([
                'evaluation_type' => [
                    'لا يمكن تعديل تقييم الحضور أو المشاركة يدويًا'
                ]
            ]);
        }

        $setting = SubjectEvaluationSetting::where([
            'subject_id'      => $subject->id,
            'evaluation_type' => $type->value
        ])->first();

        if (!$setting) {
            throw ValidationException::withMessages([
                'evaluation_type' => [
                    'نوع التقييم غير معرف لهذه المادة'
                ]
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
        if ($setting->max_count && isset($data['evaluation_number'])) {

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
                ->where('evaluation_type', $type->value)
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
            'score'            => $data['score'] ?? $evaluation->score,
            'evaluation_number'=> $data['evaluation_number'] ?? $evaluation->evaluation_number,
            'evaluation_date'  => $data['evaluation_date'] ?? $evaluation->evaluation_date,
        ]);
    }

    /**
     * جلب الدرجات
     */
    public function getGrades(array $filters = []): Collection|LengthAwarePaginator
    {
        $user = auth('sanctum')->user();

        $query = Evaluation::query()
            ->with([
                'student:id,full_name,gender,grade',
                'subject:id,name,teacher_id',
            ]);

        if ($user->hasRole('teacher')) {
            $query->whereHas('subject', fn ($q) =>
                $q->where('teacher_id', $user->id)
            );
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

        return !empty($filters['student_id'])
            ? $query->get()
            : $query->paginate(request('per_page', 10));
    }
}
