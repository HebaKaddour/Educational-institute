<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\V1\StudentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Students\StoreStudentRequest;
use App\Http\Requests\V1\Students\SearchStudentRequest;
use App\Http\Requests\V1\Students\UpdateStudentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\V1\Students\AddSubscriptionRequest;
use App\Http\Requests\V1\Students\UpdateStudentSubscriptionRequest;
use App\Http\Requests\V1\Students\UpdateSubscriptionStudentRequest;

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

public function search(SearchStudentRequest $request)
{
    $students = $this->studentService
        ->searchStudents($request->validated()['query']);

    return self::success($students, 'نتائج البحث');
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
        // بيانات الطالب
        $studentData = $request->only([
            'full_name',
            'identification_number',
            'age',
            'gender',
            'school',
            'grade',
            'section'
        ]);

        // بيانات الاشتراك
        $subscriptionData = $request->validatedSubscription();

        $student = $this->studentService->createStudentWithSubscription($studentData, $subscriptionData);

    return self::success($student, 'تم إنشاء الطالب والاشتراك بنجاح', 201);
}


public function updateStudentSubscription(UpdateStudentSubscriptionRequest $request, Subscription $subscription)
{
    $validated = $request->validated();

    $student = $this->studentService->updateStudentWithSubscription($subscription, $validated);
    return self::success($student, 'تم تعديل بيانات الطالب والاشتراك بنجاح', 200);
}


//
public function changeStatus(Student $student)
    {
        $student = $this->studentService->withdrawStudent($student);
        return self::success($student, 'تم تغيير حالة الطالب إلى منسحب بنجاح', 200);

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

    public function addSubscription(AddSubscriptionRequest $request,Student $student) {
        $subscription = $this->studentService
            ->addSubscription($student, $request->validated());

        return self::success($subscription->load('subject.teacher:id,name'),
            'تم إضافة المادة للطالب بنجاح',
            201
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
