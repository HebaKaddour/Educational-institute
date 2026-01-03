<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'evaluation_type_id',
        'score',
        'status',
        'week',
        'date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function evaluationType()
    {
        return $this->belongsTo(EvaluationType::class);
    }
}
