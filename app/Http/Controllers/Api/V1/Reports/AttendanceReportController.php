<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Services\V1\Reports\attendanceReportService;

class AttendanceReportController extends Controller
{
    protected AttendanceReportService $service;

    public function __construct(AttendanceReportService $service)
    {
        $this->service = $service;
    }

    // ===== API JSON =====
    public function daily(Request $request)
    {
        $filters = $request->only(['date','subject_id','day','gender','grade','section','per_page']);
        $attendances = $this->service->daily($filters);

        return AttendanceResource::collection($attendances)
            ->additional(['status'=>'success','message'=>'تم جلب الحضور بنجاح']);
    }

    public function byStudent(Request $request)
    {
        $filters = $request->only(['student_id','per_page']);
        $attendances = $this->service->byStudent($filters);

        return AttendanceResource::collection($attendances)
            ->additional(['status'=>'success','message'=>'تم جلب الحضور حسب الطالب']);
    }

    public function byGrade(Request $request)
    {
        $filters = $request->only(['grade','section','per_page']);
        $attendances = $this->service->byGrade($filters);

        return AttendanceResource::collection($attendances)
            ->additional(['status'=>'success','message'=>'تم جلب الحضور حسب الصف']);
    }

    // ===== PDF =====
    public function dailyPrint(Request $request)
    {
        $filters = $request->only(['date','subject_id','day','gender','grade','section']);
        $data = AttendanceResource::collection($this->service->dailyForPrint($filters))->resolve();

        return $this->downloadPdf($data,'attendance-daily.pdf','تقرير الحضور اليومي');
    }

    public function byStudentPrint(Request $request)
    {
        $filters = $request->only(['student_id']);
        $data = AttendanceResource::collection($this->service->byStudentForPrint($filters))->resolve();

        return $this->downloadPdf($data,'attendance-student.pdf','تقرير الحضور حسب الطالب');
    }

    public function byGradePrint(Request $request)
    {
        $filters = $request->only(['grade','section']);
        $data = AttendanceResource::collection($this->service->byGradeForPrint($filters))->resolve();

        return $this->downloadPdf($data,'attendance-grade.pdf','تقرير الحضور حسب الصف');
    }

    // ===== Helper PDF =====
    private function downloadPdf(array $data, string $fileName, string $title)
    {
        $html = "<h2>{$title}</h2>";
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width:100%">';
        $html .= '<thead><tr>
                    <th>اسم الطالب</th><th>الصف</th><th>الشعبة</th><th>المادة</th><th>الحضور</th><th>التاريخ</th><th>الدرجة</th>
                  </tr></thead><tbody>';

        foreach ($data as $row){
            $html .= "<tr>
                        <td>{$row['student_name']}</td>
                        <td>{$row['grade']}</td>
                        <td>{$row['section']}</td>
                        <td>{$row['subject']}</td>
                        <td>{$row['attendance']}</td>
                        <td>{$row['date']}</td>
                        <td>{$row['score']}</td>
                      </tr>";
        }

        $html .= '</tbody></table>';

        $pdf = Pdf::loadHtml($html)->setPaper('A4','landscape');
        return $pdf->download($fileName);
    }
}
