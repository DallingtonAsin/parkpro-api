<?php


namespace App\Services\Transaction\MobileMoney;

use AfricasTalking\SDK\AfricasTalking;
use App\Models\Customer;
use App\Models\MobileMoneyTransaction;
use Helper;
use Carbon\Carbon;

class MMService{
    
    private $africastkngUsername, $africastkngApiKey;
    
    public function __construct(){
        $this->africastkngUsername = config("app.AfricasTalking_Sandbox_Username"); 
        $this->africastkngApiKey = config("app.AfricasTalking_Sandbox_ApiKey");
    }
    
    public function deductFromCustomerMobileMoneyAccount($phone_number, $amount){
        try{
            
            $service       = new AfricasTalking($this->africastkngUsername, $this->africastkngApiKey);
            $payments = $service->payments();
            $productName = "";
            $idempotencyKey =  Helper::generateRandomNumber();
            $transactionDetails = array(
                "username" => $this->africastkngUsername,
                "productName" => "Parkpro",
                "providerChannel" => "525900",
                "phoneNumber" => $phone_number,
                "currencyCode" => "UGX",
                "amount" => floatval($amount),
                "metadata" => array(
                    "TID" => $idempotencyKey,
                    "phone_number" => $phone_number,
                    )
                );
                $options = array(
                    "idempotencyKey" => $idempotencyKey
                );
                // dd($transactionDetails);
                $response   = $payments->mobileCheckout($transactionDetails);
                dd($response);
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
        
        public function getAccountDetails(){
            try{
                $service       = new AfricasTalking($this->africastkngUsername, $this->africastkngApiKey);
                $application = $service->application();
                $accountDetails = $application->fetchApplicationData();
                return $accountDetails;
                
            }catch(\Exception $ex){
                throw $ex;
            }
        }
        
        
        
    }
