<?php
namespace App\Services\V1;

use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentService {

    public function getAllStudents()
    {
        return Student::all();
    }

    public function getStudentWithSubscriptions(Student $student): Student
    {
        return $student->load('subscriptions.subject.teacher:id,name');
    }

    public function createStudentWithSubscriptions(array $data): Student
    {
      {
    return DB::transaction(function () use ($data) {

        $student = Student::create(
            collect($data)->except('subscriptions')->toArray()
        );

        if (!empty($data['subscriptions'])) {
            foreach ($data['subscriptions'] as $subscription) {

                $fee      = (float) $subscription['fee'];
                $discount = (float) ($subscription['discount'] ?? 0);
                $paid     = (float) ($subscription['paid_amount'] ?? 0);

                //Calculate the net amount
                $netFee = $fee - $discount;

                // ✅ 2. منع دفع أكبر من المبلغ الصافي
                if ($paid > $netFee) {
                    throw ValidationException::withMessages([
                        'subscriptions' => [
                            'المبلغ المدفوع لا يمكن أن يتجاوز المبلغ بعد الخصم'
                        ]
                    ]);
                }

                //calculate remaininh amount
                $remaining = max(0, $netFee - $paid);

                $student->subscriptions()->create([
                    'subject_id'       => $subscription['subject_id'],
                    'fee'              => $fee,
                    'discount'         => $discount,
                    'paid_amount'      => $paid,
                    'remaining_amount' => $remaining,
                    'start_date'       => $subscription['start_date'],
                    'end_date'         => $subscription['end_date'],
                ]);
            }
        }

        return $student->load('subscriptions.subject');
    });
}
}
         public function paySubscription(Subscription $subscription,float $paymentAmount): Subscription
         {
            if (!$subscription) {
            throw ValidationException::withMessages([
                  'الاشتراك' => ['الاشتراك غير موجود']
            ]);
        }
          return DB::transaction(function () use ($subscription, $paymentAmount) {

        if ($subscription->remaining_amount <= 0) {
            throw ValidationException::withMessages([
                'payment_amount' => 'تم تسديد كامل رسوم هذه المادة'
            ]);
        }

        if ($paymentAmount > $subscription->remaining_amount) {
            throw ValidationException::withMessages([
                'payment_amount' => 'قيمة الدفعة أكبر من المبلغ المتبقي'
            ]);
        }

        $subscription->paid_amount += $paymentAmount;
        $subscription->remaining_amount =
            ($subscription->fee - $subscription->discount)
            - $subscription->paid_amount;

        $subscription->save();

        return $subscription->fresh();
    });
}
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


    public function deleteStudent(Student $student): bool
    {
        return $student->delete();
    }
}
