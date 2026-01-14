<?php
namespace App\Services\V1;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mpdf\Tag\S;

class StudentService {

    //get all students
    public function getAllStudents()
    {
            $students = Student::all();
    $count = Student::count(); // هذا يجلب عدد الطلاب

    return [
        'students' => $students,
        'count' => $count,
    ];
    }

    //get one student
    public function getStudentWithSubscriptions(Student $student): Student
    {
        return Student::findOrFail($student->id);
    }


public function searchStudents(string $query)
{
    return Student::query()
        ->where('full_name', 'LIKE', "%{$query}%")
        ->select([
            'id',
            'full_name',
            'student_mobile',
            'guardian_mobile'
        ])
        ->limit(20)
        ->get();
}

    public function createStudentWithSubscription(array $studentData, array $subscriptionData): Student
    {
        return DB::transaction(function () use ($studentData, $subscriptionData) {

            // إنشاء الطالب
            $student = Student::create($studentData);

            // إنشاء الاشتراك مع حساب الخصم والمبلغ الصافي
            $this->createSubscriptionWithPaymentInfo($student, $subscriptionData);

            return $student->load('subscriptions');
        });
    }

    /**
     * إنشاء اشتراك مع حساب الخصم والمبلغ الصافي
     */
    private function createSubscriptionWithPaymentInfo(Student $student, array $data): Subscription
    {
        $startDate = Carbon::parse($data['start_date']);
        $months = (int) $data['month_number'];
        $monthlyFee = (float) $data['monthly_fee'];
        $discountPercentage = (float) ($data['discount_percentage'] ?? 0);

        if ($months <= 0) {
            throw ValidationException::withMessages([
                'subscription.month_number' => ['عدد الأشهر يجب أن يكون أكبر من صفر']
            ]);
        }

        if ($monthlyFee <= 0) {
            throw ValidationException::withMessages([
                'subscription.monthly_fee' => ['قيمة الاشتراك الشهري يجب أن تكون أكبر من صفر']
            ]);
        }

        if ($discountPercentage < 0 || $discountPercentage > 100) {
            throw ValidationException::withMessages([
                'subscription.discount_percentage' => ['نسبة الخصم يجب أن تكون بين 0 و 100']
            ]);
        }

        $totalFee = $monthlyFee * $months;
        $discountAmount = $totalFee * $discountPercentage / 100;
        $netFee = $totalFee - $discountAmount;

        $endDate = $startDate->copy()->addMonthsNoOverflow($months);

        return $student->subscriptions()->create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'month_number' => $months,
            'monthly_fee' => $monthlyFee,
            'total_fee' => $totalFee,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'net_fee' => $netFee,
            'paid_amount' => 0,
            'status' => 'ساري',
        ]);
    }




 //update student and subscription
public function updateStudentWithSubscription(Subscription $subscription, array $validated)
{
    return DB::transaction(function () use ($subscription, $validated) {

        $studentData = collect($validated)
            ->only(['full_name','identification_number','age','gender','school','grade','section'])
            ->toArray();

        // تحديث بيانات الطالب
        if (!empty($studentData)) {
            $subscription->student->update($studentData);
        }

        // تحديث بيانات الاشتراك
        if (!empty($validated['subscription'])) {
            $subscriptionData = $validated['subscription'];
            $this->updateSubscription($subscription, $subscriptionData);
        }

        // إعادة تحميل الاشتراكات بعد التحديث
        return $subscription->student->load('subscriptions');
    });
}

public function updateSubscription(Subscription $subscription, array $data): Subscription
{
    $startDate = isset($data['start_date'])
        ? Carbon::parse($data['start_date'])
        : Carbon::parse($subscription->start_date);

    $months = isset($data['month_number']) ? (int) $data['month_number'] : $subscription->month_number;
    $monthlyFee = isset($data['monthly_fee']) ? (float) $data['monthly_fee'] : $subscription->monthly_fee;
    $discountPercentage = isset($data['discount_percentage']) ? (float) $data['discount_percentage'] : $subscription->discount_percentage;

    if ($months <= 0) {
        throw ValidationException::withMessages(['month_number' => ['عدد الأشهر يجب أن يكون أكبر من صفر']]);
    }

    if ($monthlyFee < 0) {
        throw ValidationException::withMessages(['monthly_fee' => ['قيمة الاشتراك الشهري يجب أن تكون صفر أو أكبر']]);
    }

    if ($discountPercentage < 0 || $discountPercentage > 100) {
        throw ValidationException::withMessages(['discount_percentage' => ['نسبة الخصم يجب أن تكون بين 0 و 100']]);
    }

    $totalFee = $monthlyFee * $months;
    $discountAmount = ($totalFee * $discountPercentage) / 100;
    $netFee = $totalFee - $discountAmount;
    $endDate = $startDate->copy()->addMonthsNoOverflow($months);

    $subscription->update([
        'start_date' => $startDate,
        'end_date' => $endDate,
        'month_number' => $months,
        'monthly_fee' => $monthlyFee,
        'total_fee' => $totalFee,
        'discount_percentage' => $discountPercentage,
        'discount_amount' => $discountAmount,
        'net_fee' => $netFee,
    ]);

    return $subscription->refresh();
}

//update student profile
    public function updateStudentProfile(Student $student, array $data): Student
    {
    return DB::transaction(function () use ($student, $data) {

        // update personal information
        $student->update([
            'full_name'        => $data['full_name']        ?? $student->full_name,
            'age'              => $data['age']              ?? $student->age,
            'identification_number' => $data['identification_number'] ?? $student->identification_number,
            'gender'           => $data['gender']           ?? $student->gender,
            'school'           => $data['school']           ?? $student->school,
            'grade'            => $data['grade']            ?? $student->grade,
            'student_mobile'   => $data['student_mobile']   ?? $student->student_mobile,
            'guardian_mobile'  => $data['guardian_mobile']  ?? $student->guardian_mobile,
        ]);

        return $student->fresh();
    });
}


//delete student by changing status to withdrawn and updating subscriptions to expired
    public function withdrawStudent(Student $student): Student
    {
        return DB::transaction(function () use ($student) {

            //change student status to withdrawn
            $student->status = 'منسحب';
            $student->save();

           //update all subscriptions to expired
            $student->subscriptions()->update([
                'status' => 'منتهي'
            ]);

            return $student->refresh()->load('subscriptions');
        });
    }
}
