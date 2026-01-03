<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'max_score',
        'uses_score',
        'uses_status',
    ];
}
