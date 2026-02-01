<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name', 'age', 'identification_number',
        'gender', 'school', 'grade', 'student_mobile', 'guardian_mobile','section', 'status'
    ];

        public function subjects() {
        return $this->belongsToMany(Subject::class, 'student_subjects');
    }

    public function subscriptions(){
    return $this->hasMany(Subscription::class);
   }
    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    public function notes() {
        return $this->hasMany(Note::class);
    }

    public function evaluations() {
        return $this->hasMany(Evaluation::class);

    }

    public function attendance()
{
    return $this->hasOne(Attendance::class)
        ->where('date', request('date'))
        ->where('subject_id', request('subject_id'));
}

public function scopeFilter($query, array $filters)
{

    return $this
        ->when($filters['gender'] ?? null, fn($q) => $q->where('gender', $filters['gender']))
        ->when($filters['section'] ?? null, fn($q) => $q->where('section', $filters['section']))
        ->when($filters['school'] ?? null, fn($q) => $q->where('school', $filters['school']))
         ->when($filters['grade'] ?? null, fn($q) => $q->where('grade', $filters['grade']));
}

}
