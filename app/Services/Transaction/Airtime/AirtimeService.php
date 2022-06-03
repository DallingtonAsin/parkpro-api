<?php

namespace App\Services\Transaction\Airtime;

use KingFlamez\Rave\Facades\Rave as Flutterwave;

class AirtimeService{
    
    
    public function sendAirtime($phone_number, $amount){
        try{
            $username = config("app.AfricasTalking_Sandbox_Username"); 
            $apiKey   = config("app.AfricasTalking_Sandbox_ApiKey");
            $service       = new AfricasTalking($username, $apiKey);
            $airtime      = $service->airtime();
            
            $receiver = array(
                "phoneNumber" => $phone_number,
                "currencyCode" => "UGX",
                "amount" => intval($amount),
            );
            $recipients = array(
                $receiver
            );
            $options = [
                "idempotencyKey" => Helper::generateRandomNumber()
            ];
            $parameters = array("recipients" => $recipients, []);
            $response   = $airtime->send($parameters, $options);
            return $response;
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    
    public function recordAirtimeTransaction($customerId, $phone_number, $amount){
        try{
            $isInserted = false;
            if(Customer::where("id", $customerId)->exists()){
                $customer = Customer::find($customerId);
                if($customer->phone_number  == $phone_number){
                    $description = "deposited airtime ".$amount." on your phone number ".$phone_number."";
                }else{
                    $description = "deposited airtime ".$amount." to the phone number ".$phone_number."";
                }
                $transactionDetails = [
                    'reference' => time().''.$customer->id,
                    'customer_id' => $customer->id,
                    'type' => ucfirst('airtime'),
                    'description' => ucfirst($description),
                    'credit' => 0,
                    'debt' => $amount,
                    'balance' => $customer->account_balance,
                    'date' => date('Y-m-d'),
                ];
                if(Helper::recordTransaction($transactionDetails)){
                    $isInserted = true;
                }
            }
            return $isInserted;
            
        }catch(Exception $ex){
            throw $ex;
        }
    }
    
  
    
    
    
    
}