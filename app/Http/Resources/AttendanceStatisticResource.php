<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceStatisticResource extends JsonResource
{
public function toArray($request)
    {
        return [
            'student_id' => $this->student->id ?? null,
            'name'       => $this->student->full_name ?? null,
            'gender'     => $this->student->gender ?? null,
            'grade'      => $this->student->grade ?? null,
            'section'    => $this->student->section ?? null,
            'attendance' => [
                'status'        => $this->status,
                'subject_name' => $this->subject->name,
                'participation' => (bool) $this->participation,
            ],
        ];
    }
}
