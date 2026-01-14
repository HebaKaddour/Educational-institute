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
        $payment = $paymentService->createPayment(
            $subscription,
            $request->amount,
            $request->method,
            Carbon::parse($request->paid_at),
            $request->note
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدفعة بنجاح',
            'data' => [
                'payment' => $payment,
                'subscription' => $subscription->fresh()
            ]
        ], 201);
    }

    public function monthlyPayments(Subscription $subscription, PaymentService $paymentService)
    {
        return response()->json([
            'success' => true,
            'data' => $paymentService->getMonthlyPayments($subscription)
        ]);
    }
}
