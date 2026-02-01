<?php

namespace App\Http\Controllers\Api\V1\Reports;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Services\V1\AttendanceService;

class AttendanceReportController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService
    ) {}

    public function generatePdf(Request $request)
    {
        $filters = $request->all();

        // جلب الحضور من الـ Service
        $attendances = $this->attendanceService->daily($filters);

        // HTML مباشر (بدون Blade)
        $html = '<h2 style="text-align:center;">تقرير الحضور اليومي</h2>
        <table border="1" cellspacing="0" cellpadding="6" width="100%">
            <thead>
                <tr>
                    <th>الطالب</th>
                    <th>الصف</th>
                    <th>الشعبة</th>
                    <th>المادة</th>
                    <th>اليوم</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($attendances as $attendance) {
            $html .= '<tr>
                <td>' . $attendance->student->full_name . '</td>
                <td>' . $attendance->student->grade . '</td>
                <td>' . ($attendance->student->section ?? '-') . '</td>
                <td>' . $attendance->subject->name . '</td>
                <td>' . $attendance->day . '</td>
                <td>' . $attendance->date . '</td>
                <td>' . ($attendance->status ?? '-') . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        $pdf = Pdf::loadHTML($html);

        return $pdf->download('attendance_report.pdf');
    }
}
