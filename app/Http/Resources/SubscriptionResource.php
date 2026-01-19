<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
 public function toArray($request)
    {
        return [
            'month_number' => $this->month_number,
            'start_date' => Carbon::parse($this->start_date)->format('Y-m-d'),
            'end_date' => Carbon::parse($this->end_date)->format('Y-m-d'),
            'monthly_fee' => $this->monthly_fee,
            'total_fee' => $this->total_fee,
            'discount_percentage' => $this->discount_percentage,
            'net_fee' => $this->net_fee,
            'status' => $this->status,
        ];
    }
}
