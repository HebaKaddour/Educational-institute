<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'week' => $this->week,
            'day' => $this->day,
            'status' => $this->status,

            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->full_name,
                'gender' => $this->student->gender,
                'grade' => $this->student->grade,
            ],

            'subject' => $this->subject
                ? [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                ]
                : null,
        ];
    }

    public static function collection($resource)
    {
        return parent::collection($resource);
    }


}
