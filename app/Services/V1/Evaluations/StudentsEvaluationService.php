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

    // التحقق من الصلاحية
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

    // إعدادات التقييم للمادة (مخزنة بالقيم الإنجليزية: exam, homework ...)
    $settings = SubjectEvaluationSetting::where('subject_id', $subject->id)
        ->get()
        ->keyBy('evaluation_type');

    DB::transaction(function () use ($data, $user, $subject, $settings) {

        foreach ($data['grades'] as $grade) {

            foreach ($grade['evaluations'] as $evaluation) {

                // ==========================
                // 1️⃣ تحويل النوع من عربي → Enum
                // ==========================
                try {
                    $type = EvaluationType::fromArabic($evaluation['evaluation_type']);
                } catch (\InvalidArgumentException $e) {
                    throw ValidationException::withMessages([
                        'evaluation_type' => ['نوع التقييم غير صالح']
                    ]);
                }

                // منع الحضور والمشاركة يدويًا
                if (in_array($type, [EvaluationType::ATTENDANCE, EvaluationType::PARTICIPATION])) {
                    throw ValidationException::withMessages([
                        'evaluation_type' => ['لا يمكن إضافة تقييم الحضور أو المشاركة يدويًا']
                    ]);
                }

                // ==========================
                // 2️⃣ التحقق من إعدادات المادة
                // ==========================
                // المفتاح هنا هو value (exam / homework)
                $setting = $settings->get($type->label());

                if (!$setting) {
                    throw ValidationException::withMessages([
                        'evaluation_type' => ['نوع التقييم غير معرف لهذه المادة']
                    ]);
                }

                // ==========================
                // 3️⃣ التحقق حسب نوع التقييم
                // ==========================
                if ($type === EvaluationType::EXAM) {
                    if (!array_key_exists('score', $evaluation)) {
                        throw ValidationException::withMessages([
                            'score' => ['الدرجة مطلوبة للاختبار']
                        ]);
                    }

                    if ($setting->max_score !== null && $evaluation['score'] > $setting->max_score) {
                        throw ValidationException::withMessages([
                            'score' => ["الدرجة ({$evaluation['score']}) تتجاوز الحد الأعلى ({$setting->max_score})"]
                        ]);
                    }
                }

                if ($type === EvaluationType::HOMEWORK) {
                    if (!array_key_exists('is_completed', $evaluation)) {
                        throw ValidationException::withMessages([
                            'is_completed' => ['يجب تحديد حالة إنجاز الواجب']
                        ]);
                    }

                    // تحويل القيمة إلى boolean
                    $evaluation['is_completed'] = (bool) $evaluation['is_completed'];
                }

                // ==========================
                // 4️⃣ max_count (إن وجد)
                // ==========================
                if ($setting->max_count) {

                    if (!array_key_exists('evaluation_number', $evaluation)) {
                        throw ValidationException::withMessages([
                            'evaluation_number' => ['يجب تحديد رقم التقييم لهذا النوع']
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
                        'student_id'       => $grade['student_id'],
                        'subject_id'       => $subject->id,
                        'evaluation_type'  => $type->value,
                        'evaluation_number'=> $evaluation['evaluation_number'],
                    ])->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
                            'evaluation_number' => ['تم تسجيل هذا التقييم مسبقًا بنفس الرقم']
                        ]);
                    }
                }

                // ==========================
                // 5️⃣ إنشاء التقييم
                // ==========================
                Evaluation::create([
                    'student_id'        => $grade['student_id'],
                    'subject_id'        => $subject->id,
                    'teacher_id'        => $user->id,
                    'evaluation_type'   => $type->value,
                    'evaluation_number' => $evaluation['evaluation_number'] ?? null,
                    'evaluation_date'   => $evaluation['evaluation_date'] ?? now()->toDateString(),
                    'score'             => $type === EvaluationType::EXAM ? $evaluation['score'] : null,
                    'is_completed'      => $type === EvaluationType::HOMEWORK ? $evaluation['is_completed'] : null,
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
        'evaluation_type' => $type->label()
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

    // التحقق من رقم التقييم إذا كان max_count معرف
    if ($setting->max_count) {
        if (!isset($data['evaluation_number'])) {
            throw ValidationException::withMessages([
                'evaluation_number' => [
                    'يجب تمرير رقم التقييم لهذا النوع من التقييم'
                ]
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

    // تحديث التقييم
    $evaluation->update([
        'score'            => $data['score'] ?? $evaluation->score,
        'evaluation_number'=> $data['evaluation_number'] ?? $evaluation->evaluation_number,
        'evaluation_date'  => $data['evaluation_date'] ?? $evaluation->evaluation_date,
    ]);
}


    /**
     * جلب الدرجات
     */
    public function getGrades(array $filters = [], $user = null): Collection|LengthAwarePaginator
    {
        $query = Evaluation::with([
            'student:id,full_name,gender,grade',
            'subject:id,name,teacher_id'
        ])->filter($filters, $user);

        // إذا تم تمرير student_id فقط نعيد كل النتائج
        return $filters['student_id'] ?? null
            ? $query->get()
            : $query->paginate($filters['per_page'] ?? 10);
    }
}
