<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\SubscriptionResource;
use Illuminate\Http\Resources\Json\JsonResource;
class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
                $totalPaid = (float) ($this->subscriptions_sum_paid_amount ?? 0);
                $totalNet  = (float) ($this->total_net_fee ?? 0);
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'identification_number' => $this->identification_number,
            'age' => $this->age,
            'gender' => $this->gender,
            'student_mobile' => $this->student_mobile,
            'guardian_mobile' => $this->guardian_mobile,
            'school' => $this->school,
            'grade' => $this->grade,
            'section' => $this->section,
            'status' => $this->status,
            'subscriptions' => SubscriptionResource::collection($this->subscriptions),

            'financial_summary' => [
                'total_paid' => $totalPaid,
                'total_remaining' => max($totalNet - $totalPaid, 0),
            ],

            'subscriptions' => SubscriptionResource::collection(
                $this->whenLoaded('subscriptions')
            ),
        ];
    }
}
