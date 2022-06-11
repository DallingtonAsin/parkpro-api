<?php

namespace App\Services\Transaction\Sms;

use AfricasTalking\SDK\AfricasTalking;

class SmsService{
    
    
    protected $africasTalkingUsername, $africasTalkingApiKey;
    
    public function __construct(){
        $this->africasTalkingUsername = config("app.AfricasTalking_Sandbox_Username");
        $this->africasTalkingApiKey = config("app.AfricasTalking_Sandbox_ApiKey");
    }
    
    
    public function sendMessage($phone_number, $message){
        try{
            
            $service = new AfricasTalking($this->africasTalkingUsername, $this->africasTalkingApiKey);
            $sms     = $service->sms();
            $result  = $sms->send([
                'to'      => $phone_number,
                'message' => $message
            ]);
            return $result;
            
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    public function sendOTP($phone_number, $otp){
        try{
            
            $message = "Your ".config('app.company_name')." OTP is: ".$otp."";
            $response = $this->sendMessage($phone_number, $message);
            return $response;
            
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