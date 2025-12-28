<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id','subject_id','رقم_الأسبوع','اليوم','الحالة'
    ];

    public function student() {
        return $this->belongsTo(Student::class);
    }
}
