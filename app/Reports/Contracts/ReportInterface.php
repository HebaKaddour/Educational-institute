<?php

namespace App\Reports\Contracts;

interface ReportInterface
{
    public function generate(array $filters);
}
