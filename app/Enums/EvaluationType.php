<?php
namespace App\Enums;
use InvalidArgumentException;

Enum EvaluationType : string
{

    case ATTENDANCE    = 'attendance';
    case PARTICIPATION = 'participation';
    case EXAM          = 'exam';
   // case QUIZ          = 'quiz';
    case HOMEWORK      = 'homework';

    public function label(): string
    {
        return match ($this) {
            self::ATTENDANCE    => 'الحضور',
            self::PARTICIPATION => 'المشاركة',
            self::EXAM          => 'الاختبار',
            self::HOMEWORK      => 'الواجبات',
        };
    }

    public static function fromArabic(string $value): self
    {
        return match (trim($value)) {
            'الحضور'    => self::ATTENDANCE,
            'المشاركة'  => self::PARTICIPATION,
            'الواجبات'    => self::HOMEWORK,
            'الاختبار'    => self::EXAM,
            default     => throw new InvalidArgumentException(
                'نوع التقييم غير صحيح'
            ),
        };
    }

    // (اختياري) إرجاع كل الأنواع بالعربي
    public static function arabicValues(): array
    {
        return array_map(
            fn ($case) => $case->label(),
            self::cases()
        );
    }

}
