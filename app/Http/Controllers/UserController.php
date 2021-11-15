<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Helpers\ApiResponse;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use App\Mail\RegistrationMailSender;
use Illuminate\Support\Facades\Auth;
use Validator;
use Helper;
use Globals;
use TokenAuth;
use Mail;

class UserController extends Controller
{
    
    public function authenticate(Request $request)
    {
        $resp = new ApiResponse();
        try{
            
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                
                ($request->has('remember'))
                ? $remembered = true
                : $remembered = false;
                
                $login = $request->input('username');
                $password = $request->input('password');
                
                filter_var($login, FILTER_VALIDATE_EMAIL)
                ? $fieldType = 'email' 
                : $fieldType = 'username';
                
                $user_id = $this->getUserId($login, $password);
                
                if(Auth::attempt([$fieldType => $login,
                'password' =>  $password
            ], $remembered)){
                
                $userId = $this->getUserId($login);
                $status = $this->findAccountStatus($userId);
                if($status == 0){
                    $statusCode = Globals::$STATUS_CODE_FAILED;
                    $message = 'Your account is inactivated, see admin';
                }
                if($status == 1){
                    $statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $action = $message =  "logged into the system";
                    $resp->data = Auth::user();
                    Helper::logActivity($request, ['name' => $login, 'role' => 'admin', 'action' => $action]);
                }
            }else
            {
                $statusCode = Globals::$STATUS_CODE_FAILED;
                $message = 'Invalid login credentials';
            }
        }else{
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = "Unauthorized access";
            $resp->data = "Unauthorized access";
            
        }
    } catch (\Exception $ex) {
        $statusCode = Globals::$STATUS_CODE_ERROR;
        $message = $ex->getMessage();
    }
    $resp->statusCode = $statusCode;
    $resp->message = $message;
    
    return response()->json($resp);
}

public function getUserId($login)
{
    filter_var($login, FILTER_VALIDATE_EMAIL)
    ? $fieldType = 'email' 
    : $fieldType = 'username';
    
    $userId = User::where($fieldType, $login)
    ->value('email');
    
    return $userId;
}

private function findAccountStatus($id){
    
    $accountStatus = User::where('email', $id)->value('is_active');
    return $accountStatus;
    
}


/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(Request $request)
{
    $resp = new ApiResponse();
    try {
        $authToken   =   $request->header('AuthToken');
        if (!empty($authToken) && TokenAuth::validate($authToken)) {
            $users = User::orderBy('id', 'desc')->get();
            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message  = Globals::$STATUS_DESC_SUCCESS;
            $resp->data = $users;
            
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

/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
    //
}

public function createOrReturnPath($dir){
    try{
        if(!file_exists($dir)){
            mkdir($dir, 0777, true);
        }
        return $dir;
    }catch (\Exception $ex) {
        throw $ex;
    }
}

protected function getUsernamesArr()
{
    $usernames = User::pluck('username');
    $dataArr = array();
    foreach($usernames as $username)
    {
        $dataArr[] = $username;
    }
    return $dataArr;
}

public function is_inArr($dataArr, $item)
{
    if(count($dataArr) > 0){
        (in_array($item, $dataArr))
        ? $bool = true
        : $bool = false;
    }
    
    return $bool;
}

public function validateUsername($old_username, $new_username){
    
    $arr =  $this->getUsernamesArr();
    if(in_array($old_username, $arr))
    {
        for($i=0; $i<count($arr); $i++){
            if($arr[$i] == $old_username){
                $index = $i;
                break;
            }
            else{
                $index = -1;
            }
        }
        $newArr = Arr::except($arr, $index);
    }
    else {
        $newArr = $arr;
    }
    $bool = $this->is_inArr($newArr, $new_username);
    return $bool;
}

public function saveFile($file, $userRole){
    try{
        $dir = "uploads/images";
        $path = $dir."/".ucfirst($userRole)."";
        $file_path = $this->createOrReturnPath($path);
        $extension = $file->getClientOriginalExtension();
        $filename = time().'.'.$extension;
        $file->move($file_path, $filename);
        return array("file_path" => $file_path, "filename" => $filename);
    }catch(Exception $ex){
        throw $ex;
    }
}

public function getImageStoragePath(){
    $path = public_path();
    return response()->json($path);
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
    $method = "UserController@store";
    
    try{
        
        $authToken   =   $request->header('AuthToken');
        if (!empty($authToken) && TokenAuth::validate($authToken)) {
            if($request->filled(['user_id', 'first_name','last_name', 'address',
            'email', 'mobile_no','nin', 'gender','user_role'])){
                $fname = trim($request->input('first_name'));
                $lname = trim($request->input('last_name'));
                $address = trim($request->input('address'));
                $email = trim($request->input('email'));
                $telno = trim($request->input('mobile_no'));
                $nin = trim($request->input('nin'));
                $gender = trim($request->input('gender'));
                $role = $request->input('user_role');
                $registra = User::where('id', $request->input('user_id'))->value('name');
                $registra_id = User::where('id', $request->input('user_id'))->value('role');
                $name = $fname." ".$lname;
                $defaultPwd = '12345678';
                
                if($request->filled('id')) {
                    
                    $userId = $request->input('id');
                    $user = User::find($userId);
                    $username = $request->input('username');
                    $changed_by = $user->name;
                    $bool_userExists = $this->validateUsername($user->username, $username); // User::where('username' ,$username)->exists();
                    
                    if(!$bool_userExists){
                        
                        if($request->hasFile('photo')){
                            $photo = $request->file('photo');
                            $k = $this->saveFile($photo, Helper::getUserRole($registra_id));
                            $photo_path = $k['file_path'];
                            $photo_name = $k['filename'];
                        }else{
                            $photo_path = null;
                            $photo_name = null;
                        }
                        
                        $hasUpdated = User::where('id', '=', $userId)
                        ->update([
                            'first_name' => $fname,
                            'last_name' => $lname,
                            'name' => $name,
                            'username' => $username,
                            'gender' => $gender,
                            'email' => $email,
                            'mobile_no' => $telno,
                            'address' => $address,
                            'national_id_no' => $nin,
                            'photo_path' => $photo_path,
                            'photo_name' => $photo_name,
                            'changed_by' => $changed_by,
                        ]);
                        
                        if($hasUpdated){
                            $action = "updated profile";
                            $resp->message = Helper::getMessage('success', $action);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->data = User::count();
                        }else{
                            $resp->message ="Unable to update user account details!";
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        }
                    }
                    else{
                        $resp->message = "username ".$request->input('username')." has already been taken, choose another one";
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED; 
                    }
                    
                }else {
                    
                    $user = new User();
                    $name = $fname." ".$lname;
                    $username = strtolower(Str::random(6).".".$fname);
                    $bool_userExists = User::where('username' ,$username)->exists();
                    if(!$bool_userExists){
                        
                        $password = Hash::make($defaultPwd, ['rounds' => 12]);
                        $count = User::where('email', '=', $email)->count();
                        if($count == 0){
                            if($request->hasFile('photo')){
                                $photo = $request->file('photo');
                                $k = $this->saveFile($photo, Helper::getUserRole($registra_id));
                                $photo_path = $k['file_path'];
                                $photo_name = $k['filename'];
                            }else{
                                $photo_path = null;
                                $photo_name = null;
                            }
                            
                            $user->first_name = $fname;
                            $user->last_name = $lname;
                            $user->name = $name;
                            $user->username = $username;
                            $user->gender = $gender;
                            $user->email = $email;
                            $user->role = $role;
                            $user->mobile_no = $telno;
                            $user->address = $address;
                            $user->national_id_no = $nin;
                            $user->photo_path = $photo_path;
                            $user->photo_name = $photo_name;
                            $user->password = $password;
                            $user->is_active = 1;
                            $user->changed_by = $registra;
                            
                            $save_status = $user->save();
                            if($save_status){
                                
                                $subject = 'User Registration';
                                $registraPosition = 'User';
                                $registraEmail = 'parksmartug@gmail.com'; //$request->user()->email;
                                $default_password = $defaultPwd;
                                $now = now();
                                $registeredRole = Helper::getUserRole($role);
                                $action =  "registered user ".$name." as ".$registeredRole."";
                                $sendAction = "You have been registered as ".$registeredRole."  at parksmart today at ".$now."";
                                Helper::logActivity($request, ['name' => $registra, 'role' => Helper::getUserRole($registra_id), 'action' => $action]);
                                $data = array(
                                    'name' => $name,
                                    'username' => $username,
                                    'password' => $default_password,
                                    'user_position' => 'user',
                                    'registra' => $registra,
                                    'registraPosition' => $registraPosition,
                                    'registraEmail' => $registraEmail,
                                    'email' => $email,
                                    'subject' => $subject,
                                    'created_at' => $now,
                                    'details' => $sendAction,
                                    'activity' => 'registration',
                                );
                                
                                if(Helper::is_connectedToInternet() == 1){
                                    \Mail::to($email)->send(new RegistrationMailSender($data));
                                    $message = $action." and email has been sent";
                                    
                                }else{
                                    $message = $action;
                                }
                                
                                $resp->message = Helper::getMessage('success', $message);
                                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                $resp->data = User::count();
                            }
                            else
                            {
                                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                $resp->message = "User registration failed!";
                            }
                        } else{
                            $message = "User with email ".$email." has been already registered";
                            $responseInfo = Helper::getMessage('error', $message);
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message  = $responseInfo;
                            
                        }
                        
                        
                    }else{
                        $resp->message = "username ".$username." has already been taken, choose another one";
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
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


public function mail(Request $request) {
    try{
        if($request->filled(['email', 'subject', 'message', 'name'])){
            
            $senderName = $request->input('name');
            $senderEmail = $request->input('email');
            $subject = $request->input('subject');
            $messageBody = $request->input('message');
            $receiverEmail = 'asingwire50dallington@gmail.com';
            
            $data = [
                'senderName' => $senderName,
                'senderEmail' => $senderEmail,
                'receiverEmail' => $receiverEmail,
                'subject' => $subject,
                'message' => $messageBody
            ];
            
            $sendmail = Mail::to($data['receiverEmail'])->send(new SendMail($data));
            if (empty($sendmail)) {
                $this->apiResponse['code'] = 1;
                $this->apiResponse['message'] = 'Email sent successfully';
                $this->apiResponse['data'] =  $data; 
                return response()->json($this->apiResponse, 200);
            }else{
                
                $this->apiResponse['code'] = 0;
                $this->apiResponse['message'] = "Unable to send email from ".$senderName."";
                return response()->json(['message' => 'Mail Sent fail'], 400); 
            }
            
        }else{
            $this->apiResponse['success'] = 0;
            $this->apiResponse['message'] = "Unable to get sender's email";
        }
        
    }catch(\Exception $ex){
        dd("Got exception ".$ex->getMessage());
    }
}






public function stores(Request $request)
{
    $resp = new ApiResponse();
    $method = "UserController@store";
    
    try{
        
        $authToken   =   $request->header('AuthToken');
        if (!empty($authToken) && TokenAuth::validate($authToken)) {
            
            $formData = $request->input('form-params');
            $formData = json_decode($formData); // json object to array
            
            // return response()->json($formData->first_name);
            
            if(
                $formData->user_id &&
                $formData->first_name &&
                $formData->last_name &&
                $formData->address &&
                $formData->email &&
                $formData->mobile_no &&
                $formData->nin &&
                $formData->gender &&
                $formData->user_role)
                {
                    
                    
                    $fname = trim($formData->first_name);
                    $lname = trim($formData->last_name);
                    $address = trim($formData->address);
                    $email = trim($formData->email);
                    $telno = trim($formData->mobile_no);
                    $nin = trim($formData->nin);
                    $gender = trim($formData->gender);
                    $role = $formData->user_role;
                    $registra = User::where('id', $formData->user_id)->value('name');
                    $registra_id = User::where('id', $formData->user_id)->value('role');
                    $name = $fname." ".$lname;
                    $defaultPwd = '12345678';
                    
                    if($formData->id) {
                        $userId = $formData->id;
                        $user = User::find($userId);
                        $username = $formData->username;
                        $changed_by = $user->name;
                        $bool_userExists = $this->validateUsername($user->username, $username); // User::where('username' ,$username)->exists();
                        
                        if(!$bool_userExists){
                            
                            if($request->hasFile('photo')){
                                $photo = $request->file('photo');
                                $k = $this->saveFile($photo, Helper::getUserRole($role));
                                $photo_path = $k['file_path'];
                                $photo_name = $k['filename'];
                            }else{
                                $photo_path = null;
                                $photo_name = null;
                            }
                            
                            $hasUpdated = User::where('id', '=', $userId)
                            ->update([
                                'first_name' => $fname,
                                'last_name' => $lname,
                                'name' => $name,
                                'username' => $username,
                                'gender' => $gender,
                                'email' => $email,
                                'role' => $role,
                                'mobile_no' => $telno,
                                'national_id_no' => $nin,
                                'photo_path' => $photo_path,
                                'photo_name' => $photo_name,
                                'changed_by' => $changed_by,
                            ]);
                            
                            if($hasUpdated){
                                $statusCode = Globals::$STATUS_CODE_SUCCESS;
                                $action = "updated your profile";
                                $dataArr = array("code" => $statusCode,
                                "message" => Str::replaceFirst('your', '', $action),
                                "method" => $method);
                                Helper::LogRequest($request, $dataArr);
                                $resp->message = Helper::getMessage('success', $action);
                                $resp->statusCode = $statusCode;
                                $resp->data = User::count();
                            }else{
                                $message = "Unable to update user account details!";
                                $statusCode = Globals::$STATUS_CODE_FAILED;
                                $dataArr = array("code" => $statusCode,
                                "message" => $message,
                                "method" => $method);
                                Helper::LogRequest($request, $dataArr);
                                $resp->statusCode = $statusCode;
                                $resp->message = $message;
                            }
                            
                        }
                        
                        else{
                            $statusCode = Globals::$STATUS_CODE_FAILED;
                            $message =  "username ".$formData->username." has already been taken, choose another one";
                            $dataArr = array("code" => $statusCode,
                            "message" => $message,
                            "method" =>  $method);
                            $resp->message = $message;
                            $resp->statusCode = $statusCode; 
                        }
                        
                    }else {
                        $user = new User();
                        $name = $fname." ".$lname;
                        $username = strtolower(Str::random(6).".".$fname);
                        $bool_userExists = User::where('username' ,$username)->exists();
                        if(!$bool_userExists){
                            
                            $password = Hash::make($defaultPwd, ['rounds' => 12]);
                            $count = User::where('email', '=', $email)->count();
                            if($count == 0){
                                if($request->hasFile('photo')){
                                    $photo = $request->file('photo');
                                    $k = $this->saveFile($photo, Helper::getUserRole($role));
                                    $photo_path = $k['file_path'];
                                    $photo_name = $k['filename'];
                                }else{
                                    $photo_path = null;
                                    $photo_name = null;
                                }
                                
                                $user->first_name = $fname;
                                $user->last_name = $lname;
                                $user->name = $name;
                                $user->username = $username;
                                $user->gender = $gender;
                                $user->email = $email;
                                $user->role = $role;
                                $user->mobile_no = $telno;
                                $user->address = $address;
                                $user->national_id_no = $nin;
                                $user->photo_path = $photo_path;
                                $user->photo_name = $photo_name;
                                $user->password = $password;
                                $user->is_active = 1;
                                $user->changed_by = $registra;
                                
                                $save_status = $user->save();
                                if($save_status){
                                    
                                    $subject = 'User Registration';
                                    $registraPosition = 'Client';
                                    $registraEmail = 'codesolutionug@gmail.com'; //$request->user()->email;
                                    $default_password = $defaultPwd;
                                    $now = now();
                                    $action =  "registered user ".$name." as ".Helper::getUserRole($role)."";
                                    $sendAction = "You have been registered as a client at parksmart today at ".$now."";
                                    Helper::logActivity($request, ['name' => $registra, 'role' => Helper::getUserRole($registra_id), 'action' => $action]);
                                    $data = array(
                                        'name' => $name,
                                        'username' => $username,
                                        'password' => $default_password,
                                        'user_position' => 'user',
                                        'registra' => $registra,
                                        'registraPosition' => $registraPosition,
                                        'registraEmail' => $registraEmail,
                                        'email' => $email,
                                        'subject' => $subject,
                                        'created_at' => $now,
                                        'details' => $sendAction,
                                        'activity' => 'registration',
                                    );
                                    
                                    if(Helper::is_connectedToInternet() == 1){
                                        \Mail::to($email)->send(new RegistrationMailSender($data));
                                        $message = $action." and email has been sent";
                                        
                                    }else{
                                        $message = $action;
                                    }
                                    
                                    $dataArr = array("code" => Globals::$STATUS_CODE_SUCCESS,
                                    "message" => $message,
                                    "method" => $method);
                                    Helper::LogRequest($request, $dataArr);
                                    $resp->message = Helper::getMessage('success', $message);
                                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                    $resp->data = User::count();
                                }
                                else
                                {
                                    $message = "User registration failed!";
                                    Helper::LogRequest($request, $dataArr);
                                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                    $resp->message = $message;
                                }
                            } else{
                                $message = "User with email ".$email." has been already registered";
                                $dataArr = array("code" => Globals::$STATUS_CODE_FAILED,
                                "message" => $message,
                                "method" => $method);
                                $responseInfo = Helper::getMessage('error', $message);
                                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                $resp->message  = $responseInfo;
                                
                            }
                            
                            
                        }else{
                            $message =  "username ".$username." has already been taken, choose another one";
                            $dataArr = array("code" => Globals::$STATUS_CODE_FAILED,
                            "message" => $message,
                            "method" =>  $method);
                            $resp->message = $message;
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
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
    public function destroy(Request $request)
    {
        
        $resp = new ApiResponse();
        
        try {
            
            $id = $request->input('id');
            $user = User::find($id);
            $usernames = $user->name;
            
            if ($user->delete()) {
                $action = "removed user ".$usernames." from the system";
                Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                $responseInfo = Helper::getMessage('success', $action);
                
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message = $responseInfo ;
            } else {
                $messageErr = "User not removed!";
                $responseInfo = Helper::getMessage('error', $messageErr);
                
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        
        $resp->data = User::count();
        return response()->json($resp);
        
    }
    
    
    public function changePassword(Request $request)
    {
        $resp = new ApiResponse();
        try {
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                if( ($request->has('user_id') && $request->filled('user_id')) &&
                ($request->has('current_password') && $request->filled('current_password')) &&
                ($request->has('new_password') && $request->filled('new_password')) &&
                ($request->has('confirm_password') && $request->filled('confirm_password'))){
                    
                    $user_id = $request->input('user_id');
                    $current_password = $request->input('current_password');
                    $new_password = $request->input('new_password');
                    $confirm_password = $request->input('confirm_password');
                    
                    $user = User::find($user_id);
                    $old_password = $user->password;
                    if($new_password == $confirm_password){
                        if(Hash::check($current_password, $old_password)){
                            $user->password = Hash::make($new_password);
                            if($user->save()){
                                $action = "changed your password";
                                Helper::logActivity($request, ['name' => $user->name, 'role' => Helper::getUserRole($user->role), 'action' => $action]);
                                $message = Helper::getMessage('success', $action);
                                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                $resp->message  = $message;
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
