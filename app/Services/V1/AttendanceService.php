<?php

namespace App\Services\V1;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Attendance;
use Illuminate\Auth\Access\AuthorizationException;


class AttendanceService
{
public function create(array $data)
    {
        // check if the user is authorized to manage attendance for the subject
        if (
                auth()->user()->hasRole('teacher') &&! auth()->user()->subjects()
        ->where('subjects.id', $data['subject_id'])->exists()
        ) {
            throw new AuthorizationException('غير مصرح لك بإدارة حضور هذا المقرر الدراسي');
        }

        // Create or update attendance record
        return Attendance::updateOrCreate(
            [
                'student_id' => $data['student_id'],
                'subject_id' => $data['subject_id'],
                'week' => $data['week'],
                'day' => $data['day'],
            ],
            [
                'status' => $data['status']
            ]
        );
    }
public function list(array $filters)
{
    $query = Attendance::query()
        ->with([
            'student:id,full_name',
            'subject:id,name'
        ]);

    if (!empty($filters['student_id'])) {
        $query->where('student_id', $filters['student_id']);
    }

    if (!empty($filters['subject_id'])) {
        $query->where('subject_id', $filters['subject_id']);
    }

    if (!empty($filters['week'])) {
        $query->where('week', $filters['week']);
    }

    if (!empty($filters['day'])) {
        $query->where('day', $filters['day']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    return $query
        ->orderBy('week', 'desc')
        ->orderBy('day')
        ->get();

        if (!empty($filters['week'])) {
        return $this->groupByWeek($attendances);
    }
    return $attendances;
}

private function groupByWeek($attendances)
{
    return $attendances
        ->groupBy('week')
        ->map(function ($weekAttendances, $weekNumber) {

            $first = $weekAttendances->first();

            return [
                'week' => $weekNumber,
                'student' => $first->student,
                'subject' => $first->subject,
                'days' => $weekAttendances->map(fn ($a) => [
                    'day' => $a->day,
                    'status' => $a->status,
                ])->values()
            ];
        })
        ->values();
}

}
