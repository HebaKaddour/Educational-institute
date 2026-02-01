<?php
namespace App\Http\Controllers\Api\V1\Reports;

use Illuminate\Http\Request;
use App\Reports\ReportFactory;
use App\Http\Controllers\Controller;

class UnifiedReportController extends Controller {

public function print(Request $request)
{
    $request->validate([
        'type' => 'required|in:grades_admin,attendance,financial'
    ]);

    $report = ReportFactory::make($request->type);

    return $report->generate(
        $request->all()
    );
}
}
