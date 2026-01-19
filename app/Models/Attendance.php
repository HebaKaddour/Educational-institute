<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'date',
        'week',
        'day',
        'status',
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    //scope for filtering


public function scopeFilter(Builder $query, array $filters): Builder
{
    return $query
        ->when(isset($filters['subject_id']), fn($q) =>
            $filters['subject_id'] === null
                ? $q->whereNull('subject_id')
                : $q->where('subject_id', $filters['subject_id'])
        )
        ->when(!empty(trim($filters['date'] ?? '')), fn($q) =>
            $q->whereDate('date', trim($filters['date']))
        )
        ->when(!empty(trim($filters['day'] ?? '')), fn($q) =>
            $q->where('day', trim($filters['day']))
        )
        ->when(!empty(trim($filters['section'] ?? '')), fn($q) =>
            $q->whereHas('student', fn($s) => $s->where('section', trim($filters['section'])))
        )
        ->when(!empty(trim($filters['week'] ?? '')), fn($q) =>
            $q->where('week', trim($filters['week']))
        )
        ->when(!empty(trim($filters['grade'] ?? '')), fn($q) =>
            $q->whereHas('student', fn($s) => $s->where('grade', trim($filters['grade'])))
        )
        ->when(!empty(trim($filters['gender'] ?? '')), fn($q) =>
            $q->whereHas('student', fn($s) => $s->where('gender', trim($filters['gender'])))
        );
}

protected $casts = [
    'start_date' => 'datetime',
    'end_date' => 'datetime',
];


}
