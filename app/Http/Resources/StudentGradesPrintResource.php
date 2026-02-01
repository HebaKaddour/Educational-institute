<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentGradesPrintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray($request): array
    {
        $student = $this->first()->student;

        $subjects = $this->groupBy('subject_id')->map(function ($evaluations) {

            $byType = $evaluations->groupBy('type');

            $total = 0;
            $scores = [];

            foreach (\App\Enums\EvaluationType::cases() as $type) {
                $label = $type->label();
                $value = $byType->get($label)?->sum('score') ?? 0;

                $scores[$label] = $value;
                $total += $value;
            }

            return [
                'subject' => $evaluations->first()->subject->name,
                'scores'  => $scores,
                'total'   => $total,
                'grade' => \App\Helper\grade($total),
            ];
        })->values();

        return [
            'student_name'  => $student->full_name,
            'student_class' => $student->grade,
            'subjects'      => $subjects,
        ];
    }
}
