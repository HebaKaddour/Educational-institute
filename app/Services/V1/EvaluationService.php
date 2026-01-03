<?php
namespace App\Services\V1;
use App\Models\Evaluation;
use App\Models\EvaluationType;
use Illuminate\Validation\ValidationException;

class EvaluationService
{
public function create(array $data)
{
    $type = EvaluationType::findOrFail($data['evaluation_type_id']);

    // 1️⃣ التحقق من وجود الدرجة
    if ($type->uses_score && !array_key_exists('score', $data)) {
        throw ValidationException::withMessages([
            'score' => ['هذا التقييم يتطلب إدخال درجة']
        ]);
    }

    // 2️⃣ التحقق من وجود الحالة
    if ($type->uses_status && !array_key_exists('status', $data)) {
        throw ValidationException::withMessages([
            'status' => ['هذا التقييم يتطلب تحديد الحالة']
        ]);
    }

    // 3️⃣ التحقق من الحد الأعلى للدرجة
    if ($type->uses_score && $type->max_score !== null) {
        $this->validateScoreLimit($type->max_score, $data['score']);
    }

    return Evaluation::create([
        ...$data,
        'teacher_id' => auth()->id(),
    ]);
}

private function validateScoreLimit(int $maxScore, int $score): void
{
    if ($score > $maxScore) {
        throw ValidationException::withMessages([
            'score' => ["العلامة القصوى لهذا التقييم هي {$maxScore}"]
        ]);
    }
}
}
