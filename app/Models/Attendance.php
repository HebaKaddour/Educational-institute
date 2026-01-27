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
        'participation'
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
 public function evaluations() {
        return $this->hasMany(Evaluation::class, 'student_id', 'student_id')
            ->with('evaluationType'); // Eager load نوع التقييم
    }

    //scope for filtering

public function scopeFilter($query, array $filters)
{
    return $this
        ->when($filters['date'] ?? null, fn($q) => $q->where('date', $filters['date']))
        ->when($filters['subject_id'] ?? null, fn($q) => $q->where('subject_id', $filters['subject_id']))
        ->when($filters['day'] ?? null, fn($q) => $q->where('day', $filters['day']))
        ->when($filters['gender'] ?? null, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('gender', $filters['gender'])))
        ->when($filters['grade'] ?? null, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('grade', $filters['grade'])))
        ->when($filters['section'] ?? null, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('section', $filters['section'])));
}

protected $casts = [
    'date' => 'date',
];


}
