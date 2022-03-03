<?php


namespace App\Services\Transaction\MobileMoney;

use AfricasTalking\SDK\AfricasTalking;
use App\Models\Customer;
use App\Models\MobileMoneyTransaction;
use Helper;
use Carbon\Carbon;

class MMService{

    public function deductFromCustomerMobileMoneyAccount($phone_number, $amount){
        try{
            $username = config("app.AfricasTalking_Sandbox_Username"); 
            $apiKey   = config("app.AfricasTalking_Sandbox_ApiKey");
            $service       = new AfricasTalking($username, $apiKey);
            $payments = $service->payments();
            $productName = "";
            $idempotencyKey =  Helper::generateRandomNumber();
            $transactionDetails = array(
                "productName" => "ParkPro",
                "providerChannel" => "parkpro",
                "phoneNumber" => $phone_number,
                "currencyCode" => "UGX",
                "amount" => intval($amount),
                "metadata" => [
                    "TID" => $idempotencyKey,
                    "phone_number" => $phone_number,
                ]
            );
            $options = [
                "idempotencyKey" => $idempotencyKey
            ];
            $response   = $payments->mobileCheckout($transactionDetails, $options);
            return $response;
        }catch(Exception $ex){
            throw $ex;
        }
    }

    public function recordMobileMoneyTransaction($customerId, $phone_number, $amount){
        try{
            $isInserted = false;
            if(Customer::where("id", $customerId)->exists()){
                $customer = Customer::find($customerId);
                $description = "deposited money ".$amount." on parkpro account.";
              
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


    public function LogTransaction($customer, $amount, $status){
        $isLogged = false;
        try{
            $mm = new MobileMoneyTransaction();
            $mm->customer_id = $customer->id;
            $mm->phone_number = $customer->phone_number;
            $mm->type = "Mobile Money Checkout";
            $mm->amount = $amount;
            $mm->status = $status;
            $mm->date = Carbon::now();
            if($mm->save()){
                $isLogged = true;
            }
        }catch(Exception $ex){
            throw $ex;
        }
    }



}
