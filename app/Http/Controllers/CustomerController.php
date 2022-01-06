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
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomerResource;
use App\Repositories\CustomerRepository;
use App\Helpers\formattedApiResponse;
use Helper;
use Globals;
use Mail;

class CustomerController extends Controller
{

     public $response = [];


     public function __constructor(){
            $this->response = new ApiResponse();
     }


    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(CustomerRepository $customerRepo)
    {
       $customers = $customerRepo->getCustomers();
       return formattedApiResponse::getJson($customers);

    }
 
    private function getCustomerId($phone_number){
        $customerId = Customer::where('phone_number',$phone_number)
        ->value('id');
        
        return $customerId;
    }
    
    private function findAccountStatus($id){
        
        $accountStatus = Customer::where('id', $id)->value('is_active');
        return $accountStatus;
        
    }

    public function customerLogin(Request $request){
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $validator->errors()->all();
        }else{
            if(auth()->guard('customer')->attempt([
                'phone_number' => request('phone_number'),
                'password' => request('password'),
            ])){
                config(['auth.guards.api.provider' => 'customer']);
                $customer = Customer::select('customers.*')
                           ->find(auth()->guard('customer')->user()->id);
                if($customer->account_balance >= 1000){
                    $customer->account_balance = number_format($customer->account_balance);
                }
                if(!empty($customer->image)){
                    $customer->image =  Storage::disk('public')->url($customer->image);
                }
                $customer['access_token'] = $customer->createToken('Customer'.$customer->phone_number, ['customer'])->accessToken;
                $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                $this->response['message'] = 'Login successful';
                $this->response['data'] = $customer;

            }else{
                $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                $this->response['message'] = 'Invalid login details';
            }

        }
         return response()->json($this->response, 200);
    }


    

    public function authenticate(Request $request){ 
        if($request->isMethod('post')){
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
            return response()->json($this->apiResponse, 200);
        }
        
    }
    
    public function login(Request $request)
    {
        
        try{
               
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
        } catch (\Exception $ex) {
            $statusCode = Globals::$STATUS_CODE_ERROR;
            $message = $ex->getMessage();
        }
        
        $resp->statusCode = $statusCode;
        $resp->message = $message;
        
        return response()->json($resp);
    }


    public function register(Request $request){

          $validatedData = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required',
            'password' => 'min:8|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:8',
        ]);

        if($validatedData->fails()){
            $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
            $this->response['message'] =  $validatedData->errors()->all();
        }else{

                    $first_name = trim($request->input('first_name'));
                    $last_name = trim($request->input('last_name'));
                    $phone_number = trim($request->input('phone_number'));
                    $password = $request->input('password');

                        $doesCustomerExist = Customer::where('phone_number', '=', $phone_number)->exists();
                        if(!$doesCustomerExist){

                            $hashedPassword = Hash::make($password);
                            $data = [
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'phone_number' => $phone_number,
                            'password' => $hashedPassword
                            ];
                           
                            $customer = Customer::create($data);

                            if($customer){
                                $customer_name = $first_name." ".$last_name;
                                $role = 'customer';
                                $action =  "New customer ".$customer_name." registered";
                                $message = "You have been successfully registered as ".$role.", thank you!";
                                Helper::logActivity($request, ['name' => 'system', 'role' => $role, 'action' => $action]);
                                
                                $this->response['message'] = $message;
                                $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                                // $cust = Customer::where('phone_number', '=', $phone_number)->first();
                                // $customerData = Helper::getCustomerData($cust->id); 
                                $customer->access_token = $customer->createToken('Customer'.$customer->phone_number, ['customer'])->accessToken;
                                $this->response['data'] = $customer;
                            }
                            else
                            {
                                $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                                $this->resp['message'] = "Customer registration failed!";
                            }
                        } else{
                            $message = "Customer with phone number ".$phone_number." has been already registered";
                            $responseInfo = Helper::getMessage('error', $message);
                            $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                            $this->response['message']  = $responseInfo;
                        }
        }
         return response()->json($this->response, 200);

    }


      public function updateProfile(Request $request){

         $validator = Validator::make($request->all(), [
            'id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required',
        ]);

               if($validator->fails())
               {
                    $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                    $this->response['message'] =  $validator->errors()->all();
                }
                else{
                   
                    $customerId = $request->input('id');
                    $first_name = trim($request->input('first_name'));
                    $last_name = trim($request->input('last_name'));
                    $phone_number = trim($request->input('phone_number'));
                    $role = 'Customer';
                    
                    if($request->has('email') && $request->filled('email')){
                        $email = $request->input('email');
                    }else{
                        $email = null;
                    }
                   
                        $customer = Customer::find($customerId);
                        $hasUpdated = Customer::where('id', '=', $customerId)
                        ->update([
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'phone_number' => $phone_number,
                            'email' => $email
                        ]);
                        
                        if($hasUpdated){
                            $customer = Customer::find($customerId);
                            $action = "updated your profile";
                            $this->response['message'] = Helper::getMessage('success', $action);
                            $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                            $customerData = Helper::getCustomerData($customerId);
                            $this->response['data'] = $customerData;
                        }else{
                            $this->response['message'] ="Unable to update customer account profile!";
                            $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                        }
                      
                    }
              
      
        
                      return response()->json($this->response, 200);
        
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
                            'email' => $email
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
        $customer = Customer::find($id);
        return response()->json($customer, 200);
    }
    
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $customer)
    {
        $customer->update($request->all());
        return response(['ceo' => new CustomerResource($customer), 'message' => 'Updated successfully'], 200);
    }
    
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($customer)
    {
        $customer->delete();
        return response(['message' => 'Deleted successfully']);
    }
    
    
    public function findCustomer(Request $request){ 
        if($request->isMethod('get')){
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
              return response()->json($this->apiResponse, 200);
        }
        
    }
    
    
    public function changePassword(Request $request)
    {
        $resp = new ApiResponse();
        try {
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
                if($request->filled('id') && $request->filled('phone_number') && $request->filled('extension') && $request->has('image')){
                    $file = $request->file('image');
                    $customer_id = $request->input('id');
                    $phone_number = $request->input('phone_number');
                    $file_extension = $request->input('extension');

                    $doesCustomerExist = Customer::where('id', $customer_id)->where('phone_number', $phone_number)->exists();
                    if($doesCustomerExist){
                        $customer = Customer::find($customer_id);
                        if(!empty($customer->image)){
                            Storage::disk('public')->delete($customer->image);
                        }
                        $fileName = $customer_id.''.time().'.'.$file_extension;
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
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }
    
    
}
