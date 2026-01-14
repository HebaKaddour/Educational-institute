<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class workingDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_name',
        'gender',
    ];

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

}
