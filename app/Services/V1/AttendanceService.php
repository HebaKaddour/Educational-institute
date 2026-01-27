<?php

namespace App\Services\V1;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\Evaluation;
use App\Models\EvaluationType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\AttendanceResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Auth\Access\AuthorizationException;


class AttendanceService
{
  public function storeDailyAttendance(array $data)
{
    $user = auth()->user();
    if (!$user) {
        throw new AuthorizationException('User not authenticated.');
    }

    $subject = null;
    if (!empty($data['subject_id'])) {
        $subject = Subject::select('id', 'teacher_id')->findOrFail($data['subject_id']);
        if ($user->hasRole('teacher') && $subject->teacher_id !== $user->id) {
            throw new AuthorizationException('غير مصرح لك بإدارة حضور هذه المادة');
        }
    }

    $students = Student::whereIn(
        'id',
        collect($data['students'])->pluck('student_id')
    )->get()->keyBy('id');

    $attendanceIds = [];

    DB::transaction(function () use ($data, $students, $subject, &$attendanceIds) {
        foreach ($data['students'] as $index => $item) {
            $student = $students[$item['student_id']] ?? null;

            if (!$student) {
                throw ValidationException::withMessages([
                    "students.$index.student_id" => 'الطالب غير موجود'
                ]);
            }

            $workingDay = \App\Models\WorkingDay::where('day_name', $data['day'])
                ->where('gender', $student->gender)
                ->first();

            if (!$workingDay) {
                throw ValidationException::withMessages([
                    "students.$index.student_id" => "لا يمكن تسجيل حضور {$student->full_name} في يوم {$data['day']} للجنس {$student->gender}"
                ]);
            }

            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'date'       => $data['date'],
                    'subject_id' => $subject?->id,
                ],
                [
                    'week'          => $data['week'],
                    'day'           => $data['day'],
                    'status'        => $item['status'],
                    'participation' => $item['participation'] ?? false,
                ]
            );

            $attendanceIds[] = $attendance->id;
        }
    });

    // إعادة Eloquent Collection مع العلاقات جاهزة
    return Attendance::with(['student', 'subject', 'evaluations.evaluationType'])
        ->whereIn('id', $attendanceIds)

        ->get();
}

   /**
 * تعديل الحضور والمشاركة
 */
public function updateAttendance(array $data, Attendance $attendance): Attendance
{
$user = auth()->user();
    if (!$user) {
        throw new AuthorizationException('User not authenticated.');
    }

    DB::transaction(function () use ($data, $attendance) {

        // أولاً تحديث حالة الحضور إذا موجودة
        if (array_key_exists('status', $data)) {
            $attendance->update([
                'status' => $data['status']
            ]);
        }

        // ثم تحديث المشاركة إذا موجودة
        if (array_key_exists('participation', $data)) {
            $attendance->update([
                'participation' => (bool) $data['participation']
            ]);
        }

    });

    // إعادة تحميل الحضور مع العلاقات بعد التحديث
    return $attendance->refresh()->load([
        'student',
        'subject:id,name',
        'evaluations.evaluationType'
    ]);
}
    public function daily(array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;
    return Attendance::with(['student', 'subject'])
        ->filter($filters)
        ->orderBy('student_id')
         ->paginate($perPage);

    }


// Get daily attendance report

//public function daily(array $filters = []): Collection
//{
    // 1️⃣ Load grouped statistics (FAST)
  //  $stats = Attendance::query()
    //    ->selectRaw('
      //      students.grade,
        //    students.gender,
          //  attendances.date,
            //COUNT(*) as total,
            //SUM(attendances.status = "حضور") as present,
            //SUM(attendances.status = "غياب") as absent,
            //SUM(attendances.status = "بعذر") as excused
        //')
        //->join('students', 'students.id', '=', 'attendances.student_id')
        //->filter($filters)
        //->groupBy('students.grade', 'students.gender', 'attendances.date',)
        //->orderBy('attendances.date')
        //->get()
        //->keyBy(fn ($r) => "{$r->grade}-{$r->gender}-{$r->date}");

    // 2️⃣ Load attendance models (for students list)
    //$attendances = Attendance::with(['student:id,full_name,gender,grade,section','subject:id,name'])
      //  ->filter($filters)
        //->orderBy('date')
        //->orderBy('student_id')
        //->get()
        //->groupBy(fn ($a) =>
          //  "{$a->student->grade}-{$a->student->gender}-{$a->date}"
        //);

    // 3️⃣ Merge results
 //   return $attendances->map(function ($items, $key) use ($stats) {

   //     $stat = $stats[$key];

     //   return [
         //   'grade' => $stat->grade,
       //     'group' => $stat->gender,
           // 'day'   => $stat->date,

            //'statistics' => [
                //'total' => $stat->total,
              //  'present' => $stat->present,
                //'absent' => $stat->absent,
                //'excused' => $stat->excused,
                //'attendance_rate' => $stat->total
                  //  ? round(($stat->present / $stat->total) * 100, 2)
                   // : 0,
            //],

            //'students' => AttendanceResource::collection($items),
        //];
    //})->values();
//}

    /**
     * حذف الحضور والمشاركة
     */
  public function deleteAttendance(Attendance $attendance): void
    {
        $user = auth()->user();
        if (!$user) {
            throw new AuthorizationException('User not authenticated.');
        }

        DB::transaction(function () use ($attendance) {

            // احصل على نوعي التقييم: حضور ومشاركة
            $attendanceType    = EvaluationType::where('label', 'الحضور')->first();
            $participationType = EvaluationType::where('label', 'المشاركة')->first();

            // حذف تقييم الحضور
            if ($attendanceType) {
                Evaluation::where([
                    'student_id'         => $attendance->student_id,
                    'subject_id'         => $attendance->subject_id,
                    'evaluation_type_id' => $attendanceType->id,
                    'evaluation_date'    => $attendance->date,
                ])->delete();
            }

            // حذف تقييم المشاركة
            if ($participationType) {
                Evaluation::where([
                    'student_id'         => $attendance->student_id,
                    'subject_id'         => $attendance->subject_id,
                    'evaluation_type_id' => $participationType->id,
                    'evaluation_date'    => $attendance->date,
                ])->delete();
            }

            // حذف سجل الحضور نفسه
            $attendance->delete();
        });
    }

}
