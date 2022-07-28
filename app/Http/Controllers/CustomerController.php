<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Role;
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
use App\Services\Transaction\Sms\SmsService;
use App\Helpers\formattedApiResponse;
use Helper;
use Globals;
use Mail;

class CustomerController extends Controller
{
    
    protected $smsService, $response;
    
    public function __construct(SmsService $smsService){
        $this->smsService = $smsService;
    }
    
    
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(CustomerRepository $customerRepo)
    {
        try{
            $customers = $customerRepo->getCustomers();
            return formattedApiResponse::getJson($customers);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }  
        
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
    
    
    public function InsertOrUpdateCustomerOTP(Request $request){
        
        $validator = Validator::make($request->all(), [
            'countryIsoCode' => 'required',
            'countryCode' => 'required',
            'number' => 'required',
            'uniqueDeviceId' => 'required',
            'deviceToken' => 'required',
            'currentVersion' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
            }else{
                
                $country_iso_code = request('countryIsoCode');
                $country_code = request('countryCode');
                $phone_number = request('number');
                $unique_device_id = request('uniqueDeviceId');
                $fcm_token = request('deviceToken');
                $current_version = request('currentVersion');
                
                
                $request->filled('ipAddress')
                ? $ip_address = $request->input('ipAddress')
                : $ip_address = null;
                
                $request->filled('deviceLanguage')
                ? $device_language = $request->input('deviceLanguage')
                : $device_language = null;
                
                
                $otp = $this->smsService->generateNumericOTP(4);
                $exists = Customer::where("country_code", "=", $country_code)
                ->where("phone_number", "=", $phone_number)
                ->exists();
                if($exists){
                    $customer = Customer::where("country_code", "=", $country_code)
                    ->where("phone_number", "=", $phone_number)
                    ->first();
                    $customer->update(['unique_device_id' => $unique_device_id, 
                    'ip_address' => $ip_address,
                    'current_version' => $current_version,
                    'fcm_token' => $fcm_token,
                    'device_language' => $device_language,
                ]);
                
                $data = $this->sendVerificationCode($customer, $otp);
                return Helper::sendOkHttpResponse(['message' => 'OTP inserted successfully', 'data' => $data]);
                
            }else{
                
                $customer = new Customer();
                $customer->country_iso_code = $country_iso_code;
                $customer->country_code = $country_code;
                $customer->phone_number = $phone_number;
                $customer->otp = $otp;
                $customer->unique_device_id = $unique_device_id;
                $customer->fcm_token = $fcm_token;
                $customer->device_language = $device_language;
                $customer->current_version = $current_version;
                $customer->ip_address = $ip_address;
                
                
                if($customer->save()){
                    $data = $this->sendVerificationCode($customer, $otp);
                    return Helper::sendOkHttpResponse($data);
                    
                } else{
                    $message = "Unable to register customer phone number";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }
            
        }
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
    }
}




public function resendOTP(Request $request){
    
    $validator = Validator::make($request->all(), [
        'countryCode' => 'required',
        'phoneNumber' => 'required'
    ]);
    
    try{
        if($validator->fails()){
            $message = $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
            
        }else{
            
            $country_code  = request('countryCode');
            $phone_number  = request('phoneNumber');
            
            $exists = Customer::where("country_code", "=", $country_code)
            ->where("phone_number", "=", $phone_number)
            ->exists();
            
            if($exists){
                
                $customer = Customer::where("country_code", "=", $country_code)
                ->where("phone_number", "=", $phone_number)->first();
                
                $otp = $this->smsService->generateNumericOTP(4); // $customer->otp
                $data = $this->sendVerificationCode($customer, $otp);
                return Helper::sendOkHttpResponse(['message' => 'SUCCESS', 'data' => $data]);
                
            }else{
                $message = "Unable to find customer with supplied details";
                return Helper::sendFailedHttpResponse($message);
                
            }
        }
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
        
    }
}


public function verifyChangePhoneNumber(Request $request){
    
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'countryCode' => 'required',
        'phoneNumber' => 'required'
    ]);
    
    try{
        if($validator->fails()){
            $message = $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
            
        }else{
            
            $user_id = request('id');
            $country_code  = request('countryCode');
            $phone_number  = request('phoneNumber');
            
            $exists = Customer::where('id', $user_id)->exists();
            
            if($exists){
                
                $newPhoneNumberExists = Customer::where("country_code", "=", $country_code)
                ->where("phone_number", "=", $phone_number)
                ->exists();
                
                $user = Customer::find($user_id);
                $currentPhoneNumber = $user->country_code."".$user->phone_number;
                $newPhoneNumber = $country_code."".$phone_number;
                
                if($newPhoneNumberExists){
                    $message = "Someone already registered with this phone number";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
                else if($currentPhoneNumber == $newPhoneNumber){
                    $message = "Your new phone number is similar to the current phone number";
                    return Helper::sendFailedHttpResponse($message);
                    
                }else{
                    
                    $user->new_country_code = $country_code;
                    $user->new_phone_number = $phone_number;
                    
                    $isUpdated = $user->save();
                    if($isUpdated){
                        
                        $user->country_code = $user->new_country_code;
                        $user->phone_number = $user->new_phone_number;
                        $otp = $this->smsService->generateNumericOTP(4);
                        $data = $this->sendVerificationCode($user, $otp);
                        $data['new_country_code'] = $user->new_country_code;
                        $data['new_phone_number'] = $user->new_phone_number;
                        return Helper::sendOkHttpResponse(['message' => 'SUCCESS', 'data' => $data]);
                        
                        
                    }else{
                        $message = "Unable to update details while verifying change phone number";
                        return Helper::sendFailedHttpResponse($message);
                    } 
                }
                
            }else{
                $message = "Unable to find user with supplied details";
                return Helper::sendFailedHttpResponse($message);
                
            }
        }
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
    }
}


public function changePhoneNumber(Request $request){
    
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'countryCode' => 'required',
        'phoneNumber' => 'required',
        'otp' => 'required'
    ]);
    
    try{
        if($validator->fails()){
            $message = $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
        }else{
            
            $user_id = request('id');
            $country_code = request('countryCode');
            $phone_number  = request('phoneNumber');
            $otp  = request('otp');
            
            $exists = Customer::where('id', $user_id)->exists();
            
            if($exists){
                
                $newPhoneNumberExists = Customer::where("new_country_code", "=", $country_code)
                ->where("new_phone_number", "=", $phone_number)
                ->exists();
                
                $user = Customer::find($user_id);
                $currentPhoneNumber = $user->country_code."".$user->phone_number;
                $newPhoneNumber = $country_code."".$phone_number;
                
                if($newPhoneNumberExists){
                    
                    if($user->otp == $otp){
                        
                        $user->country_code = $user->new_country_code;
                        $user->phone_number = $user->new_phone_number;
                        $user->new_country_code = null; 
                        $user->new_phone_number = null;
                        $user->otp = null;
                        
                        $isUpdated = $user->save();
                        if($isUpdated){
                            
                            $userData = Helper::getCustomerData($user_id);
                            
                            $userData['id'] = $user->id;
                            $userData['country_code'] = $user->country_code;
                            $userData['phone_number'] = $user->phone_number;
                            $userData['is_registered'] = $user->profile_status;
                            $userData['access_token'] = $user->createToken('Customer'.$user->country_code.''.$user->phone_number, ['customer'])->accessToken;
                            
                            $message = "Your phone number has been changed successfully";
                            return Helper::sendOkHttpResponse(['message' => $message, 'data' => $userData]);
                            
                        }else{
                            $message = "System unable to change your phone number";
                            return Helper::sendFailedHttpResponse($message);
                        }
                        
                    }else{
                        $message = "Invalid OTP";
                        return Helper::sendFailedHttpResponse($message);
                    } 
                }else{
                    $message = "Please enter your new phone number first"; 
                    return Helper::sendFailedHttpResponse($message);
                }
                
            }else{
                $message = "Unable to find user with supplied details";
                return Helper::sendFailedHttpResponse($message);
            }
        }
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
    }
}


public function changePin(Request $request){
    
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'currentPin' => 'required',
        'newPin' => 'min:4|required_with:confirmPin|same:confirmPin',
        'confirmPin' => 'required|min:4',
    ]);
    
    try{
        if($validator->fails()){
            $message = $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
        }else{
            
            $user_id  = request('id');
            $current_pin  = request('currentPin');
            $new_pin  = request('newPin');
            $confirm_pin  = request('confirmPin');
            
            $exists = Customer::where("id", "=", $user_id)->exists();
            
            if($exists){
                
                $customer = Customer::find($user_id);
                $currentPinInDB = $customer->pin;
                
                if(Hash::check($current_pin, $currentPinInDB)){
                    
                    if(Hash::check($new_pin, $currentPinInDB)){
                        $message = "Your new pin is same as current pin";
                        return Helper::sendFailedHttpResponse($message); 
                    }else{
                        
                        $newHashedPin = Hash::make($new_pin);
                        $isUpdated = $customer->update(['pin' => $newHashedPin]);
                        if($isUpdated){
                            $message = "Your pin has been changed successfully";
                            return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customer]);
                        }else{
                            $message = "Unable to change your pin"; 
                            return Helper::sendFailedHttpResponse($message);
                        }
                    } 
                }else{
                    $message = "Your current pin is incorrect";
                    return Helper::sendFailedHttpResponse($message);
                }
                
            }else{
                $message = "Unable to find customer with supplied details";
                return Helper::sendFailedHttpResponse($message);
            }
        }
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
    }
}


public function updateAppDetails(Request $request){
    
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'currentVersion' => 'required',
    ]);
    
    try{
        if($validator->fails()){
            $message = $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
        }else{
            
            $user_id = request('id');
            $current_version = request('currentVersion');
            
            $exists = Customer::where("id", "=", $user_id)
            ->exists();
            
            if($exists){
                
                $customer = Customer::find($user_id);
                
                $ip_address = $request->filled('ipAddress') ? request('ipAddress') : $customer->ip_address;
                $fcm_token = $request->filled('deviceToken') ? request('deviceToken') : $customer->fcm_token;
                $unique_device_id = $request->filled('uniqueDeviceId') ? request('uniqueDeviceId') : $customer->unique_device_id;
                
                $isUpdated = $customer->update([
                    'unique_device_id' => $unique_device_id, 
                    'ip_address' => $ip_address,
                    'current_version' => $current_version,
                    'fcm_token' => $fcm_token,
                ]);
                
                if($isUpdated){
                    $message = Globals::$STATUS_DESC_SUCCESS;
                    $data = Helper::getCustomerData($user_id);
                    return Helper::sendOkHttpResponse(['message' => $message, 'data' => $data]);
                }else{
                    $message = Globals::$STATUS_DESC_FAILED;
                    return Helper::sendFailedHttpResponse($message);
                }
                
            }else{
                $message = "Unable to find customer with supplied details";
                return Helper::sendFailedHttpResponse($message);
            }
            
        }
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
    }
}


private function sendVerificationCode($customer, $otp){
    try{
        
        $customer_phone_number = $customer->country_code.''.$customer->phone_number;
        $data = $this->smsService->sendOTP($customer_phone_number, $otp);
        
        $customerData['user_id'] = $customer->id;
        $customerData["otp"] = $otp;
        $customerData['country_code'] = $customer->country_code;
        $customerData['phone_number'] = $customer->phone_number;
        $customerData['access_token'] = $customer->createToken('Customer'.$customer_phone_number, ['customer'])->accessToken;
        
        Customer::where("id", $customer->id)->update(["otp" => $otp]);
        return $customerData;
        
    }catch(\Exception $ex){
        throw $ex;
    }
}


public function verifyOTP(Request $request){
    $validator = Validator::make($request->all(), [
        'country_code' => 'required',
        'phone_number' => 'required',
        'otp' => 'required|min:4',
    ]);
    
    if($validator->fails()){
        $message = $validator->errors()->all();
        return Helper::sendFailedHttpResponse($message);
    }else{
        
        $country_code = request('country_code');
        $phone_number = request('phone_number');
        $otp = request('otp');
        $isRegistered = false;
        
        $exists = Customer::where("country_code", "=", $country_code)
        ->where("phone_number", "=", $phone_number)
        ->where("otp", "=", $otp)->exists();
        
        if($exists){
            $customer = Customer::where("country_code", "=", $country_code)
            ->where("phone_number", "=", $phone_number)
            ->where("otp", "=", $otp)->first();
            
            config(['auth.guards.api.provider' => 'customer']);
            $customer_id = $customer->id;
            $customer = Customer::select('customers.*')->find($customer_id);
            $customerData = Helper::getCustomerData($customer_id);
            
            $customerData['id'] = $customer->id;
            $customerData['country_code'] = $customer->country_code;
            $customerData['phone_number'] = $customer->phone_number;
            $customerData['is_registered'] = $customer->profile_status;
            $customerData['access_token'] = $customer->createToken('Customer'.$customer->country_code.''.$customer->phone_number, ['customer'])->accessToken;
            
            Customer::where("country_code", "=", $country_code)->where("phone_number", "=", $phone_number)->update(["otp" => null]);
            
            $message = 'OTP successfully verified!';
            return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customerData]);
            
        }else{
            $message = 'Invalid OTP Code';
            return Helper::sendFailedHttpResponse($message);
        }
        
    }
}




public function signup(Request $request){
    $validator = Validator::make($request->all(), [
        'phone_number' => 'required',
    ]);
    
    try{
        if($validator->fails()){
            $message = $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
        }else{
            $phone_number = request('phone_number');
            $exists = Customer::where("phone_number", "=", $phone_number)->exists();
            $otp = $this->smsService->generateNumericOTP(4);
            if($exists){
                $customer = Customer::where("phone_number", "=", $phone_number)->first();
                if($customer->first_name){
                    $data = $this->sendVerificationCode($customer, $otp);
                    return Helper::sendOkHttpResponse($data);
                    
                }else{
                    $message = "Customer with phone number ".$phone_number." is already registered.";
                    return Helper::sendFailedHttpResponse($message);
                }
            }else{
                $customer = new Customer();
                $customer->phone_number = $phone_number;
                $customer->otp = $otp;
                if($customer->save()){
                    $data = $this->sendVerificationCode($customer, $otp);
                    return Helper::sendOkHttpResponse($data);
                    
                } else{
                    $message = "Unable to register customer phone number";
                    return Helper::sendFailedHttpResponse($message);
                }
            }
            
        }
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
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
                    $data = Auth::user();
                    $customer_name = Auth::user()->first_name." ".Auth::user()->last_name;
                    Helper::logActivity($request, ['name' => $customer_name, 'role' => 'customer', 'action' => $action]);
                    return Helper::sendOkHttpResponse($data);
                }
            }else
            {
                $message = 'Invalid login credentials';
                return Helper::sendFailedHttpResponse($message);
            }
        }else{
            $message = "Unable to process request: missing parameters";
            return Helper::sendFailedHttpResponse($message);
        }
    } catch (\Exception $ex) {
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
    }
}


public function register(Request $request){
    
    try{
        $validatedData = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required',
            'password' => 'min:8|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:8',
        ]);
        
        if($validatedData->fails()){
            $message =  $validatedData->errors()->all();
            return Helper::sendFailedHttpResponse($message);
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
                    $customer->access_token = $customer->createToken('Customer'.$customer->phone_number, ['customer'])->accessToken;
                    return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customer]);
                }
                else
                {
                    $message = "Customer registration failed!";
                    return Helper::sendFailedHttpResponse($message);
                }
            } else{
                $message = "Customer with phone number ".$phone_number." has been already registered";
                $responseInfo = Helper::getMessage('error', $message);
                return Helper::sendFailedHttpResponse($responseInfo);
            }
        }
    }catch(\Exception $ex){
        return Helper::sendFailedHttpResponse($ex->getMessage());
    }
    
}


public function createProfile(Request $request){
    
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'first_name' => 'required',
        'last_name' => 'required',
        'country_code' => 'required',
        'phone_number' => 'required'
    ]);
    
    
    try{
        
        if($validator->fails())
        {
            $message =  $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
            
        }
        else{
            
            $customerId = $request->input('id');
            $first_name = trim($request->input('first_name'));
            $last_name = trim($request->input('last_name'));
            $country_code = trim($request->input('country_code'));
            $phone_number = trim($request->input('phone_number'));
            $role = 'Customer';
            
            if($request->has('email') && $request->filled('email')){
                $email = $request->input('email');
            }else{
                $email = null;
            }
            
            $exists = Customer::where("id", "=", $customerId)
            ->where("country_code", "=", $country_code)
            ->where("phone_number", "=", $phone_number)
            ->exists();
            
            if($exists){
                
                $customer = Customer::find($customerId);
                
                $pin = $this->generateRandomPin();
                $hashedPin =  Hash::make($pin);
                
                $hasUpdated = $customer->update([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'pin' => $hashedPin,
                    'is_active' => true,
                    'profile_status' => true,
                ]);
                
                if($hasUpdated){
                    
                    $pinNotificationMessage = "Hey ".$customer->first_name.", your default ".config('app.company_name')." pin is ".$pin.". You can change it anytime you want.";
                    $this->smsService->sendMessage($customer->country_code.''.$customer->phone_number, $pinNotificationMessage);
                    
                    config(['auth.guards.api.provider' => 'customer']);
                    $action = "created your profile";
                    $message = Helper::getMessage('success', $action);
                    $customerData = Helper::getCustomerData($customerId);
                    $customerData['access_token'] = $customer->createToken('Customer'.$customer->country_code.''.$customer->phone_number, ['customer'])->accessToken;
                    return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customerData]);
                    
                }else{
                    $message ="Unable to update customer account profile!";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }else{
                $message ="Unable to find customer with supplied details.";
                return Helper::sendFailedHttpResponse($message);
                
            }
            
        }
        
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
    }
    
    
}


public function updateProfile(Request $request){
    
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'first_name' => 'required',
        'last_name' => 'required',
        'country_code' => 'required',
        'phone_number' => 'required',
    ]);
    
    
    try{
        
        if($validator->fails())
        {
            $message =  $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
            
        }
        else{
            
            $customerId = $request->input('id');
            $first_name = trim($request->input('first_name'));
            $last_name = trim($request->input('last_name'));
            $country_code = trim($request->input('country_code'));
            $phone_number = trim($request->input('phone_number'));
            $role = 'Customer';
            
            if($request->has('email') && $request->filled('email')){
                $email = $request->input('email');
            }else{
                $email = null;
            }
            
            $exists = Customer::where("id", "=", $customerId)
            ->where("country_code", "=", $country_code)
            ->where("phone_number", "=", $phone_number)
            ->exists();
            
            if($exists){
                
                $customer = Customer::find($customerId);
                $hasUpdated = Customer::where('id', '=', $customerId)
                ->update([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'is_active' => true,
                ]);
                
                if($hasUpdated){
                    $customer = Customer::find($customerId);
                    $action = "updated your profile";
                    $message = Helper::getMessage('success', $action);
                    $customerData = Helper::getCustomerData($customerId);
                    return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customerData]);
                }else{
                    $message ="Unable to update customer account profile!";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }else{
                $message ="Unable to find customer with supplied details.";
                return Helper::sendFailedHttpResponse($message);
                
            }
            
        }
        
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
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
                    $message = Helper::getMessage('success', $action);
                    $customerData = Helper::getCustomerData($customerId);
                    return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customerData]);
                }else{
                    $message ="Unable to update customer account profile!";
                    return Helper::sendFailedHttpResponse($message);
                    
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
                        
                        $cust = Customer::where('phone_number', '=', $phone_number)->first();
                        $customerData = Helper::getCustomerData($cust->id);
                        return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customerData]);
                    }
                    else
                    {
                        $message = "Customer registration failed!";
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                } else{
                    $message = "Customer with phone number ".$phone_number." has been already registered";
                    $responseInfo = Helper::getMessage('error', $message);
                    return Helper::sendFailedHttpResponse($responseInfo);
                    
                    
                }
                
            }
        } else{
            $message = "Unable to process request: missing parameters";
            return Helper::sendFailedHttpResponse($message);
            
        }
        
    } catch (\Exception $ex) {
        $message = $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
        
    }
    
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
                return Helper::sendOkHttpResponse($customerData);
            } else {
                $message = 'Unable to find customer details';
                return Helper::sendFailedHttpResponse($message);
                
            }
        } else {
            $message = "Unable to process request";
            return Helper::sendFailedHttpResponse($message);
            
        }
    }
    
}


public function changePassword(Request $request)
{
    
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
                            return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customerData]);
                            
                        }else{
                            $message = "Unable to change your password";
                            return Helper::sendFailedHttpResponse($message);
                            
                        }  
                    }else{
                        $message = "Incorrect old password";
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                    
                }else{
                    $message = "Enter new matching passwords";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
                
            }else{
                $message = "Customer with supplied details does not exist";
                return Helper::sendFailedHttpResponse($message);
                
            }
        }
        else{
            $message = "Unable to process request";
            return Helper::sendFailedHttpResponse($message);
            
        }
    } catch (\Exception $ex) {
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
        
    }
}


public function uploadProfilePicture(Request $request){
    
    try {
        if($request->filled('id') && $request->filled('country_code') && $request->filled('phone_number') &&
        $request->filled('extension') && $request->has('image')){
            
            $file = $request->file('image');
            $customer_id = $request->input('id');
            $country_code = $request->input('country_code');
            $phone_number = $request->input('phone_number');
            $file_extension = $request->input('extension');
            
            $doesCustomerExist = Customer::where('id', $customer_id)
            ->where('country_code', $country_code)
            ->where('phone_number', $phone_number)
            ->exists();
            
            if($doesCustomerExist){
                $customer = Customer::find($customer_id);
                if(!empty($customer->image)){
                    Storage::disk('public')->delete($customer->image);
                }
                $fileName = $customer_id.''.time().'.'.$file_extension;
                $filePath = $file->storeAs('images', $fileName, 'public');
                
                $input = ['image' => $filePath];
                $hasUpdated = Customer::where('id', $customer_id)
                ->where('country_code', $country_code)
                ->where('phone_number', $phone_number)
                ->update($input);
                
                if($hasUpdated){
                    $action = "updated your profile picture";
                    $message = Helper::getMessage('success', $action);
                    $customerData = Helper::getCustomerData($customer_id);
                    return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customerData]);
                }else{
                    $message = "Unable to update your profile picture";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }else{
                $message = "Unable to find customer with supplied details";
                return Helper::sendFailedHttpResponse($message);
                
            }
        }
        else{
            $message = "Unable to process request";
            return Helper::sendFailedHttpResponse($message);
            
        }
        
    } catch (\Exception $ex) {
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
        
    }
}


public function removeProfilePicture(Request $request){
    
    $validator = Validator::make($request->all(), [
        'id' => 'required',
        'country_code' => 'required',
        'phone_number' => 'required'
    ]);
    
    try{
        
        if($validator->fails()){
            $message =  $validator->errors()->all();
            return Helper::sendFailedHttpResponse($message);
        }
        else{
            
            $customerId = $request->input('id');
            $country_code = $request->input('country_code');
            $phone_number = trim($request->input('phone_number'));
            
            $exists = Customer::where("id", "=", $customerId)
            ->where("country_code", "=", $country_code)
            ->where("phone_number", "=", $phone_number)
            ->exists();
            
            if($exists){
                $customer = Customer::find($customerId);
                $hasUpdated = Customer::where('id', '=', $customerId)
                ->update([
                    'image' => null,
                ]);
                if($hasUpdated){
                    $customer = Customer::find($customerId);
                    $action = "removed your profile picture";
                    $message = Helper::getMessage('success', $action);
                    $customerData = Helper::getCustomerData($customerId);
                    return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customerData]);
                }else{
                    $message ="Unable to remove profile picture";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }else{
                $message ="Unable to find customer with supplied details.";
                return Helper::sendFailedHttpResponse($message);
                
            }
            
        }
        
    }catch(\Exception $ex){
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
        
    }
    
}

private function generateRandomPin(){
    try{
        $randomNumber = random_int(1000, 9999);
        return $randomNumber;
    }catch(\Exception $ex){
        throw $ex;
    }
}


}
