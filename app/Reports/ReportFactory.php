<?php

namespace App\Reports;

use App\Reports\GradesReport;
use App\Reports\GradesAdminReport;
use App\Reports\AttendanceReport;
use App\Reports\FinancialReport;

class ReportFactory
{
    public static function make(string $type)
    {
        return match ($type) {
           // 'grades'        => app(GradesReport::class),
            'grades_admin'  => app(GradesAdminReport::class), // ✅ مهم جدًا
            'attendance'    => app(AttendanceReport::class),
            'financial'     => app(FinancialReport::class),
            default         => throw new \InvalidArgumentException('Invalid report type'),
        };
    }
}
