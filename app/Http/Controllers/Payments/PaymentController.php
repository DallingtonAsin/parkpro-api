<?php

namespace App\Http\Controllers\Payments;

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
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use App\Services\Payments\FlutterWaveService;
use App\Services\Transaction\MobileMoney\MMService;
use Carbon\Carbon;
use Hash;
use Helper;
use Globals;
use Notification;
use LaramanBeyonic;
use Validator;


class PaymentController extends Controller
{
    
    protected $flutterWaveService, $moMoService, $response;
    
    public function __construct(FlutterWaveService $flutterWaveService,
    MMService $moMoService,
    ApiResponse $response)
    {
        $this->flutterWaveService = $flutterWaveService;
        $this->moMoService = $moMoService;
        $this->response = $response;
    }
    
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
    public function create1(Request $request)
    {
        ;
        $paymentData = array(
            'phonenumber' => '256774014727',
            'amount'      => '1000',
            'currency'    => 'UGX',
            'description' => 'Pay Dallington this money',
            'metadata'    => "{ 'appId': '2952025', 'xactId': '1000000' }"
        );
        
        try {
            $response = LaramanBeyonic::createCollectionRequest($paymentData);
            $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $this->response->message  = Globals::$STATUS_DESC_SUCCESS;
            $this->response->data = $response;
        } catch (\Exception $ex) {
            $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->response->message = $ex->getMessage();
            $this->response->data = null;
        }
        
        return response()->json($this->response);
    }
    
    public function create(Request $request) {
        
        $requestData = [
            "phonenumber" => "+80000000001",
            "amount" => "100.2",
            "currency" => "BXC",
            "metadata" => ["my_id"=>"123ASDAsd123"],
            "send_instructions" => True,
            "subscription_settings" => [
                "start_date"=>"24/05/2019 10:30:00",
                "end_date"=>"24/06/2019 10:30:00",
                "frequency"=>"weekly"
                ]
            ];
            
            try {
                $response = LaramanBeyonic::createCollection($requestData);
                dd($response);
            } catch (\Exception $ex) {
                $error = json_decode($ex->getMessage());
            }
        }
        
        
        
        public function creditCustomerAccount(Request $request, MMService $mmService){
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'country_code' => 'required',
                'phone_number' => 'required',
                'amount' => 'required',
            ]);
            
            try{
                if($validator->fails()){
                    $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                    $this->response->message = $validator->errors()->all();
                }else{
                    $customerId = $request->input("id");
                    $country_code = $request->input("country_code");
                    $phone_number = $request->input("phone_number");
                    $exists = Customer::where("id", $customerId)->exists();
                    if($exists){
                        
                        $customer = Customer::find($customerId);
                        $amount = $request->input("amount");
                        
                        $resp = $mmService->deductFromCustomerMobileMoneyAccount($country_code.''.$phone_number, $amount);
                        
                        if($resp['status'] == "PendingConfirmation"){
                            
                            $isTransactionLoggedInDB = true; // $mmService->LogTransaction($customer, $amount, $resp['status']);
                            if($isTransactionLoggedInDB){
                                
                                $hasCreditedCustomerBal = Helper::creditCustomerAccount($customer->id, $amount);
                                
                                if($hasCreditedCustomerBal){
                                    $isTransactionRecorded = true; //$mmService->recordMobileMoneyTransaction($customerId, $phone_number, $amount);
                                    
                                    if($isTransactionRecorded){
                                        $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                        $this->response->message = 'Mobile money topup successful';
                                        $this->response->data = Helper::getCustomerData($customerId);
                                        $this->response['airtimeResponse'] = $resp;
                                    }else{
                                        $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                                        $this->response->message = "Unable to log mobile money transaction in the customer ledger";
                                    }
                                }else{
                                    $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                                    $this->response->message = "Unable to credit customer balance";
                                }
                            }else{
                                $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                                $this->response->message = "Unable to log transaction in  mobile money transactions table";
                            }
                            
                        }else{
                            $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                            $this->response->message =$this->response->data;
                        }
                        
                    }else{
                        $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                        $this->response->message = "Unable to get customer's identity";
                    }
                    
                }
            }catch(\Exception $ex){
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = json_encode($ex->getMessage());
            }
            return response()->json($this->response, 200);   
            
        }
        
        
        
        public function dispatchAirtime(Request $request, AirtimeService $airtimeService){
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'phone_number' => 'required',
                'amount' => 'required',
            ]);
            
            try{
                if($validator->fails()){
                    $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                    $this->response->message = $validator->errors()->all();
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
                                    $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                    $this->response->message = 'Airtime loaded successfully on '.$phone_number.'';
                                    $this->response->data = Helper::getCustomerData($customerId);
                                    $this->response['airtimeResponse'] = $resp;
                                }else{
                                    $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                                    $this->response->message = "Unable to log airtime transaction in the customer ledger";
                                }
                            }else{
                                $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                                $this->response->message = "Unable to deduct customer balance";
                            }
                            
                        }else{
                            $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                            $this->response->message =$this->response->data;
                        }
                        
                    }else{
                        $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                        $this->response->message = "Unable to get customer's identity";
                    }
                    
                }
            }catch(\Exception $ex){
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $ex->getMessage();
            }
            return response()->json($this->response, 200);   
            
        }
        
        
        public function topupUserAccount(Request $request){
            
            try{
                
                if($request->filled(['customer_id', 'amount', 'country_code', 'phone_number'])){
                    
                    $customer_id = $request->input('customer_id');
                    $amount = $request->input('amount');
                    $country_code = $request->input('country_code');
                    $phone_number = $request->input('phone_number');
                    
                    $exists = Customer::where('id', $customer_id)
                    ->exists();
                    
                    if($exists){
                   
                        $customer = Customer::find($customer_id);
                        $withdrawPhoneNumber = $country_code.''.$phone_number;
                        $charge = $this->flutterWaveService->initializeMobileMoneyPayment($customer, $withdrawPhoneNumber, $amount);
                        
                        if ($charge['status'] == 'success') {

                            $isLogged = $this->moMoService->insertTransactionInDB($request, $customer, $charge);
                            $redirect_link = $charge['data']['redirect'];
                            $respData['link'] = $redirect_link;
                            
                            $this->response->data = $respData;
                            $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $this->response->message = Globals::$STATUS_DESC_SUCCESS;

                        }else{
                            $this->response->data = null;
                            $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                            $this->response->message = Globals::$STATUS_DESC_FAILED;
                        }

                    }else{
                        $messageErr = "Failed to find customer with supplied details";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                        $this->response->message = $responseInfo;
                    }
                } else {
                    $messageErr = "Unable to process request: missing parameters";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                    $this->response->message = $responseInfo;
                }
            } catch (\Exception $ex) {
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $ex->getMessage();
            }
            
            return response()->json($this->response);
        }
        
        
        public function topupUserAccounts(Request $request){
            ;
            
            try{
                
                if($request->filled(['customer_id', 'amount', 'country_code', 'phone_number'])){
                    
                    $customer_id = $request->input('customer_id');
                    $amount = $request->input('amount');
                    $country_code = $request->input('country_code');
                    $phone_number = $request->input('phone_number');
                    
                    $exists = Customer::where('id', $customer_id)
                    ->where('country_code', $country_code)
                    ->where('phone_number', $phone_number)
                    ->exists();
                    
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
                            $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $this->response->message = $responseInfo; 
                            
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
                            
                            
                            $this->response->data = $customer;
                        } else {
                            $messageErr = "Unable to top up customer account!";
                            $responseInfo = Helper::getMessage('error', $messageErr);
                            $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                            $this->response->message = $responseInfo;
                        }
                        
                    }else{
                        $messageErr = "Failed to find customer with supplied details";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                        $this->response->message = $responseInfo;
                    }
                } else {
                    $messageErr = "Unable to process request: missing parameters";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                    $this->response->message = $responseInfo;
                }
            } catch (\Exception $ex) {
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $ex->getMessage();
            }
            return response()->json($this->response);
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
            ;
            try {
                if($request->filled('id')){
                    $customer_id = $request->input('id');
                    $data = $notificationRepo->getUserNotification($customer_id);
                    if(count((array)$data) > 0){
                        $this->response->message  = "No notifications found";
                    }
                    $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $this->response->data = $data;
                    
                }else {
                    $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                    $this->response->message = "Unable to process request: missing parameters";
                }
                
            } catch (\Exception $ex) {
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $ex->getMessage();
                $this->response->data = $ex->getMessage();
            }
            
            return response()->json($this->response);
        }
        
        public function getTransactionHistory(PaymentRepository $paymentRepo, Request $request){
            ;
            try {
                if($request->filled('id')){
                    $customer_id = $request->input('id');
                    $transactions = $paymentRepo->getTransactionRecords($customer_id);
                    if(count($transactions->toArray()) > 0){
                        $this->response->message  = Globals::$STATUS_DESC_SUCCESS;
                    }else{
                        $this->response->message  = "No transactions found";
                    }
                    $tranRecords = $paymentRepo->mapNotifications($transactions);
                    $this->response->data = $tranRecords;
                    $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                }else {
                    $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                    $this->response->message = "Unable to process request: missing parameters";
                }
            } catch (\Exception $ex) {
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $ex->getMessage();
                $this->response->data = $ex->getMessage();
            }
            
            return response()->json($this->response);
        }
        
        public function africasTkngAccountDetails(Request $request, MMService $mmService){
            ;
            try {
                $accountDetailsResp = $mmService->getAccountDetails();
                if($accountDetailsResp){
                    $this->response->message = strtoupper($accountDetailsResp['status']);
                    $this->response->data = $accountDetailsResp['data']->UserData;
                }
                $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
            } catch (\Exception $ex) {
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $ex->getMessage();
                $this->response->data = $ex->getMessage();
            }
            
            return response()->json($this->response);
        }
        
        
    }
