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
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'identification_number' => $this->identification_number,
            'age' => $this->age,
            'gender' => $this->gender,
            'school' => $this->school,
            'grade' => $this->grade,
            'section' => $this->section,
            'status' => $this->status,
            'subscriptions' => SubscriptionResource::collection($this->subscriptions),
        ];
    }
}
