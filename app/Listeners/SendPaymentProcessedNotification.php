<?php

namespace App\Listeners;

use App\Events\PaymentProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\PaymentMadeNotification;
use Notification;
use App\Models\Customer;

class SendPaymentProcessedNotification implements ShouldQueue
{


    public $connection = 'database';
    public $queue = 'payments';
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  PaymentProcessed  $event
     * @return void
     */
    public function handle(PaymentProcessed $event)
    {
        $notification = $event->notification;
        $customerSchema = Customer::where('id', $notification['id'])->first();
        Notification::send($customerSchema, new PaymentMadeNotification($notification));
    }

   
}
