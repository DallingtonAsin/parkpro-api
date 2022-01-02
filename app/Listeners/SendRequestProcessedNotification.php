<?php

namespace App\Listeners;

use App\Events\RequestProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRequestProcessedNotification
{
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
     * @param  RequestProcessed  $event
     * @return void
     */
    public function handle(RequestProcessed $event)
    {
        //
    }
}
