<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use App\Services\Payments\FlutterWaveService;
use App\Services\Transaction\MobileMoney\MMService;
use App\Models\MobileMoneyTransaction;
use Illuminate\Support\Facades\Log;
use Helper;
use Globals;

class FlutterwaveController extends Controller
{
    
    protected $flutterWaveService, $moMoService;
    
    public function __construct(FlutterWaveService $flutterWaveService, MMService $moMoService)
    {
        $this->flutterWaveService = $flutterWaveService;
        $this->moMoService = $moMoService;
    }
    
    /**
    * Obtain Rave mobile money callback information
    * @return void
    */
    public function mobileMoneyCollectionCallback()
    {
        
        // $status = request()->status;
        $transactionID = Flutterwave::getTransactionIDFromCallback();
        $response = Flutterwave::verifyTransaction($transactionID);
        
        $status = $response['status'];
        $message = $response['message'];
        Log::channel('single')->info("Callback response data ".json_encode($response));
        Log::channel('single')->info("Transaction ID ".$transactionID);
        Log::channel('single')->info("I have actually received a response ".$status);

        if($status == "success"){
            
            $tranData = $response['data'];
            $tranStatus = $tranData['status'];
            $requestId = $tranData['tx_ref']; // transReference
            $status = $tranData['status']; // status
            Log::channel('single')->info("Transaction status ".json_encode($tranStatus));

            //if payment is successful
            if ($tranStatus ==  'successful') {
               
                $flwReference = $tranData['flw_ref']; // flwReference
                $transactionId = $tranData['id']; // transactionId

                $amount = $tranData['amount']; // amount
                $currency = $tranData['currency']; // currency
                $charged_amount = $tranData['charged_amount']; //chargedAmount
                $app_fee = $tranData['app_fee']; //appFee
                $merchant_fee = $tranData['merchant_fee']; //merchantFee
                $processor_response = $tranData['processor_response']; // processorResponse
                $auth_model = $tranData['auth_model']; //authModel
                $ip = $tranData['ip']; //transIpAddress
                $narration = $tranData['narration']; // narration

                
                $payment_type = $tranData['payment_type']; // paymentType
                $payment_date = $tranData['created_at']; // paymentDate
                $account_id = $tranData['account_id']; //accountId
                $amount_settled = $tranData['amount_settled']; // amountSettled

                // customer data
                $customer = $tranData['customer']; 
                $customer_id = $customer['id']; // customerId
                $customer_name = $customer['name']; // customerName
                $customer_phone_number = $customer['phone_number']; //customerPhoneNumber
                $customer_email = $customer['email']; // customerEmail
                
                if(MobileMoneyTransaction::where('requestId', $requestId)->exists()){

                    $transaction = MobileMoneyTransaction::where('requestId', $requestId);

                    $isUpdated = $transaction
                                    ->update([
                                        'transactionId' => $transactionId,
                                        'transReference' => $flwReference,
                                        'currency' => $currency,
                                        'chargedAmount' => $charged_amount,
                                        'appFee' => $app_fee,
                                        'merchantFee' => $merchant_fee,
                                        'processorResponse' => $processor_response,
                                        'authModel' => $auth_model,
                                        'narration' => $narration,
                                        'paymentType' => $payment_type,
                                        'paymentDate' => $payment_date ,
                                        'accountId' => $account_id ,
                                        'amountSettled' => $amount_settled ,
                                        'transIpAddress' => $ip,
                                        'transCustomerId' => $customer_id ,
                                        'transCustomerName' => $customer_name,
                                        'transCustomerPhoneNumber' => $customer_phone_number,
                                        'transCustomerEmail' => $customer_email ,
                                        'status' => strtoupper($status),
                                    ]);
                  if($isUpdated){

                    $customerIdInDB = $transaction->value('customerId');

                    $ledger['reference'] = $transactionId;
                    $ledger['type'] = strtoupper('deposit');
                    $ledger['customer_id'] = $customerIdInDB;
                    $ledger['description'] = 'Deposited '.$charged_amount.'';
                    $ledger['credit'] = $charged_amount;
                    $ledger['debt'] = 0;
                    $ledger['balance'] = $charged_amount;
                    $ledger['date'] = date('Y-m-d', strtotime($payment_date));

                    Helper::recordLedgerTransaction($ledger);

                  }
                }
                
            }
            elseif ($status ==  'cancelled'){

                 //Put desired action/code after transaction has been cancelled here
                $mm = MobileMoneyTransaction::where('requestId', $requestId)
                ->update([
                    'status' => strtoupper($status),
                ]);
               
            }
            else{

                //Put desired action/code after transaction has failed here
                $mm = MobileMoneyTransaction::where('requestId', $requestId)
                ->update([
                    'status' => strtoupper($status),
                ]);
                
            }
        }
        
        
        
        
        // Get the transaction from your DB using the transaction reference (txref)
        // Check if you have previously given value for the transaction. If you have, redirect to your successpage else, continue
        // Confirm that the currency on your db transaction is equal to the returned currency
        // Confirm that the db transaction amount is equal to the returned amount
        // Update the db transaction record (including parameters that didn't exist before the transaction is completed. for audit purpose)
        // Give value for the transaction
        // Update the transaction to note that you have given value for the transaction
        // You can also redirect to your success page from here
        
    }
    
}
