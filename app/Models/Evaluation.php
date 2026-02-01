<?php
namespace App\Models;
use App\Enums\EvaluationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject_id',
        'evaluation_type',
        'score',
        'date',
        'notes',
        'evaluation_number',
        'evaluation_date',
        'is_completed'
    ];

    protected $appends = ['type'];


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

    // Accessor
    public function getTypeAttribute(): ?string
    {
        return $this->evaluation_type
            ? EvaluationType::tryFrom($this->evaluation_type)?->label()
            : null;
    }

   // إذا كان المستخدم معلم، نعرض فقط التقييمات الخاصة بمقرره
           public function scopeFilter(Builder $query, array $filters = [], $user = null): Builder
    {
        if ($user?->hasRole('teacher')) {
            $query->whereHas('subject', fn($q) => $q->where('teacher_id', $user->id));
        }

        return $query
            ->when($filters['student_id'] ?? null, fn($q, $v) => $q->where('student_id', $v))
            ->when($filters['subject_id'] ?? null, fn($q, $v) => $q->where('subject_id', $v))
            ->when($filters['grade'] ?? null, fn($q, $v) => $q->whereHas('student', fn($q2) => $q2->where('grade', $v)))
            ->when($filters['gender'] ?? null, fn($q, $v) => $q->whereHas('student', fn($q2) => $q2->where('gender', $v)));
    }
}


