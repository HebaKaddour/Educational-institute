<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Subject;
use App\Services\V1\SubjectService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreSubjectRequest;
use App\Http\Requests\V1\Subject\UpdateSubjectRequest;

class SubjectController extends Controller
{
    public function __construct(
        protected SubjectService $subjectService
    ) {}

    public function index()
    {
        return self::success(
            $this->subjectService->getAllSubjects(),
            'قائمة المواد الدراسية'
        );
    }

    public function show(Subject $subject)
    {
        return self::success(
            $this->subjectService->getSubject($subject),
            'تفاصيل المادة'
        );
    }

    public function store(StoreSubjectRequest $request)
    {
        $subject = $this->subjectService->createSubject($request->validated());

        return self::success(
            $subject,
            'تم إنشاء المادة بنجاح',
            201
        );
    }

    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        $subject = $this->subjectService->updateSubject(
            $subject,
            $request->validated()
        );

        return self::success(
            $subject,
            'تم تحديث المادة بنجاح'
        );
    }

    public function destroy(Subject $subject)
    {
        $this->subjectService->deleteSubject($subject);

        return self::success(
            null,
            'تم حذف المادة بنجاح'
        );
    }
}
