<?php

namespace App\Services\Transaction\Sms;

use AfricasTalking\SDK\AfricasTalking;

class SmsService{


   public function sendOTP($phone_number, $otp){
    try{
        $username = config("app.AfricasTalking_Sandbox_Username"); 
        $apiKey   = config("app.AfricasTalking_Sandbox_ApiKey");
        $service       = new AfricasTalking($username, $apiKey);
        $sms      = $service->sms();
        $result   = $sms->send([
            'to'      => $phone_number,
            'message' => "Your ".config('app.company_name')." verification code is: ".$otp.""
        ]);
        return $result;
    }catch(\Exception $ex){
        throw $ex;
    }
}

public function generateNumericOTP($n) { 
    $generator = "1357902468"; 
    $result = ""; 
    for ($i = 1; $i <= $n; $i++) { 
        $result .= substr($generator, (rand()%(strlen($generator))), 1); 
    }  
    return $result; 
} 






}