<?php

namespace App\Reports;

use App\Reports\Contracts\ReportInterface;
use App\Services\V1\Evaluations\StudentsEvaluationService;
use Carbon\Carbon;

class GradesAdminReport extends BasePdfReport implements ReportInterface
{
    public function __construct(
        private StudentsEvaluationService $service
    ) {}

    public function generate(array $filters)
    {
        $grades = $this->service->getGrades($filters);

        // في حال pagination
        $grades = collect($grades->items() ?? $grades);

        $avgScore = $grades->whereNotNull('score')->avg('score');
        $count    = $grades->count();
        $dateNow  = Carbon::now()->format('Y-m-d H:i');

        $html = "
        <style>
            body { font-family: dejavusans; direction: rtl; font-size: 11px; }
            .header { text-align: center; margin-bottom: 10px; }
            .meta { margin-bottom: 10px; font-size: 10px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000; padding: 4px; text-align: center; }
            th { background: #eee; }
            .footer { margin-top: 15px; font-size: 11px; }
        </style>

        <div class='header'>
            <h2>التقرير الإداري للدرجات</h2>
            <div>تاريخ الإصدار: {$dateNow}</div>
        </div>

        <div class='meta'>
            <strong>معايير التقرير:</strong><br>
            الصف: ".($filters['grade'] ?? 'الكل')." |
            الشعبة: ".($filters['section'] ?? 'الكل')." |
            المادة: ".($filters['subject_id'] ?? 'الكل')." |
            نوع التقييم: ".($filters['evaluation_type'] ?? 'الكل')."
        </div>

        <table>
            <tr>
                <th>#</th>
                <th>الطالب</th>
                <th>الصف</th>
                <th>الشعبة</th>
                <th>المادة</th>
                <th>المعلم</th>
                <th>نوع التقييم</th>
                <th>رقم</th>
                <th>التاريخ</th>
                <th>الدرجة</th>
                <th>الحالة</th>
            </tr>
        ";

foreach ($grades as $i => $g) {

    $status = '-';
    if ($g->evaluation_type === 'homework') {
        $status = $g->is_completed ? 'مكتمل' : 'غير مكتمل';
    }

    $evaluationNumber = $g->evaluation_number ?? '-';
    $score            = $g->score ?? '-';
    $teacherName      = $g->teacher->id->name ?? '-';

    $html .= "
    <tr>
        <td>".($i + 1)."</td>
        <td>{$g->student->full_name}</td>
        <td>{$g->student->grade}</td>
        <td>{$g->student->section}</td>
        <td>{$g->subject->name}</td>
        <td>{$teacherName}</td>
        <td>{$this->mapEvaluationType($g->evaluation_type)}</td>
        <td>{$evaluationNumber}</td>
        <td>{$g->evaluation_date}</td>
        <td>{$score}</td>
        <td>{$status}</td>
    </tr>
    ";
}


        $html .= "
        </table>

        <div class='footer'>
            <strong>ملخص إداري:</strong><br>
            عدد التقييمات: {$count}<br>
            متوسط الدرجات: ".number_format($avgScore, 2)."<br><br>

            توقيع الإدارة: _______________________
        </div>
        ";

        return $this->pdf($html, 'grades_admin_report.pdf');
    }

    private function mapEvaluationType(string $type): string
    {
        return match ($type) {
            'exam'       => 'اختبار',
            'quiz'       => 'اختبار قصير',
            'homework'   => 'واجب',
            'attendance' => 'حضور',
            default      => $type,
        };
    }
}
