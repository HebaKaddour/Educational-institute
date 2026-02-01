<?php
namespace App\Helper;

function grade(int $total): string
{
    return match (true) {
        $total >= 90 => 'A',
        $total >= 80 => 'B',
        $total >= 70 => 'C',
        $total >= 60 => 'D',
        default      => 'F',
    };
}
