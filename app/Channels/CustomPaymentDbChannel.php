<?php

namespace App\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel as BaseDatabaseChannel;
use Illuminate\Notifications\Notification;

class CustomPaymentDbChannel extends BaseDatabaseChannel
{

  public function send($notifiable, Notification $notification)
  {
   
    $data = $notification->toCustomDb();
    return $notifiable->routeNotificationFor('database')->create([
        'id' => $notification->id,
        'type' => $data['type'], 
        // 'notifiable_type' => $data['notifiable_type'], 
        // 'notifiable_id' => $data['id'], 
        'data' => $data['body'], 
        'read_at' => null,
    ]);
  }

}