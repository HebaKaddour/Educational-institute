<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
   // protected $appends = ['remaining_amount'];
    use HasFactory;
    protected $fillable = ['student_id','start_date',
    'total_fee','end_date','monthly_fee','discount_percentage','discount_amount','paid_amount',
    'remaining_amount', 'month_number','net_fee'];

    public function subject()
{
    return $this->belongsTo(Subject::class);
}

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

//return remaining amount
public function getRemainingAmountAttribute() : float
   {
    return max(
        (float) $this->net_fee - (float) $this->paid_amount,
        0
    );
  }

      public function isFullyPaid(): bool
    {
        return $this->remaining_amount <0;
    }
}
