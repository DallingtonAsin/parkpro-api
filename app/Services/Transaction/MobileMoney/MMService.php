<?php


namespace App\Services\Transaction\MobileMoney;

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\MobileMoneyTransaction;
use Helper;
use Carbon\Carbon;
use Globals;

class MMService{
    
    private $africastkngUsername, $africastkngApiKey;
    
    public function __construct(){
        $this->africastkngUsername = config("app.AfricasTalking_Sandbox_Username"); 
        $this->africastkngApiKey = config("app.AfricasTalking_Sandbox_ApiKey");
    }
    
    
    public function insertMoMoTransactionInDB2($customerId, $phone_number, $amount){
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
    
    
    public function insertTransactionInDB(Request $request, $customer, $transaction){
        
        try{
            $isLogged = false;
            
            $mm = new MobileMoneyTransaction();
            $mm->customerId = $customer->id;
            $mm->type = "Collection";
            $mm->phoneNumber = $customer->country_code.''.$customer->phone_number;
            $mm->amount = $transaction['data']['amount'];
            $mm->orderId = $transaction['data']['order_id'];
            $mm->tranReference = $transaction['data']['reference'];
            $mm->status = Globals::$PENDING_STATUS;
            $mm->date = Carbon::now();
            $mm->userIpAddress = $request->ip();
            $mm->serverIpAddress = $request->getClientIp();
            ;
            
            if($mm->save()){
                $isLogged = true;
            }
            
            return $isLogged;
            
        }catch(Exception $ex){
            throw $ex;
        }
    }
    
    
    public function updateTransaction($tranReference){
        try{
            
            $mm = MobileMoneyTransaction::where('tranReference', $tranReference)
            // ->where('phoneNumber', $phoneNumber)
            ->update([
                'status' => Globals::$SUCCESS_STATUS,
            ]);
            
        }catch(\Exception $ex){
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
