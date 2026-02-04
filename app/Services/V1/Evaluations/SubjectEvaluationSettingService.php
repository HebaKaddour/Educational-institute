<?php
namespace App\Services\V1\Evaluations;
use App\Models\Subject;
use App\Enums\EvaluationType;
use App\Models\SubjectEvaluationSetting;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class SubjectEvaluationSettingService
{
 private function authorize($user, Subject $subject): void
    {
        if ($user->hasRole('admin')) {
            return;
        }

        if (
            $user->hasRole('teacher') &&
            $subject->teacher_id === $user->id
        ) {
            return;
        }

        throw new AuthorizationException(
            'غير مصرح لك بضبط تقييمات هذه المادة'
        );
    }

public function create(int $subjectId, array $data): SubjectEvaluationSetting
    {
        $user = auth('sanctum')->user();

        $subject = Subject::findOrFail($subjectId);

        $this->authorize($user, $subject);
        $type = EvaluationType::fromArabic($data['evaluation_type']);

        $this->validateByType($type, $data);

    $exists = SubjectEvaluationSetting::where('subject_id', $subject->id)
        ->where('evaluation_type', $data['evaluation_type'])
        ->exists();

    if ($exists) {
        throw ValidationException::withMessages([
            'evaluation_type' => "إعدادات تقييم ({$data['evaluation_type']}) للمادة ({$subject->name}) موجودة مسبقاً"
        ]);
    }

        return SubjectEvaluationSetting::create([
            'subject_id'         => $subject->id,
            'evaluation_type' => $data['evaluation_type'],
            'max_score'          => $data['max_score'] ?? null,
            'max_count'          => $data['max_count'] ?? null,
        ]);
    }
    public function update(SubjectEvaluationSetting $subjectevaluationsetting, array $data): SubjectEvaluationSetting
    {
        $user = auth('sanctum')->user();
        $this->authorize($user, $subjectevaluationsetting->subject);

        if (isset($data['evaluation_type'])) {
            $type = EvaluationType::fromArabic($data['evaluation_type']);
        } else {
            $type = EvaluationType::fromArabic($subjectevaluationsetting->evaluation_type);
        }

        $this->validateByType($type, $data, true);

        $subjectevaluationsetting->update([
            'max_count' => $data['max_count'] ?? $subjectevaluationsetting->max_count,
            'max_score' => $data['max_score'] ?? $subjectevaluationsetting->max_score,
        ]);

        return $subjectevaluationsetting;
    }

    public function delete(SubjectEvaluationSetting $subjectevaluationsetting): void
    {

       $user = auth('sanctum')->user();
        $this->authorize($user, $subjectevaluationsetting->subject);

        $subjectevaluationsetting->delete();
    }

    private function validateByType(EvaluationType $type,array $data,bool $isUpdate = false): void {
        match ($type) {

            // -------------------------
            // الحضور والمشاركة
            // -------------------------
            EvaluationType::ATTENDANCE,
            EvaluationType::PARTICIPATION
                => $this->validateNoExtraFields($data),

            // -------------------------
            // الواجبات
            // -------------------------
            EvaluationType::HOMEWORK
                => $this->validateHomework($data, $isUpdate),

            // -------------------------
            // الاختبارات
            // -------------------------
            EvaluationType::EXAM
                => $this->validateExam($data, $isUpdate),
        };
    }

    private function validateNoExtraFields(array $data): void
    {
        if (!empty($data['max_count'])) {
            throw ValidationException::withMessages([
                'evaluation_type' =>
                    'هذا النوع لا يقبل إعدادات إضافية'
            ]);
        }
    }

    private function validateHomework(array $data, bool $isUpdate): void
    {
        if (!$isUpdate && empty($data['max_count'])) {
            throw ValidationException::withMessages([
                'max_count' => 'عدد الواجبات مطلوب'
            ]);
        }

        if (isset($data['max_score'])) {
            throw ValidationException::withMessages([
                'max_score' => 'الواجبات لا تستخدم max_score'
            ]);
        }
    }

    private function validateExam(array $data, bool $isUpdate): void
    {
        if (!$isUpdate && empty($data['max_score'])) {
            throw ValidationException::withMessages([
                'max_score' => 'الدرجة القصوى للاختبار مطلوبة'
            ]);
        }

        if (!isset($data['max_count'])) {
            throw ValidationException::withMessages([
                'max_count' => 'الاختبارات تحتاج لتحديد قيمة max_count'
            ]);
        }
    }
}
