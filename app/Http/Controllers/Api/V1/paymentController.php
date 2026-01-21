<?php
namespace App\Http\Controllers\Api\V1;
use Carbon\Carbon;
use App\Models\Subscription;
use App\Services\V1\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Payments\StorePaymentRequest;

class paymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * تسجيل دفعة جديدة للاشتراك
     */
 public function store(StorePaymentRequest $request, Subscription $subscription, PaymentService $paymentService)
    {
        $payment = $paymentService->createPayment($subscription,$request->amount,$request->method,Carbon::parse($request->paid_at),$request->note
        );
        $remaining_amount = $subscription->remaining_amount;
        $data = [
            'payment' => $payment,

            'subscription' => $subscription->fresh(),

            'remaining_amount' =>$remaining_amount,
        ];
        return self::success(
            $data,
            'تم تسجيل الدفعة بنجاح'
        );
    }

    public function monthlyPayments(Subscription $subscription, PaymentService $paymentService)
    {
        return response()->json([
            'success' => true,
            'data' => $paymentService->getMonthlyPayments($subscription)
        ]);
    }
}
