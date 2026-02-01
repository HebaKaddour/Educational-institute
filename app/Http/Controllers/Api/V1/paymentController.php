<?php
namespace App\Http\Controllers\Api\V1;
use Carbon\Carbon;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\V1\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Requests\V1\Payments\StorePaymentRequest;
use App\Http\Requests\V1\Payments\UpdatePaymentRequest;

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
        ,201);
    }

public function update(UpdatePaymentRequest $request, Payment $payment)
{
    $updatedData = $this->paymentService->updatePayment(
        $payment,
        $request->validated()
    );

    return response()->json([
        'status' => 'success',
        'message' => 'تم تعديل الدفعة بنجاح',
        'data' => [
            'payment' => new PaymentResource($updatedData['payment']),
            'subscription' => $updatedData['subscription'],
            'remaining_amount' => $updatedData['remaining_amount'],
        ]
    ]);
}

public function destroy(Payment $payment)
{
    $this->paymentService->deletePayment($payment);

    return response()->json([
        'status' => 'success',
        'message' => 'تم حذف الدفعة بنجاح',
        'data' => [
            'subscription_id' => $payment->subscription_id,
            'remaining_amount' => $payment->subscription->remaining_amount,
        ]
    ], 200);
}

    public function monthlyPayments(Subscription $subscription, PaymentService $paymentService)
    {
        return response()->json([
            'success' => true,
            'data' => $paymentService->getMonthlyPayments($subscription)
        ]);
    }
}
