<?php

namespace App\Observers;

use DB;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\Evaluation;
use App\Enums\EvaluationType;
use App\Models\SubjectEvaluationSetting;

class AttendanceObserver
{
    public function created(Attendance $attendance)
    {
        $this->handleAttendance($attendance);
    }

    public function updated(Attendance $attendance)
    {
        $this->handleAttendance($attendance, true);
    }

    protected function handleAttendance(Attendance $attendance, bool $isUpdate = false)
{
    if (!$attendance->subject_id) return;

    $teacherId = Subject::where('id', $attendance->subject_id)->value('teacher_id');
    if (!$teacherId) return;

    // ======================
    // 1️⃣ الحضور
    // ======================
    if (in_array($attendance->status, ['حضور', 'بعذر'])) {
        $this->syncEvaluation($attendance, EvaluationType::ATTENDANCE, $teacherId);
    }

    // ======================
    // ❗ حالة بعذر: المشاركة = 0 دائمًا
    // ======================
    if ($attendance->status === 'بعذر') {

        // حذف تقييم المشاركة إن وجد
        $this->deleteEvaluation($attendance, EvaluationType::PARTICIPATION);

        // إجبار المشاركة على false
        if ($attendance->participation) {
            $attendance->updateQuietly(['participation' => false]);
        }

        return; // لا نكمل أي لوجيك مشاركة
    }

    // ======================
    // الغياب
    // ======================
    if ($attendance->status === 'غياب') {
        $this->deleteEvaluation($attendance, EvaluationType::ATTENDANCE);
        $this->deleteEvaluation($attendance, EvaluationType::PARTICIPATION);

        if ($attendance->participation) {
            $attendance->updateQuietly(['participation' => false]);
        }
        return;
    }

    // ======================
    // 2️⃣ المشاركة (فقط للحضور)
    // ======================
    if ($attendance->participation) {
        $this->syncEvaluation($attendance, EvaluationType::PARTICIPATION, $teacherId);
    }

    if ($isUpdate && $attendance->wasChanged('participation') && !$attendance->participation) {
        $this->deleteEvaluation($attendance, EvaluationType::PARTICIPATION);
    }
}

    // ======================
    // Helpers
    // ======================

    protected function syncEvaluation(
        Attendance $attendance,
        EvaluationType $type,
        int $teacherId
    ): void {
        $max_score = $this->getScoreFromSettings($attendance->subject_id, $type);

    if ($max_score === null && in_array($type, [EvaluationType::ATTENDANCE, EvaluationType::PARTICIPATION])) {
        $max_score = 5;
    }

    if ($max_score === null) return;
        Evaluation::updateOrCreate(
            [
                'student_id'      => $attendance->student_id,
                'subject_id'      => $attendance->subject_id,
                'evaluation_type' => $type->value,
                'evaluation_date' => $attendance->date,
            ],
            [
                'teacher_id' => $teacherId,
                'score'      => $max_score,
            ]
        );
    }

    protected function deleteEvaluation(
        Attendance $attendance,
        EvaluationType $type
    ): void {
        Evaluation::where([
            'student_id'      => $attendance->student_id,
            'subject_id'      => $attendance->subject_id,
            'evaluation_type' => $type->value,
            'evaluation_date' => $attendance->date,
        ])->delete();
    }

    protected function getScoreFromSettings(
        int $subjectId,
        EvaluationType $type
    ): ?int {
        return SubjectEvaluationSetting::where([
            'subject_id'      => $subjectId,
            'evaluation_type' => $type->value,
        ])->value('max_score');
    }
}
