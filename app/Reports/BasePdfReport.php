<?php

namespace App\Reports;

use Mpdf\Mpdf;

abstract class BasePdfReport
{
    protected function pdf(string $html, string $fileName)
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'default_font' => 'dejavusans',
            'direction' => 'rtl',
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output($fileName, 'D');
    }
}
