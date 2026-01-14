<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectEvaluationSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' =>[
                'subject_name' => $this->subject->name,
                //'key' => $this->evaluationType->name,
                'label' => $this->evaluationType->label,
            ],

            'max_score' => $this->max_score,
            'max_count' => $this->max_count,

        ];
    }
}
