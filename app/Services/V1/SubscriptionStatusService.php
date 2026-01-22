<?php
namespace App\Services\V1;

use Carbon\Carbon;
use App\Models\Subscription;

class SubscriptionStatusService {
public static function updateDaily(): void
    {
        $today = Carbon::today();

        Subscription::chunk(100, function ($subscriptions) use ($today) {
            foreach ($subscriptions as $subscription) {

                if ($today->gt($subscription->end_date)) {
                    $subscription->status = 'منتهي';
                } elseif (
                    $today->lte($subscription->end_date) &&
                    $today->diffInDays($subscription->end_date) <= 7
                ) {
                    $subscription->status = 'منتهي قريباً';
                } else {
                    $subscription->status = 'ساري';
                }

                $subscription->save();
            }
        });
    }
}
