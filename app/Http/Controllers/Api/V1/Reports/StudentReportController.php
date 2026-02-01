<?php

namespace App\Http\Controllers\Api\V1\Reports;

use Mpdf\Mpdf;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Services\V1\Reports\StudentReportService;
use App\Http\Resources\StudentGradesPrintResource;
use App\Http\Requests\V1\Reports\StudentReportRequest;
use App\Services\V1\Evaluations\StudentsEvaluationService;

class StudentReportController extends Controller
{
public function __construct(private StudentsEvaluationService $studentsEvaluationService)
    {
    }


public function generatePdf(Request $request)
{
    $filters = $request->all();

    // جلب الدرجات باستخدام Service
    $grades = $this->studentsEvaluationService->getGrades($filters);

    // إنشاء HTML مباشرة بدون Blade
    $html = '<h2 style="text-align:center;">تقرير الدرجات</h2>
             <table border="1" cellspacing="0" cellpadding="5" width="100%">
             <tr>
                <th>الطالب</th>
                <th>الصف</th>
                <th>المادة</th>
                <th>الدرجة</th>
                <th>المعلم</th>
             </tr>';

    foreach ($grades as $grade) {
        $html .= '<tr>
                    <td>' . $grade->student->full_name . '</td>
                    <td>' . $grade->student->grade . '</td>
                    <td>' . $grade->subject->name . '</td>
                    <td>' . ($grade->score ?? '-') . '</td>
                    <td>' . ($grade->subject->teacher_id ?? '-') . '</td>
                  </tr>';
    }

    $html .= '</table>';

    $pdf = Pdf::loadHTML($html);

    return $pdf->download('grades_report.pdf');
}

}
