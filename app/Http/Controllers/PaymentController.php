<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Storage;
use App\Notifications\PaymentMadeNotification;
use App\Models\Customer;
use App\Models\CustomersLedger;
use App\Jobs\ProcessCustomerPayment;
use App\Repositories\NotificationRepository;
use App\Repositories\PaymentRepository;
use App\Services\Transaction\Airtime\AirtimeService;
use Carbon\Carbon;
use Hash;
use Helper;
use Globals;
use Notification;
use LaramanBeyonic;
use Validator;


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
            'metadata'    => "{ 'appId': '2952025', 'xactId': '1000000' }"
        );
        
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


    public function dispatchAirtime(Request $request, AirtimeService $airtimeService){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'phone_number' => 'required',
            'amount' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->response['message'] = $validator->errors()->all();
            }else{
                $customerId = $request->input("id");
                $phone_number = $request->input("phone_number");
                $exists = Customer::where("id", $customerId)->exists();
                if($exists){
                    $customer = Customer::find($customerId);
                    $amount = $request->input("amount");
                    $resp = $airtimeService->sendAirtime($phone_number, $amount);
                    if($resp['status'] == "success"){
                        $hasDeductedCustomerBal = Helper::deductCustomerBalance($customer->id, $amount);
                        if($hasDeductedCustomerBal){
                            $isTransactionRecorded = $airtimeService->recordAirtimeTransaction($customerId, $phone_number, $amount);
                            if($isTransactionRecorded){
                                $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                                $this->response['message'] = 'Airtime sent successfully to '.$phone_number.'';
                                $this->response['data'] = $resp;
                            }else{
                                $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                                $this->response['message'] = "Unable to log airtime transaction in the customer ledger";
                            }
                        }else{
                            $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                            $this->response['message'] = "Unable to deduct customer balance";
                        }

                    }else{
                        $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                        $this->response['message'] =$resp['data'];
                    }
                 
                }else{
                    $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                    $this->response['message'] = "Unable to get customer's identity";
                }

            }
        }catch(\Exception $ex){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $ex->getMessage();
        }
        return response()->json($this->response, 200);   

    }




    public function topupUserAccount(Request $request){
        $resp = new ApiResponse();

        try{
                
                if($request->filled(['customer_id', 'amount', 'phone_number'])){

                    $customer_id = $request->input('customer_id');
                    $amount = $request->input('amount');
                    $phone_number = $request->input('phone_number');
                    $exists = Customer::where('id', $customer_id)->where('phone_number', $phone_number)->exists();
                    if($exists){
                        $transData = [
                            'customer_id' => $customer_id,
                            'amount' => $amount,
                        ];
                        
                         ProcessCustomerPayment::dispatch($transData)->onQueue('payments');
                        // dd($result);
                        // if($result->hasProcessed){
                            $customer = Customer::find($customer_id);
                            $customer_name = $customer->first_name." ".$customer->last_name;
                            $action = "topped up your account with amount ".number_format($amount).". Your new balance is ".number_format($customer->account_balance)."";
                            $responseInfo = Helper::getMessage('success', $action);
                            Helper::logActivity($request, ['name' => 'System', 'role' => 'system', 'action' => $action]);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->message = $responseInfo; 
                            $resp->data = Helper::getCustomerData($customer_id);
                        // }else{
                        //     $messageErr = "Unable to top up customer account!";
                        //     $responseInfo = Helper::getMessage('error', $messageErr);
                        //     $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        //     $resp->message = $responseInfo;
                        // }
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
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        return response()->json($resp);
    }


    public function topupUserAccounts(Request $request){
        $resp = new ApiResponse();

        try{
                
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
                                'description' => ucfirst('deposit'),
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

                        $this->storePaymentNotification($paymentNotificationData);

                         if(isset($customer->image)){
                            $customer_image =  Storage::disk('public')->url($customer->image);
                          } else{
                            $customer_image = $customer->image;
                          }
                          
                    
                        $resp->data = $customer;
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
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        return response()->json($resp);
    }

    private function storePaymentNotification($paymentData) {
        try{
        $customerSchema = Customer::where('id', $paymentData['id'])->first();
        Notification::send($customerSchema, new PaymentMadeNotification($paymentData));
       }catch(Exception $ex){
        throw $ex;
       }
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


    public function getNotifications(NotificationRepository $notificationRepo, Request $request){
        $resp = new ApiResponse();
        try {
                if($request->filled('id')){
                   $customer_id = $request->input('id');
                   $data = $notificationRepo->getUserNotification($customer_id);
                   if(count((array)$data) > 0){
                      $resp->message  = "No notifications found";
                   }
                   $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                   $resp->data = $data;

                }else {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request: missing parameters";
                }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }

    public function getTransactionHistory(PaymentRepository $paymentRepo, Request $request){
        $resp = new ApiResponse();
        try {
                if($request->filled('id')){
                $customer_id = $request->input('id');
                $transactions = $paymentRepo->getTransactionRecords($customer_id);
                if(count($transactions->toArray()) > 0){
                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                }else{
                    $resp->message  = "No transactions found";
                }
                $tranRecords = $paymentRepo->mapNotifications($transactions);
                $resp->data = $tranRecords;
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                }else {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request: missing parameters";
                }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }



}
