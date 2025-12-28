<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','subject_id','start_date','end_date','fee','discount','paid_amount','remaining_amount'];

    public function subject()
{
    return $this->belongsTo(Subject::class);
}

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

}
