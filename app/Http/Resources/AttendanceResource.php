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
            'week' => $this->week,
            'day' => $this->day,
            'status' => $this->status,

            'student' => [
                'id' => $this->student->id,
                'name' => $this->student->full_name,
            ],

            'subject' => [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ],
        ];
    }

    public static function collection($resource)
    {
        $request = request();

       // student + subject + week
        if ($request->filled(['student_id', 'subject_id', 'week'])) {
            return parent::collection($resource);
        }

        // student + subject
        if ($request->filled(['student_id', 'subject_id'])) {
            return $resource->groupBy('subject_id')->map(function ($items) {
                return [
                    'subject' => $items->first()->subject->name,
                    'attendance' => parent::collection($items),
                ];
            })->values();
        }

        // student only
        if ($request->filled('student_id')) {
            return $resource->groupBy('week')->map(function ($items, $week) {
                return [
                    'week' => $week,
                    'days' => parent::collection($items),
                ];
            })->values();
        }

        // subject only
        if ($request->filled('subject_id')) {
            return $resource->groupBy('week')->map(function ($items, $week) {
                return [
                    'week' => $week,
                    'students' => parent::collection($items),
                ];
            })->values();
        }

        // week only
        if ($request->filled('week')) {
            return $resource->groupBy('subject_id')->map(function ($items) {
                return [
                    'subject' => $items->first()->subject->name,
                    'attendance' => parent::collection($items),
                ];
            })->values();
        }

        return parent::collection($resource);
    }
}
