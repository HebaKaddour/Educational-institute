<?php

namespace App\Http\Controllers\Api\V1\Reports;

use Mpdf\Mpdf;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Services\V1\Reports\StudentReportService;
use App\Http\Requests\V1\Reports\StudentReportRequest;

class StudentReportController extends Controller
{
    public function __construct(private StudentReportService $studentReportService)
    {
    }

public function index(StudentReportRequest $request)
{
    $groups = $this->studentReportService
        ->getGroupedStudents($request->validated());

    return response()->json([
        'status' => 'success',
        'data' => $this->studentReportService->formatGrouped($groups)
    ]);
}

public function exportPdf(StudentReportRequest $request)
{
    $groups = $this->studentReportService
        ->getGroupedStudents($request->validated());

    $data = $this->studentReportService->formatGrouped($groups);

    $html = view('reports.students', [
        'groups' => $data,
        'total'  => collect($data)->sum('total'),
    ])->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4-L',
        'default_font' => 'cairo',
        'direction' => 'rtl',
    ]);

    $mpdf->WriteHTML($html);

    return response(
        $mpdf->Output('student_report.pdf', 'S'),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="student_report.pdf"',
        ]
    );
}

}
