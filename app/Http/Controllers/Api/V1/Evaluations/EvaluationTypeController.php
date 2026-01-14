<?php

namespace App\Http\Controllers\Api\V1\Evaluations;

use Illuminate\Http\Request;
use App\Models\EvaluationType;
use App\Http\Controllers\Controller;
use App\Http\Resources\EvaluationTypeResource;
use App\Services\V1\Evaluations\EvaluationTypeService;
use App\Http\Requests\V1\Evaluations\StoreEvaluationRequest;
use App\Http\Requests\V1\Evaluations\StoreEvaluationTypeRequest;
use App\Http\Requests\V1\Evaluations\UpdateEvaluationTypeRequest;
use GrahamCampbell\ResultType\Success;

class EvaluationTypeController extends Controller
{

        public function __construct(private EvaluationTypeService $evaluationTypeService)
    {}

    public function index()
    {
        return self::success(
            EvaluationTypeResource::collection(EvaluationType::all())
            , 'قائمة أنواع التقييمات');
    }

    public function store(StoreEvaluationTypeRequest $request)
    {
        $types = $this->evaluationTypeService->create($request->validatedLabels());
        return self::success(
            EvaluationTypeResource::collection($types),
            'تم إنشاء أنواع التقييمات بنجاح',
            201
        );
    }

    public function update(UpdateEvaluationTypeRequest $request, EvaluationType $evaluationType)
    {
        $this->evaluationTypeService->update($evaluationType, $request->validated());
        return self::success(new EvaluationTypeResource($evaluationType), 'تم تحديث نوع التقييم بنجاح');
    }

    public function destroy(EvaluationType $evaluationType)
    {
        $this->evaluationTypeService->delete($evaluationType);
        return self::success(message: 'تم حذف نوع التقييم بنجاح');
    }

}
