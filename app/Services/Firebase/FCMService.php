<?php

namespace App\Services\Firebase;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class FCMService
{ 
    public function send($token, $notification)
    {
        
        try{
            $response = Http::acceptJson()->withToken(config('fcm.token'))->post(
                'https://fcm.googleapis.com/fcm/send',
                [
                    'to' => $token,
                    'notification' => $notification,
                    ]
                );
            return $response;
            
            }catch(\Exception $ex){
                throw $ex;
            }
        }
    }