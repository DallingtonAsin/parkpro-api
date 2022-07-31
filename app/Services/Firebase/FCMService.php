<?php

namespace App\Services\Firebase;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use App\Models\Customer;
use Helper;

class FCMService
{ 
    public function send($customerId, $notification)
    {
        
        try{
            $customer = Customer::find($customerId);
            $fcmToken = $customer->fcm_token;
            $customerData = Helper::getCustomerData($customerId);

            $response = Http::acceptJson()->withToken(config('fcm.token'))->post(
                'https://fcm.googleapis.com/fcm/send',
                [
                    'to' => $fcmToken,
                    'user' => $customerData,
                    'notification' => $notification,
                    ]
                );
            return $response;
            
            }catch(\Exception $ex){
                throw $ex;
            }
        }
    }