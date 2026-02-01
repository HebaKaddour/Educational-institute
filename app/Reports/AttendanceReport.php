<?php

namespace App\Reports;

use App\Services\V1\AttendanceService;
use App\Reports\Contracts\ReportInterface;

class AttendanceReport extends BasePdfReport implements ReportInterface
{
    public function __construct(
        private AttendanceService $service
    ) {}

    public function generate(array $filters)
    {
        $rows = $this->service->daily($filters);

        $html = '
        <style>
            body { font-family: dejavusans; direction: rtl; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        </style>

        <h2 style="text-align:center;">تقرير الحضور</h2>

        <table>
            <tr>
                <th>الطالب</th>
                <th>المادة</th>
                <th>التاريخ</th>
                <th>اليوم</th>
                <th>الحالة</th>
            </tr>';

        foreach ($rows as $r) {
            $html .= "
            <tr>
                <td>{$r->student->full_name}</td>
                <td>{$r->subject->name}</td>
                <td>{$r->date}</td>
                <td>{$r->day}</td>
                <td>{$r->status}</td>
            </tr>";
        }

        $html .= '</table>';

        return $this->pdf($html, 'attendance_report.pdf');
    }
}
