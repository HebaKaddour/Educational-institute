<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Subscription;
use Illuminate\Console\Command;

class UpdateSubscriptionsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحديث حالة الاشتراكات تلقائياً';

    /**
     * Execute the console command.
     */
public function handle()
    {
        $today = Carbon::today();

        Subscription::chunk(200, function ($subscriptions) use ($today) {
            foreach ($subscriptions as $subscription) {

                if ($today->gt($subscription->end_date)) {
                    $subscription->status = 'منتهي';
                } elseif ($today->diffInDays($subscription->end_date) <= 7) {
                    $subscription->status = 'منتهي قريباً';
                } else {
                    $subscription->status = 'ساري';
                }

                $subscription->save();
            }
        });

        $this->info('Subscription statuses updated successfully.');
    }
}
