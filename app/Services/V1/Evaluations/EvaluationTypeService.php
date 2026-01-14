<?php
namespace App\Services\V1\Evaluations;

use Illuminate\Support\Str;
use App\Models\EvaluationType;

class EvaluationTypeService
{
         public function create(array $labels): array
    {
        $created = [];

        foreach ($labels as $label) {
            $created[] = EvaluationType::create([
                'label' => $label,
                'name' => Str::slug($label),
                //'max_score' => $maxScore,
            ]);
        }

        return $created;
    }

    public function update(EvaluationType $type, array $data): EvaluationType
    {
        $type->update($data);
        return $type;
    }

    public function delete(EvaluationType $type): void
    {
        if ($type->subjectEvaluationSettings()->exists()) {
            throw new \Exception('Cannot delete evaluation type in use.');
        }
        $type->delete();
    }
}
