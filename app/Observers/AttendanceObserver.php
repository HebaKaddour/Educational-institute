<?php

namespace App\Observers;

use DB;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\Evaluation;
use App\Models\EvaluationType;

class AttendanceObserver
{
    public function created(Attendance $attendance): void
    {
        $this->handleAttendance($attendance);
    }

    public function updated(Attendance $attendance): void
    {
        $this->handleAttendance($attendance, true);
    }

    protected function handleAttendance(Attendance $attendance, bool $isUpdate = false): void
    {
        if (!$attendance->subject_id) {
            return;
        }

        $teacherId = Subject::where('id', $attendance->subject_id)->value('teacher_id');
        if (!$teacherId) return;

        $attendanceType    = EvaluationType::where('label', 'الحضور')->first();
        $participationType = EvaluationType::where('label', 'المشاركة')->first();

        // ----------------------
        // معالجة حالة الحضور
        // ----------------------
        if ($attendanceType) {
            if (in_array($attendance->status, ['حضور', 'بعذر'])) {
                // إنشاء أو تحديث تقييم الحضور
                $this->createEvaluationOnce($attendance, $attendanceType, $teacherId);
            } elseif ($attendance->status === 'غياب') {
                // حذف تقييم الحضور
                Evaluation::where([
                    'student_id' => $attendance->student_id,
                    'subject_id' => $attendance->subject_id,
                    'evaluation_type_id' => $attendanceType->id,
                    'evaluation_date' => $attendance->date,
                ])->delete();

                // حذف تقييم المشاركة
                if ($participationType) {
                    Evaluation::where([
                        'student_id' => $attendance->student_id,
                        'subject_id' => $attendance->subject_id,
                        'evaluation_type_id' => $participationType->id,
                        'evaluation_date' => $attendance->date,
                    ])->delete();
                }

                // تحديث عمود المشاركة في الحضور إلى false
                if ($attendance->participation) {
                    $attendance->participation = false;
                    $attendance->saveQuietly();
                }
            }
        }

        // ----------------------
        // معالجة المشاركة
        // ----------------------
        if ($participationType) {
            // إذا أصبحت المشاركة true (جديدة أو تعديل)
            if ($attendance->participation && (!$isUpdate || $attendance->wasChanged('participation'))) {
                $this->createEvaluationOnce($attendance, $participationType, $teacherId);
            }

            // إذا تم تعديل المشاركة إلى false
            if ($isUpdate && $attendance->wasChanged('participation') && !$attendance->participation) {
                Evaluation::where([
                    'student_id' => $attendance->student_id,
                    'subject_id' => $attendance->subject_id,
                    'evaluation_type_id' => $participationType->id,
                    'evaluation_date' => $attendance->date,
                ])->delete();
            }
        }
    }

    protected function createEvaluationOnce(Attendance $attendance, EvaluationType $type, int $teacherId): void
    {
        Evaluation::firstOrCreate(
            [
                'student_id'         => $attendance->student_id,
                'subject_id'         => $attendance->subject_id,
                'evaluation_type_id' => $type->id,
                'evaluation_date'    => $attendance->date,
            ],
            [
                'teacher_id' => $teacherId,
                'score'      => 5, // إضافة 5 درجات مرة واحدة
            ]
        );
    }
}
