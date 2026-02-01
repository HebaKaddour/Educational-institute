<?php
namespace App\Services\V1\Reports;

use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentReportService
{
// تقرير يومي حسب الطالب/المادة/اليوم
    public function daily(array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;

        return Attendance::with(['student', 'subject'])
            ->filter($filters)
            ->orderBy('student_id')
            ->paginate($perPage);
    }

    // تقرير كامل بدون pagination للطباعة
    public function dailyForPrint(array $filters): Collection
    {
        return Attendance::with(['student', 'subject'])
            ->filter($filters)
            ->orderBy('student_id')
            ->get();
    }

    // تقرير حسب الطالب
    public function byStudent(array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;

        return Attendance::with(['student', 'subject'])
            ->filter($filters)
            ->orderBy('student_id')
            ->paginate($perPage);
    }

    public function byStudentForPrint(array $filters): Collection
    {
        return Attendance::with(['student', 'subject'])
            ->filter($filters)
            ->orderBy('student_id')
            ->get();
    }

    // تقرير حسب الصف
    public function byGrade(array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;

        return Attendance::with(['student', 'subject'])
            ->filter($filters)
            ->orderBy('grade')
            ->paginate($perPage);
    }

    public function byGradeForPrint(array $filters): Collection
    {
        return Attendance::with(['student', 'subject'])
            ->filter($filters)
            ->orderBy('grade')
            ->get();
    }
}
