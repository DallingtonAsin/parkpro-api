<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Role;
use App\Helpers\ApiResponse;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use App\Mail\RegistrationMailSender;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Validator;
use Helper;
use Globals;
use TokenAuth;
use Mail;

class CustomerController extends Controller
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
    public function create()
    {
        //
    }
    
    public function getCustomerId($phone_number){
        $customerId = Customer::where('phone_number',$phone_number)
        ->value('id');
        
        return $customerId;
    }
    
    
    private function findAccountStatus($id){
        
        $accountStatus = Customer::where('id', $id)->value('is_active');
        return $accountStatus;
        
    }
    
    public $apiResponse = [];
    public function authenticate(Request $request){ 
        if($request->isMethod('post')){
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                if ($request->filled(['phone_number', 'password'])) {
                    
                    $phone_number = $request->input('phone_number');
                    $password = $request->input('password');
                    
                    $doesCustomerExist = Customer::where('phone_number', $phone_number)->exists();
                    if($doesCustomerExist){
                        $customer = Customer::all()->firstWhere('phone_number', '=', $phone_number);
                        $checkPass = Hash::check($password, $customer['password']);
                        if (!empty($checkPass) && $checkPass == '1') {
                            $customerAuthData = Helper::getCustomerData($customer['id']);
                            $this->apiResponse['statusCode'] = 1;
                            $this->apiResponse['message'] = 'customer logged in successfully';
                            $this->apiResponse['data'] = $customerAuthData;
                        } else {
                            $this->apiResponse['statusCode'] = 0;
                            $this->apiResponse['message'] = 'Invalid credentials';
                        }
                        
                    } else {
                        $this->apiResponse['statusCode'] = 0;
                        $this->apiResponse['message'] = 'Invalid credentials';
                    }
                } else {
                    $this->apiResponse['statusCode'] = 0;
                    $this->apiResponse['message'] = "Unable to process request: missing parameters";
                }
            }else{
                $this->apiResponse['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->apiResponse['message'] = "Unauthorized access";   
            }
            return response()->json($this->apiResponse, 200);
        }
        
    }
    
    public function authenticate1(Request $request)
    {
        
        try{
            $resp = new ApiResponse();
            
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                
                if($request->filled(['phone_number', 'password'])){
                    
                    $phone_number = $request->input('telephone');
                    $password = $request->input('password');
                    ($request->has('remember'))
                    ? $remembered = true
                    : $remembered = false;
                    
                    if(Auth::attempt(['phone_number' => $phone_number, 'password' =>  $password], $remembered)){
                        
                        $customerId = $this->getCustomerId($phone_number);
                        $status = $this->findAccountStatus($customerId);
                        if($status == 0){
                            $statusCode = Globals::$STATUS_CODE_FAILED;
                            $message = 'Your account is inactivated, see admin';
                        }
                        if($status == 1){
                            $statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $action = $message =  "logged into the app";
                            $resp->data = Auth::user();
                            $customer_name = Auth::user()->first_name." ".Auth::user()->last_name;
                            Helper::logActivity($request, ['name' => $customer_name, 'role' => 'customer', 'action' => $action]);
                        }
                    }else
                    {
                        $statusCode = Globals::$STATUS_CODE_FAILED;
                        $message = 'Invalid login credentials';
                    }
                }else{
                    $statusCode = Globals::$STATUS_CODE_ERROR;
                    $message = "Unable to process request: missing parameters";
                }
            }else{
                $statusCode = Globals::$STATUS_CODE_ERROR;
                $message = "Unauthorized access";
                
            }
        } catch (\Exception $ex) {
            $statusCode = Globals::$STATUS_CODE_ERROR;
            $message = $ex->getMessage();
        }
        
        $resp->statusCode = $statusCode;
        $resp->message = $message;
        
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
        $resp = new ApiResponse();
        $method = "CustomerController@store";
        try{
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                if($request->filled(['first_name','last_name', 'phone_number','password'])){
                    $first_name = trim($request->input('first_name'));
                    $last_name = trim($request->input('last_name'));
                    $phone_number = trim($request->input('phone_number'));
                    $password = trim($request->input('password'));
                    $role = 'Customer';
                    
                    if($request->has('email') && $request->filled('email')){
                        $email = $request->input('email');
                    }else{
                        $email = null;
                    }
                    
                    if($request->filled('user_id')) {
                        $customerId = $request->input('user_id');
                        $customer = Customer::find($customerId);
                        if($request->hasFile('photo')){
                            $photo = $request->file('photo');
                            $k = $this->saveFile($photo, $role);
                            $photo_path = $k['file_path'];
                            $photo_name = $k['filename'];
                            $image = $photo_path . '/' . $photo_name;
                            
                        }else{
                            $photo_path = null;
                            $photo_name = null;
                            $image = null;
                        }
                        
                        $hasUpdated = Customer::where('id', '=', $customerId)
                        ->update([
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'phone_number' => $phone_number,
                            'email' => $email,
                            // 'password' => $password,
                        ]);
                        
                        if($hasUpdated){
                            $customer = Customer::find($customerId);
                            $action = "updated profile";
                            $resp->message = Helper::getMessage('success', $action);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $customerData = Helper::getCustomerData($customerId);
                            $resp->data = $customerData;
                        }else{
                            $resp->message ="Unable to update customer account profile!";
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        }
                        
                        
                    }else {
                        
                        $customer = new Customer();
                        $customer_name = $first_name." ".$last_name;
                        $password = Hash::make($password, ['rounds' => 12]);
                        $count = Customer::where('phone_number', '=', $phone_number)->count();
                        if($count == 0){
                            if($request->hasFile('photo')){
                                $photo = $request->file('photo');
                                $k = $this->saveFile($photo, $role);
                                $photo_path = $k['file_path'];
                                $photo_name = $k['filename'];
                                $image = $photo_path . '/' . $photo_name;
                            }else{
                                $photo_path = null;
                                $photo_name = null;
                                $image = null;
                            }
                            
                            $customer->first_name = $first_name;
                            $customer->last_name = $last_name;
                            $customer->phone_number = $phone_number;
                            $customer->email = $email;
                            $customer->password = $password;
                            
                            $customer->image = $photo_path;
                            if($customer->save()){
                                
                                $action =  "New customer ".$customer_name." registered";
                                $message = "You have been successfully registered as ".$role.", thank you!";
                                Helper::logActivity($request, ['name' => 'system', 'role' => $role, 'action' => $action]);
                                
                                $resp->message = $message;
                                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                $cust = Customer::where('phone_number', '=', $phone_number)->first();
                                $customerData = Helper::getCustomerData($cust->id);
                                $resp->data = $customerData;
                            }
                            else
                            {
                                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                $resp->message = "Customer registration failed!";
                            }
                        } else{
                            $message = "Customer with phone number ".$phone_number." has been already registered";
                            $responseInfo = Helper::getMessage('error', $message);
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message  = $responseInfo;
                            
                        }
                        
                    }
                } else{
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request: missing parameters";
                }
                
            }else{
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = "Unauthorized access";
                
            }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $message = $ex->getMessage();
        }
        
        $dataArr = array("code" => $resp->statusCode,
        "message" => $resp->message,
        "method" => $method);
        Helper::LogRequest($request, $dataArr);
        return response()->json($resp);
        
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
    
    
    public function findCustomer(Request $request){ 
        if($request->isMethod('get')){
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                if($request->has('id')) {
                    $customer_id = $request->input('id');
                    $doesCustomerExist = Customer::where('id', $customer_id)->exists();
                    if ($doesCustomerExist) {
                        $customerData = Helper::getCustomerData($customer_id);
                        $this->apiResponse['statusCode'] = 1;
                        $this->apiResponse['message'] = 'customer details found';
                        $this->apiResponse['data'] = $customerData;
                    } else {
                        $this->apiResponse['statusCode'] = 0;
                        $this->apiResponse['message'] = 'Unable to find customer details';
                    }
                } else {
                    $this->apiResponse['statusCode'] = 0;
                    $this->apiResponse['message'] = "Unable to process request";
                }
            }else{
                $this->apiResponse['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->apiResponse['message'] = "Unauthorized access";   
            }
            return response()->json($this->apiResponse, 200);
        }
        
    }
    
    
    
    
    
    
    
    public function changePassword(Request $request)
    {
        $resp = new ApiResponse();
        try {
            $authToken  = $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                if($request->filled('id') && $request->filled('current_password') 
                && $request->filled('new_password') && $request->filled('confirm_password')  ){
                    
                    $customer_id = $request->input('id');
                    $current_password = $request->input('current_password');
                    $new_password = $request->input('new_password');
                    $confirm_password = $request->input('confirm_password');
                    $doesCustomerExist = Customer::where('id', $customer_id)->exists();

                    if($doesCustomerExist){
                    $customer = Customer::find($customer_id);

                    $old_password = $customer->password;

                    if($new_password === $confirm_password){
                        if(Hash::check($current_password, $old_password)){
                            $customer->password = Hash::make($new_password);
                            if($customer->save()){
                                $action = "changed your password";
                                Helper::logActivity($request, ['name' => $customer->first_name." ".$customer->last_name,
                                 'role' => 'customer',
                                 'action' => "changed password" ]);
                                $message = Helper::getMessage('success', $action);
                                $customerData = Helper::getCustomerData($customer->id);
                                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                $resp->message  = $message;
                                $resp->data  = $customerData;

                            }else{
                                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                $resp->message = "Unable to change your password";
                            }  
                        }else{
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message = "Incorrect old password";
                        }

                    }else{
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = "Enter new matching passwords";
                    }

                }else{
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Customer with supplied details does not exist"; 
                }
                }
                else{
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request";
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
    
    
    public function uploadProfilePicture(Request $request){
        $resp = new ApiResponse();
        try {
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                if($request->filled('id') && $request->filled('phone_number') && $request->has('image')){
                    $file = $request->file('image');
                    $customer_id = $request->input('id');
                    $phone_number = $request->input('phone_number');
                    $doesCustomerExist = Customer::where('id', $customer_id)->where('phone_number', $phone_number)->exists();
                    if($doesCustomerExist){
                        $customer = Customer::find($customer_id);
                        if(!empty($customer->image)){
                            Storage::disk('public')->delete($customer->image);
                        }
                        $file_extension = $request->file->getClientOriginalExtension();
                        $fileName = $customer_id."".time().'.'.$file_extension;
                        $filePath = $file->storeAs('images', $fileName, 'public');

                        $input = ['image' => $filePath];
                        $hasUpdated = Customer::where('id', $customer_id)->where('phone_number', $phone_number)->update($input);
                        
                        if($hasUpdated){
                            $action = "updated your profile picture";
                            $message = Helper::getMessage('success', $action);
                            $customerData = Helper::getCustomerData($customer_id);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->message  = $message;
                            $resp->data = $customerData;
                        }else{
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message = "Unable to update your profile picture";
                        }
                    }else{
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = "Unable to find customer with supplied details";
                    }
                }
                else{
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request";
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
