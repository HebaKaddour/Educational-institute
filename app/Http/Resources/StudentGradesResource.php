<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class StudentGradesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

        public function toArray($request): array
    {
 $student = $this->pluck('student')->unique('id')->first();

        if (!$student) {
            return [];
        }

        $subjects = $this
            ->where('student_id', $student->id)
            ->groupBy('subject_id')
            ->map(function ($evaluations) {

                $byType = $evaluations->groupBy(
                    fn ($evaluation) => $evaluation->evaluationType->label
                );

                $attendance    = $byType->get('الحضور')?->sum('score') ?? 0;
                $participation = $byType->get('المشاركة')?->sum('score') ?? 0;
                $homework      = $byType->get('الواجبات')?->sum('score') ?? 0;
                $exams         = $byType->get('الاختبارات')?->pluck('score') ?? collect();

                $total = $attendance + $participation + $homework + $exams->sum();

                return [
                    'subject_name' => $evaluations->first()->subject->name,
                    'attendance'   => $attendance,
                    'participation'=> $participation,
                    'homework'     => $homework,
                    'exams'        => $exams->values(),
                    'total'        => $total,
                    'grade'        => $this->grade($total),
                ];
            })->values();

        return [
            'student_id'   => $student->id,
            'student_class' => $student->grade,
            'student_name' => $student->full_name,
            'subjects'     => $subjects,
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
