<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Customer;
use Helper;
use TokenAuth;
use Globals;
use LaramanBeyonic;
use App\Notifications\PaymentMadeNotification;
use Notification;
use App\Models\Notifications;
use App\Models\CustomersLedger;



class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $resp = new ApiResponse();
        $paymentData = array(
            'phonenumber' => '256774014727',
            'amount'      => '1000',
            'currency'    => 'UGX',
            'description' => 'Pay Dallington this money',
            /* Information used by application to identify transaction */
            'metadata'    => "{ 'appId': '2952025', 'xactId': '1000000' }"
        );
        // f62a81d491fb2921d99797f3825c3fbf014b2f17
        
        try {
          $response = LaramanBeyonic::createCollectionRequest($paymentData);
          $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
          $resp->message  = Globals::$STATUS_DESC_SUCCESS;
          $resp->data = $response;
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = null;
        }

        return response()->json($resp);
    }

    public function sendPaymentNotification($paymentData) {

        try{
        $customerSchema = Customer::where('id', $paymentData['id'])->first();
        Notification::send($customerSchema, new PaymentMadeNotification($paymentData));
       }catch(Exception $ex){
        throw $ex;
       }

    }


    public function topupUserAccount(Request $request){
        $resp = new ApiResponse();

        try{
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                
                if($request->filled(['customer_id', 'amount', 'phone_number'])){
                    $customer_id = $request->input('customer_id');
                    $amount = $request->input('amount');
                    $phone_number = $request->input('phone_number');
                    $exists = Customer::where('id', $customer_id)->where('phone_number', $phone_number)->exists();
                    
                    if($exists){

                        $amount = Helper::Numberize($amount);
                        $hasUpdated = Customer::where('id', $customer_id)->increment('account_balance', $amount);
                        $customer = Customer::find($customer_id);
                        
                        if ($hasUpdated) {
                            $customer_name = $customer->first_name. " ".$customer->last_name;
                            // $action = "topped up ".$customer_name." account's with amount worth ".$amount;
                            $action = "topped up your account with amount ".number_format($amount).". Your new balance is ".number_format($customer->account_balance)."";
                            $responseInfo = Helper::getMessage('success', $action);
                            Helper::logActivity($request, ['name' => 'System', 'role' => 'system', 'action' => $action]);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->message = $responseInfo; 

                            // $ledger = new CustomersLedger();
                            $ledgerInput = [
                                'reference' => time().''.$customer_id,
                                'customer_id' => $customer_id,
                                'type' => ucfirst('deposit'),
                                'description' => 'deposited amount '.$amount,
                                'credit' => $amount,
                                'debt' => 0,
                                'balance' => $customer->account_balance,
                                'date' => date('Y-m-d'),
                            ];
                         CustomersLedger::create($ledgerInput);
                            
                        
                        $paymentNotificationData = [
                                        'id' => $customer_id,
                                        'type' => ucfirst('payment'),
                                        'name' => $customer_name,
                                        'body' => 'Congratulations, You have deposited amount '.number_format($amount).' successfully',
                                        'thanks' => 'Thank you',
                                        'offerText' => 'Please keep using the app to get better offers',
                        ];

                        $this->sendPaymentNotification($paymentNotificationData);
                        $data =    ['customer_id' => intval($customer_id),
                                    'customer_name' => $customer_name,
                                    'phonenumber' => $phone_number, 
                                    'account_balance' => $customer->account_balance,
                                    'paid_at' => date('Y-m-d H:i A'),
                                    ];
                        $resp->data = $data;
                        } else {
                            $messageErr = "Unable to top up customer account!";
                            $responseInfo = Helper::getMessage('error', $messageErr);
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message = $responseInfo;
                        }
                        
                    }else{
                        $messageErr = "Failed to find customer with supplied details";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                } else {
                    $messageErr = "Unable to process request: missing parameters";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            }else{
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = "Unauthorized access";
                $resp->data = "Unauthorized access";
                
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        return response()->json($resp);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function getNotifications(Request $request){
        $resp = new ApiResponse();
        try {
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                if($request->filled('id')){
                $customer_id = $request->input('id');
                $notifications = Notifications::where('notifiable_id', $customer_id)->orderBy('created_at', 'desc')->get();
                $notifications->makeHidden(['notifiable_type']);
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                if(count($notifications->toArray()) > 0){
                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                    foreach($notifications as $item){
                        $customer = Customer::find($item->notifiable_id);
                        $item->name = $customer->first_name." ".$customer->last_name;
                        $item->date = date('Y-m-d H:i A', strtotime($item->created_at));
                    }
                }else{
                    $resp->message  = "No transactions found";
                }
                $resp->data = $notifications;
                }else {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request: missing parameters";
                }
                
            }else{
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = "Unauthorized access";
                $resp->data = "Unauthorized access";
                
            }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }

    public function getTransactionHistory(Request $request){
        $resp = new ApiResponse();
        try {
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                if($request->filled('id')){
                $customer_id = $request->input('id');
                $transactions = CustomersLedger::where('customer_id', $customer_id)->orderBy('created_at', 'desc')->get();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                if(count($transactions->toArray()) > 0){
                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                    foreach($transactions as $item){
                        $customer = Customer::find($item->customer_id);
                        $item->name = $customer->first_name." ".$customer->last_name;
                        $item->credit = number_format($item->credit);
                        $item->debt = number_format($item->debt);
                        $item->balance = number_format($item->balance);
                    }
                }else{
                    $resp->message  = "No transactions found";
                }
                $resp->data = $transactions;
                }else {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request: missing parameters";
                }
                
            }else{
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = "Unauthorized access";
                $resp->data = "Unauthorized access";
                
            }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }



}
