<?php

namespace App\Services\Firebase;

use Illuminate\Support\Facades\Http;

class FCMService
{ 
    public function send($token, $notification)
    {
        Http::acceptJson()->withToken(config('fcm.token'))->post(
            'https://fcm.googleapis.com/fcm/send',
            [
                'to' => $token,
                'notification' => $notification,
            ]
        );
    }
}