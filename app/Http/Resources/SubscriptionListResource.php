<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'subscription_id' => $this->id,

           //student info
            'student' => [
                'id' => $this->student->id,
                'full_name' => $this->student->full_name,
            ],

           // subscription info
            'months_registered' => $this->month_number,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,

              // financial info
            'fees' => [
                'required' => $this->net_fee,
                'paid' => $this->paid_amount,
                'remaining' => $this->remaining_amount,
            ],

            'status' => $this->status,
        ];
    }
}
