<?php

namespace App\Http\Controllers\Api\V1\Reports;

use Mpdf\Mpdf;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Services\V1\Reports\StudentReportService;
use App\Http\Resources\StudentGradesPrintResource;
use App\Http\Requests\V1\Reports\StudentReportRequest;

class StudentReportController extends Controller
{
    public function __construct(private StudentReportService $studentReportService)
    {
    }


public function print(Request $request)
{
    $filters = $request->only(['student_id', 'subject_id', 'grade', 'gender']);
        $user = auth('sanctum')->user();

        // 1️⃣ جلب التقييمات من السيرفس
        $evaluations = $this->studentReportService
            ->getGradesForPrint($filters, $user);

        // 2️⃣ تحويل التقييمات إلى بيانات جاهزة
        $students = StudentGradesPrintResource::collection(
            $evaluations->groupBy('student_id')
        )->resolve();

        // 3️⃣ إذا حددنا ?format=pdf في الرابط
        if ($request->query('format') === 'pdf') {
            $html = $this->generateHtml($students);
            $pdf  = Pdf::loadHTML($html);
            return $pdf->download('students-grades.pdf');
        }

        // 4️⃣ خلاف ذلك نرجع JSON
        return response()->json([
            'status' => 'success',
            'message' => 'تقييمات الطلاب جاهزة',
            'data' => $students,
        ]);
    }

    /**
     * توليد HTML سريع من البيانات لإنشاء PDF
     */
    private function generateHtml(array $students): string
    {
        $html = '<h1 style="text-align:center;">تقرير درجات الطلاب</h1>';
        foreach ($students as $student) {
            $html .= "<h2>{$student['student_name']} - {$student['student_class']}</h2>";
            $html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">';
            $html .= '<tr style="background:#eee;"><th>المادة</th><th>الحضور</th><th>المشاركة</th><th>الواجبات</th><th>الاختبارات</th><th>المجموع</th><th>الدرجة</th></tr>';

            foreach ($student['subjects'] as $subject) {
                $scores = $subject['scores'];
                $exams  = is_array($scores['الاختبار']) ? implode(', ', $scores['الاختبار']) : $scores['الاختبار'];

                $html .= "<tr>
                    <td>{$subject['subject']}</td>
                    <td>{$scores['الحضور']}</td>
                    <td>{$scores['المشاركة']}</td>
                    <td>{$scores['الواجبات']}</td>
                    <td>{$exams}</td>
                    <td>{$subject['total']}</td>
                    <td>{$subject['grade']}</td>
                </tr>";
            }

            $html .= '</table><br><br>';
        }

        return $html;
    }

}
