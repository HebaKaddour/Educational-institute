<?php
namespace App\Services\V1;

use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
 public function createPayment(Subscription $subscription,float $amount,string $method,Carbon $paidAt,
        ?string $note = null
    ): Payment {

        $this->validatePaymentAmount($amount, $subscription);
        return DB::transaction(function () use ($subscription, $amount, $method, $paidAt, $note) {

            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'method' => $method,
                'paid_at' => $paidAt ?? now(),
                'note' => $note,
            ]);

          $subscription->increment('paid_amount', $amount);


            $this->updateSubscriptionStatus($subscription);

            return $payment;
        });
    }

    /**
     * تحديث حالة الاشتراك
     */
    private function updateSubscriptionStatus(Subscription $subscription): void
    {
        $today = Carbon::today();

        if ($today->gt($subscription->end_date)) {
            $subscription->status = 'منتهي';
        } elseif ($today->diffInDays($subscription->end_date) <= 7) {
            $subscription->status = 'منتهي قريباً';
        } else {
            $subscription->status = 'ساري';
        }

        $subscription->save();
    }

    /**
     * عرض الدفعات الشهرية مع احتساب المستحق والمدفوع والمتبقي
     */
    public function getMonthlyPayments(Subscription $subscription): array
    {
        $months = $subscription->month_number;
        $netFee = $subscription->net_fee;
        $totalPaid = $subscription->paid_amount;
        $startDate = Carbon::parse($subscription->start_date);
        $monthlyPayments = [];

        $monthlyFee = round($netFee / $months, 2);

        for ($i = 0; $i < $months; $i++) {
            $monthDate = $startDate->copy()->addMonthsNoOverflow($i);

            $paidThisMonth = min($monthlyFee, $totalPaid);
            $remaining = max($monthlyFee - $paidThisMonth, 0);

            $status = 'غير مدفوع';
            if ($paidThisMonth >= $monthlyFee) $status = 'مدفوع';
            elseif ($monthDate->isPast() && $paidThisMonth < $monthlyFee) $status = 'متأخر';

            $monthlyPayments[] = [
                'month' => $monthDate->format('Y-m'),
                'month_name' => $monthDate->translatedFormat('F Y'),
                'required' => $monthlyFee,
                'paid' => $paidThisMonth,
                'remaining' => $remaining,
                'status' => $status,
            ];

            $totalPaid -= $paidThisMonth;
        }

        return $monthlyPayments;
    }

    private function validatePaymentAmount(float $amount, Subscription $subscription): void
{
    if ($subscription->paid_amount >= $subscription->net_fee) {
        throw ValidationException::withMessages([
            'تم تسديد كامل الرسوم مسبقا'
        ]);
    }


    if ($amount <= 0) {
        throw ValidationException::withMessages([
            'amount' => ['قيمة الدفعة يجب أن تكون أكبر من صفر']
        ]);
    }

    if ($amount > $subscription->remaining_amount) {
        throw ValidationException::withMessages([
            'amount' => ['قيمة الدفعة أكبر من المبلغ المتبقي على الطالب']
        ]);
    }
}
public function updatePayment(
        Payment $payment,
        array $data
    ): array { // سنرجع كل شيء للرد النهائي
        return DB::transaction(function () use ($payment, $data) {

            $subscription = $payment->subscription;

            // 👇 نجهز القيم الجديدة، مع fallback للقيم القديمة
            $newAmount = $data['amount'] ?? $payment->amount;
            $method = $data['method'] ?? $payment->method;
            $paidAt = isset($data['paid_at'])
                ? Carbon::parse($data['paid_at'])
                : $payment->paid_at;
            $note = $data['note'] ?? $payment->note;

            // حساب الفرق
            $delta = $newAmount - $payment->amount;

            // التحقق من صحة المبلغ
            $this->validatePaymentUpdateAmount($delta, $subscription);

            // تحديث الدفعة
            $payment->update([
                'amount'  => $newAmount,
                'method'  => $method,
                'paid_at' => $paidAt,
                'note'    => $note,
            ]);

            $subscription->increment('paid_amount', $delta);
            $this->updateSubscriptionStatus($subscription);

            return [
                'payment' => $payment->fresh(),
                'subscription' => $subscription->fresh(),
                'remaining_amount' => $subscription->remaining_amount,
            ];
        });
    }

    private function validatePaymentUpdateAmount(float $delta, Subscription $subscription): void
    {
        if ($delta > 0 && $subscription->paid_amount + $delta > $subscription->net_fee) {
            throw ValidationException::withMessages([
                'amount' => ['قيمة الدفعة المعدلة تتجاوز المبلغ المتبقي على الطالب']
            ]);
        }

        if ($delta + $subscription->paid_amount < 0) {
            throw ValidationException::withMessages([
                'amount' => ['المبلغ بعد التعديل لا يمكن أن يكون أقل من صفر']
            ]);
        }
    }

    public function deletePayment(Payment $payment): void
{
    DB::transaction(function () use ($payment) {
        $subscription = $payment->subscription;

        // نخصم قيمة الدفعة من المبلغ المدفوع في الاشتراك
        $subscription->decrement('paid_amount', $payment->amount);

        // تحديث حالة الاشتراك بعد حذف الدفعة
        $this->updateSubscriptionStatus($subscription);

        // حذف الدفعة نفسها
        $payment->delete();
    });
}

}
