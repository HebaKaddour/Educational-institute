<?php

namespace App\Services\V1;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Auth\Access\AuthorizationException;


class AttendanceService
{
   public function storeDailyAttendance(array $data): void
    {
        $user = auth()->user();

       if (!empty($data['subject_id'])) {

            $subject = Subject::findOrFail($data['subject_id']);

            if (
                $user->hasRole('teacher') &&
                $subject->teacher_id !== $user->id
            ) {
                throw new AuthorizationException(
                    'غير مصرح لك بإدارة حضور هذه المادة'
                );
            }
        }

        DB::transaction(function () use ($data) {
            foreach ($data['students'] as $item) {
                Attendance::updateOrCreate(
                    [
                        'student_id'      => $item['student_id'],
                        'date' => $data['date'],
                         'subject_id' => $data['subject_id'] ?? null,
                    ],
                    [

                        'week'   => $data['week'],
                        'day'    => $data['day'],
                        'status' => $item['status'],
                    ]
                );
            }
        });

    }

// Get daily attendance report
public function list(array $filters = []): Collection
    {
        $query = Attendance::query()
            ->with([
                'student:id,full_name,gender,grade',
                'subject:id,name'
            ]);


        if (array_key_exists('subject_id', $filters)) {
            $filters['subject_id'] === null
                ? $query->whereNull('subject_id')
                : $query->where('subject_id', $filters['subject_id']);
        }


        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }
        if (!empty($filters['week'])) {
            $query->where('week', $filters['week']);
        }
        if (!empty($filters['grade'])) {
            $query->whereHas('student', fn($q) => $q->where('grade', $filters['grade']));
        }
        if (!empty($filters['gender'])) {
            $query->whereHas('student', fn($q) => $q->where('gender', $filters['gender']));
        }


        $attendances = $query->get()->sortBy([
            fn($a, $b) => $a->student->grade <=> $b->student->grade,
            fn($a, $b) => $a->student->gender <=> $b->student->gender,
            fn($a, $b) => $a->student->full_name <=> $b->student->full_name,
        ])->values();

        return $attendances;
    }


    public function statistics(array $filters = []): array
    {
        $attendances = $this->list($filters);

        $total = $attendances->count();

        return [
            'total' => $total,
            'present' => $attendances->where('status', 'حضور')->count(),
            'absent' => $attendances->where('status', 'غياب')->count(),
            'excused' => $attendances->where('status', 'بعذر')->count(),
            'attendance_rate' => $total ? round(($attendances->where('status', 'حضور')->count() / $total) * 100, 2) : 0,
        ];
    }

}
