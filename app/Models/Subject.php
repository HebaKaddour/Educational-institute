<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
     protected $fillable = ['name','teacher_id'];

     public function teacher()
    {
    return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students() {
        return $this->belongsToMany(Student::class, 'student_subjects')
        ->withPivot(['start_date', 'end_date']);;
    }

    public function subscriptions()
    {
    return $this->hasMany(Subscription::class);
    }
}
