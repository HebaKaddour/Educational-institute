<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'date'
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    //scope for filtering

   public function scopeWeek($query, $week)
    {
        return $query->when($week, fn ($q) =>
            $q->where('week', $week)
        );
    }

    public function scopeDate($query, $date)
    {
        return $query->when($date, fn ($q) =>
            $q->whereDate('date', $date)
        );
    }

    public function scopeSubject($query, $subjectId)
    {
        return $query->when(
            array_key_exists('subject_id', request()->all()),
            fn ($q) => $subjectId === null
                ? $q->whereNull('subject_id')
                : $q->where('subject_id', $subjectId)
        );
    }

    public function scopeGrade($query, $grade)
    {
        return $query->when($grade, fn ($q) =>
            $q->whereHas('student', fn ($s) =>
                $s->where('grade', $grade)
            )
        );
    }

    public function scopeGender($query, $gender)
    {
        return $query->when($gender, fn ($q) =>
            $q->whereHas('student', fn ($s) =>
                $s->where('gender', $gender)
            )
        );
    }
}
