<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Student;
use App\Models\Subscription;
use App\Services\V1\StudentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreSudentRequest;
use App\Http\Requests\V1\StoreStudentRequest;
use App\Http\Requests\V1\UpdateStudentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\V1\UpdateSubscriptionStudentRequest;

class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService
    ) {}

    public function index()
    {
        return self::success(
            $this->studentService->getAllStudents(),
            'قائمة الطلاب'
        );
    }

   public function show(Student $student)
    {
        try {
            $studentWithSubscriptions = $this->studentService->getStudentWithSubscriptions($student);

            return self::success(
                $studentWithSubscriptions,
                'تم جلب بيانات الطالب بنجاح'
            );

        } catch (ModelNotFoundException $e) {
            return self::error('الطالب غير موجود', 404);
        }
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->createStudentWithSubscriptions($request->validated());

        return self::success($student, 'تم إنشاء الطالب والاشتراكات بنجاح', 201);
    }

    public function pay(UpdateSubscriptionStudentRequest $request, $subscription_id)
 {

         $subscription = Subscription::find($subscription_id);

        if (!$subscription) {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'الاشتراك' => ['الاشتراك غير موجود']
        ]);
    }

    $updated = $this->studentService->paySubscription($subscription, $request->payment_amount);

 return self::success($updated, 'تم تسجيل الدفعة بنجاح', 200);
}

     public function update(UpdateStudentRequest $request, Student $student)
    {
    return self::success(
        $this->studentService->updateStudentProfile(
            $student,
            $request->validated()
        ),
        'تم تحديث بيانات الطالب بنجاح'
    );
}


    public function destroy(Student $student)
    {
        $this->studentService->deleteStudent($student);

        return self::success(
            null,
            'تم حذف الطالب بنجاح'
        );
    }
}
