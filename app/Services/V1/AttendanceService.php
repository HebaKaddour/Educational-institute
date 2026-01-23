<?php

namespace App\Services\V1;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Attendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AttendanceResource;
use Illuminate\Auth\Access\AuthorizationException;


class AttendanceService
{
   public function storeDailyAttendance(array $data): void
    {
        $user = auth()->user();
        if (!$user) {
        throw new AuthorizationException('User not authenticated.');
    }
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
try {
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
           } catch (\Exception $e) {
            throw new \Exception('فشل في تسجيل الحضور: ' . $e->getMessage());
        }

    }
//update attendance
public function updateAttendance(Attendance $attendance , array $data)
{
    $user = auth()->user();
    if(!$user){
         throw new AuthorizationException('User not authenticated.');

    }
    if (!empty($data['subject_id'])) {


            $subject = Subject::findOrFail($data['subject_id']);

            if (
                $user->hasRole('teacher') &&
                $subject->teacher_id !== $user->id
            ) {
                throw new AuthorizationException(
                    'غير مصرح لك بتعديل حضور هذه المادة'
                );
            }
        }
                 $attendance->update($data);
                 return $attendance;
        }
// Get daily attendance report

public function daily(array $filters = []): Collection
{
    // 1️⃣ Load grouped statistics (FAST)
    $stats = Attendance::query()
        ->selectRaw('
            students.grade,
            students.gender,
            attendances.date,
            COUNT(*) as total,
            SUM(attendances.status = "حضور") as present,
            SUM(attendances.status = "غياب") as absent,
            SUM(attendances.status = "بعذر") as excused
        ')
        ->join('students', 'students.id', '=', 'attendances.student_id')
        ->filter($filters)
        ->groupBy('students.grade', 'students.gender', 'attendances.date')
        ->orderBy('attendances.date')
        ->get()
        ->keyBy(fn ($r) => "{$r->grade}-{$r->gender}-{$r->date}");

    // 2️⃣ Load attendance models (for students list)
    $attendances = Attendance::with(['student:id,full_name,gender,grade'])
        ->filter($filters)
        ->orderBy('date')
        ->orderBy('student_id')
        ->get()
        ->groupBy(fn ($a) =>
            "{$a->student->grade}-{$a->student->gender}-{$a->date}"
        );

    // 3️⃣ Merge results
    return $attendances->map(function ($items, $key) use ($stats) {

        $stat = $stats[$key];

        return [
            'grade' => $stat->grade,
            'group' => $stat->gender,
            'day'   => $stat->date,

            'statistics' => [
                'total' => $stat->total,
                'present' => $stat->present,
                'absent' => $stat->absent,
                'excused' => $stat->excused,
                'attendance_rate' => $stat->total
                    ? round(($stat->present / $stat->total) * 100, 2)
                    : 0,
            ],

            'students' => AttendanceResource::collection($items),
        ];
    })->values();
}

public function deleteAttendance(Attendance $attendance): void
{
    $user = auth()->user();

    if (
        $user->hasRole('teacher') &&
        $attendance->subject &&
        $attendance->subject->teacher_id !== $user->id
    ) {
        throw new AuthorizationException(
            'غير مصرح لك بحذف حضور هذه المادة'
        );
    }

    $attendance->delete();
}

}
