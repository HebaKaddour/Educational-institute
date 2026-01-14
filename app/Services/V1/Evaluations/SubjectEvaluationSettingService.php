<?php
namespace App\Services\V1\Evaluations;
use App\Models\Subject;
use App\Models\EvaluationType;
use App\Models\SubjectEvaluationSetting;
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

        return SubjectEvaluationSetting::create([
            'subject_id'         => $subject->id,
            'evaluation_type_id' => $data['evaluation_type_id'],
            'max_score'          => $data['max_score'],
            'max_count'          => $data['max_count'] ?? null,
        ]);
    }
    public function update(SubjectEvaluationSetting $subjectevaluationsetting, array $data): SubjectEvaluationSetting
    {
        $user = auth('sanctum')->user();
        $this->authorize($user, $subjectevaluationsetting->subject);
        $subjectevaluationsetting->update($data);
        return $subjectevaluationsetting;
    }

    public function delete(SubjectEvaluationSetting $subjectevaluationsetting): void
    {

       $user = auth('sanctum')->user();
        $this->authorize($user, $subjectevaluationsetting->subject);

        $subjectevaluationsetting->delete();
    }
}
