<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\V1\EvaluationService;
use App\Http\Requests\V1\Evaluations\StoreEvaluationRequest;

class EvaluationController extends Controller
{
   public function __construct(private EvaluationService $evaluationService) {}

   public function store(StoreEvaluationRequest $request)
   {
       $evaluation = $this->evaluationService->create($request->all());
       return self::success($evaluation, 'تم إنشاء التقييم بنجاح', 201);
   }
}
