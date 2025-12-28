<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name', 'age', 'identification_number',
        'gender', 'school', 'grade', 'student_mobile', 'guardian_mobile'
    ];

        public function subjects() {
        return $this->belongsToMany(Subject::class, 'student_subjects');
    }

    public function subscriptions()
{
    return $this->hasMany(Subscription::class);
}

    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    public function evaluations() {
        return $this->hasMany(Evaluation::class);
    }
    public function notes() {
        return $this->hasMany(Note::class);
    }
}
