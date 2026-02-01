<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectEvaluationSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'subject_id',
        'evaluation_type',
        'max_score',
        'max_count',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
