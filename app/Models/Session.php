<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'sessions';
    use HasFactory;
    protected $fillable = [
        'working_day_id',
        'session_name',
        'start_time',
        'end_time',
    ];

    public function workingDay()
    {
        return $this->belongsTo(workingDay::class);
    }
}
