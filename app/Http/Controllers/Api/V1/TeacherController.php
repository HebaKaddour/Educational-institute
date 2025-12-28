<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Services\V1\TeacherService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Teacher\StoreTeacherRequest;
use App\Http\Requests\V1\Teacher\UpdateTeacherRequest;

class TeacherController extends Controller
{
   protected $teacherService;

    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }
    public function index()
    {
        return self::success(
            $this->teacherService->getAllTeachers(),
            'قائمة المعلمين',200
        );
    }

    public function show(User $teacher)
    {
        return self::success(
            $this->teacherService->getTeacher($teacher),
            'بيانات المعلم',200
        );
    }

    public function store(StoreTeacherRequest $request)
    {
        return self::success(
            $this->teacherService->createTeacher($request->validated()),
            'تم إضافة المعلم بنجاح',
            201
        );
    }

    public function update(UpdateTeacherRequest $request, $teacher_id)
    {
        // تحقق من وجود المعلم
        $teacher = User::find($teacher_id);
        if (!$teacher) {
            return response()->json(['status' => 'error', 'message' => 'المعلم غير موجود'], 404);
        }

        // تحديث المعلم
        $updatedTeacher = $this->teacherService->updateTeacher($teacher, $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $updatedTeacher,
            'message' => 'تم تحديث بيانات المعلم بنجاح'
        ]);
    }


    public function destroy(User $teacher)
    {
        $this->teacherService->deleteTeacher($teacher);

        return self::success(
            null,
            'تم حذف المعلم بنجاح',200
        );
    }
}
