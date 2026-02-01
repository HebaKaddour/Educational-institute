<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\V1\StudentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Http\Resources\SubscriptionListResource;
use App\Http\Requests\V1\Students\StoreStudentRequest;
use App\Http\Requests\V1\Students\SearchStudentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\V1\Students\UpdateStudentProfileRequest;
use App\Http\Requests\V1\Students\UpdateStudentSubscriptionRequest;
/**
 * StudentController
 */
class StudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService
    ) {}
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     * @param  \App\Models\Student  $student
     *
      */
public function index()
{
  $paginator = $this->studentService->getAllStudents();

    // 👇 مهم جدًا
    $paginator->getCollection()->loadMissing('subscriptions.payments');

    return response()->json([
        'status' => 'success',
        'message' => 'قائمة الطلاب',
        'data' => [
            'total_students' => $paginator->total(),
            'students' => StudentResource::collection(
                $paginator->getCollection()
            ),
        ],
        'pagination' => [
            'count' => $paginator->count(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'total_pages' => $paginator->lastPage(),
        ],
    ]);
}

    public function allSubscriptions(){
    $subscriptions = $this->studentService->getAllStudentsWithDetails();

    return response()->json([
        'status' => 'success',
        'message' => 'قائمة الاشتراكات',
        'data' => [
            'total_subscriptions' => $subscriptions->total(), // العدد الكلي للنظام
            'subscriptions' => SubscriptionListResource::collection($subscriptions->items()), // عناصر الصفحة الحالية
        ],
        'pagination' => [
            'count' => $subscriptions->count(), // عدد العناصر في الصفحة الحالية
            'per_page' => $subscriptions->perPage(),
            'current_page' => $subscriptions->currentPage(),
            'total_pages' => $subscriptions->lastPage(),
        ],
    ]);

    }

    public function search(SearchStudentRequest $request)
    {
    $students = $this->studentService->searchStudents($request->validated()['query']);
    return self::success($students,'نتائج البحث');
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
            'section',
            'student_mobile',
            'guardian_mobile',
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

     public function update(UpdateStudentSubscriptionRequest $request, Student $student)
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

        $student->delete();
        return self::success(null,
            " تم حذف الطالب بنجاح : $student->full_name"
        );
    }

    public function updateProfile(UpdateStudentProfileRequest $request,Student $student
    ) {
        $updatedStudent = $this->studentService->updateStudentProfile(
            $student,
            $request->validated()
        );

         return self::success(
            $updatedStudent,
            'تم تحديث ملف الطالب الشخصي بنجاح'
        );
}

public function filterStudents(Request $request)
{
    $filters = $request->only([
        'search',
        'grade',
        'section',
        'gender',
        'status',
    ]);

    $perPage = $request->integer('per_page', 15);

    $students = $this->studentService->filterStudents($filters, $perPage);

    return self::paginated($students, 'تم جلب الطلاب بنجاح');
}

}
