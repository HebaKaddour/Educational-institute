<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Enums\EvaluationType;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentGradesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray($request): array
    {
        // الحصول على الطالب من أول تقييم
        $student = $this->first()->student;

        if (!$student) {
            return [];
        }

        // نجمع التقييمات لكل مادة
        $subjects = $this->groupBy('subject_id')->map(function ($evaluations) {

            $subjectTotals = [];
            $totalScore = 0;

            foreach (EvaluationType::cases() as $type) {
                $label = $type->label();

                // احصل على جميع التقييمات من نفس النوع
                $scores = $evaluations->filter(fn($e) => $e->evaluation_type === $type->value)->pluck('score');

                // إذا كان هناك تقييم واحد نرجع القيمة مباشرة، إذا أكثر نرجع Collection
                $subjectTotals[$label] = $scores->count() > 1 ? $scores->values() : ($scores->first() ?? 0);

                $totalScore += $scores->sum();
            }

            return [
                'subject_name' => $evaluations->first()->subject->name,
                'scores'       => $subjectTotals,
                'total'        => $totalScore,
                'grade'        => $this->grade($totalScore),
            ];
        })->values();

        return [
            'student_id'    => $student->id,
            'student_class' => $student->grade,
            'student_name'  => $student->full_name,
            'subjects'      => $subjects,
        ];
    }

    private function grade(int $total): string
    {
        return match (true) {
            $total >= 90 => 'A',
            $total >= 80 => 'B',
            $total >= 70 => 'C',
            $total >= 60 => 'D',
            default      => 'F',
        };
    }
}
