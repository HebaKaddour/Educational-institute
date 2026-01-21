<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //protected $appends = ['remaining_amount'];
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'paid_at',
        'method',
        'note',
        'amount',
    ];
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }


}
