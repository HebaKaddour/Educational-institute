<?php
namespace App\Enums;

enum SchedulerDay: string
{
    case SATURDAY = 'السبت';
    case SUNDAY = 'الأحد';
    case MONDAY = 'الاثنين';
    case TUESDAY = 'الثلاثاء';
    case WEDNESDAY = 'الأربعاء';
    case THURSDAY = 'الخميس';
    case FRIDAY = 'الجمعة';
}
