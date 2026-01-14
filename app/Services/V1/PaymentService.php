<?php
namespace App\Services\V1;

use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
 public function createPayment(
        Subscription $subscription,
        float $amount,
        string $method,
        Carbon $paidAt,
        ?string $note = null
    ): Payment {

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => ['قيمة الدفعة يجب أن تكون أكبر من صفر']
            ]);
        }

        return DB::transaction(function () use ($subscription, $amount, $method, $paidAt, $note) {

            $payment = Payment::create([
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'method' => $method,
                'paid_at' => $paidAt,
                'note' => $note,
            ]);

            $subscription->paid_amount += $amount;
            $subscription->save();

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
}
