<?php
namespace App\Services\V1\Reports;

use App\Models\Student;
use Illuminate\Support\Collection;

class StudentReportService
{
    public function getGroupedStudents(array $filters)
    {
    $query = Student::query()
    ->with([
        'subscriptions' => function ($q) use ($filters) {
            if (!empty($filters['subject_id'])) {
                $q->where('subject_id', $filters['subject_id']);
            }
        },
        'subscriptions.subject:id,name'
    ])
            ->select(
                'id',
                'full_name',
                'identification_number',
                'student_mobile',
                'guardian_mobile',
                'grade'
            );

        // filter by grade
        if (!empty($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        // filter by subject
        if (!empty($filters['subject_id'])) {
            $query->whereHas('subscriptions', function ($q) use ($filters) {
                $q->where('subject_id', $filters['subject_id']);
            });
        }

        $students = $query->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'identification_number' => $student->identification_number,
                    'student_mobile' => $student->student_mobile,
                    'guardian_mobile' => $student->guardian_mobile,
                    'grade' => $student->grade,

                  // the subjects the student is enrolled in
                    'subjects' => $student->subscriptions
                        ->map(fn ($sub) => [
                            'id' => $sub->subject->id,
                            'name' => $sub->subject->name,
                        ])
                        ->unique('id')
                        ->values(),
                ];
            });

        return match ($filters['group_by'] ?? null) {

           //group by subject
            'subject' => $students->groupBy(fn ($student) =>
                collect($student['subjects'])
                    ->pluck('name')
                    ->implode(', ')
            ),

            // group by grade
            'grade' => $students->groupBy('grade'),

            // without grouping
            default => collect([
                'جميع الطلاب' => $students
            ]),
        };
    }

public function formatGrouped(Collection $groups): array
{
    return $groups->map(fn ($students, $group) => [
        'group' => $group,
        'total' => $students->count(),
        'students' => $students->values(),
    ])->values()->toArray();
}

}
