<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'week' => $this->week,
            'day' => $this->day,
            'status' => $this->status,
            'participation' => (bool) $this->participation,

            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->full_name,
                'gender' => $this->student->gender,
                'grade' => $this->student->grade,
                'section' => $this->student->section,
            ],

            'subject' => $this->whenLoaded('subject', function () {
                return [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                ];
            }),

            'evaluations' => $this->whenLoaded('evaluations', function () {
                return $this->evaluations
                    ->filter(fn($eval) =>
                        $eval->subject_id == $this->subject_id &&
                        Carbon::parse($eval->evaluation_date)->toDateString() === Carbon::parse($this->date)->toDateString()
                    )
                    ->map(fn($eval) => [
                        'id' => $eval->id,
                        'type' => $eval->evaluationType->label ?? null,
                        'score' => $eval->score,
                        'date' => $eval->evaluation_date,
                    ])
                    ->values();
            }),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
