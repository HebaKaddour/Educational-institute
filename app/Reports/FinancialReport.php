<?php

namespace App\Reports;

use App\Reports\Contracts\ReportInterface;
use App\Models\Subscription;
use Carbon\Carbon;

class FinancialReport extends BasePdfReport implements ReportInterface
{
    public function generate(array $filters)
    {
        $subscriptions = Subscription::with('student')
            ->when($filters['grade'] ?? null,
                fn($q) => $q->whereHas('student',
                    fn($sq) => $sq->where('grade', $filters['grade'])
                )
            )
            ->when($filters['section'] ?? null,
                fn($q) => $q->whereHas('student',
                    fn($sq) => $sq->where('section', $filters['section'])
                )
            )
            ->get();

        // ===== الملخص =====
        $expectedTotal = $subscriptions->sum('net_fee');
        $paidTotal     = $subscriptions->sum('paid_amount');
        $remaining     = $expectedTotal - $paidTotal;
        $count         = $subscriptions->count();

        // ===== HTML =====
        $html = "
        <style>
            body { font-family: dejavusans; direction: rtl; font-size: 12px; }
            h2, h3 { text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #000; padding: 6px; text-align: center; }
            th { background: #f2f2f2; }
            .late { background: #ffe5e5; }
            .summary td { font-weight: bold; }
        </style>

        <h2>التقرير المالي للرسوم</h2>

        <table class='summary'>
            <tr>
                <th>عدد الاشتراكات</th>
                <th>إجمالي المتوقع</th>
                <th>إجمالي المُحصّل</th>
                <th>المتبقي</th>
            </tr>
            <tr>
                <td>{$count}</td>
                <td>{$expectedTotal}</td>
                <td>{$paidTotal}</td>
                <td>{$remaining}</td>
            </tr>
        </table>

        <h3>تفاصيل الاشتراكات</h3>

        <table>
            <tr>
                <th>#</th>
                <th>الطالب</th>
                <th>الصف</th>
                <th>الشعبة</th>
                <th>بداية الاشتراك</th>
                <th>نهاية الاشتراك</th>
                <th>قيمة الاشتراك</th>
                <th>المدفوع</th>
                <th>المتبقي</th>
                <th>الحالة</th>
            </tr>
        ";

        foreach ($subscriptions as $i => $s) {

            $remainingAmount = $s->net_fee - $s->paid_amount;
            $isLate = $remainingAmount > 0 && Carbon::today()->gt($s->end_date);

            $rowClass = $isLate ? 'late' : '';

            $html .= "
            <tr class='{$rowClass}'>
                <td>".($i + 1)."</td>
                <td>{$s->student->full_name}</td>
                <td>{$s->student->grade}</td>
                <td>{$s->student->section}</td>
                <td>{$s->start_date}</td>
                <td>{$s->end_date}</td>
                <td>{$s->net_fee}</td>
                <td>{$s->paid_amount}</td>
                <td>{$remainingAmount}</td>
                <td>{$s->status}</td>
            </tr>
            ";
        }

        $html .= "</table>";

        return $this->pdf($html, 'financial_report.pdf');
    }
}
