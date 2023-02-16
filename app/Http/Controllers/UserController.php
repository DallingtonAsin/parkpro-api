<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use App\Mail\RegistrationMailSender;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Repositories\User\UserRepository;
use App\Helpers\formattedApiResponse;
use Validator;
use App\Helpers\SharedCommon as Helper;
use App\Helpers\Globals as Globals;
use Mail;

class UserController extends Controller
{
    
    public function __construct(){
        
    }
    
    
    public function authenticate(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
                
            } else {
                
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
                
                $is_deleted = $this->isAccountDeleted($userId);
                $status = $this->findAccountStatus($userId);
                
                if($is_deleted == 0){
                    if($status == 0){
                        $message= 'Your account is inactivated, see admin';
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                    if($status == 1){
                        $action = $message =  "logged into the system";
                        $user = Auth::user();
                        $user['access_token'] = $user->createToken('User->'.$user->username, ['user'])->accessToken;
                        Helper::logActivity($request, ['name' => $login, 'role' => 'admin', 'action' => $action]);
                        return Helper::sendOkHttpResponse(['message' => 'SUCCESS', 'data' => $user]);
                    }
                }else{
                    $message= 'Your account was removed, see admin';
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }else
            {
                
                $message = 'Invalid login credentials';
                return Helper::sendFailedHttpResponse($message);
                
            }
        }
        
    } catch (\Exception $ex) {
        $message = $ex->getMessage();
        return Helper::sendFailedHttpResponse($message);
        
    }
    
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

private function isAccountDeleted($id){ 
    $is_deleted = User::where('email', $id)->value('is_deleted');
    return $is_deleted; 
}


/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index(UserRepository $userrepo)
{
    try{
        $users = $userrepo->getUsers();
        return formattedApiResponse::getJson($users);
    }catch(\Exception $ex){
        return Helper::sendFailedHttpResponse($ex->getMessage());
    }
    
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
    ;
    $method = "UserController@store";
    
    try{
        
        if($request->filled(['user_id', 'first_name','last_name', 'address',
        'email', 'mobile_no', 'gender','user_role'])){
            $fname = trim($request->input('first_name'));
            $lname = trim($request->input('last_name'));
            $address = trim($request->input('address'));
            $email = trim($request->input('email'));
            $telno = trim($request->input('mobile_no'));
            $gender = trim($request->input('gender'));
            $role = $request->input('user_role');
            $reg = User::find($request->input('user_id'));
            $registra = $reg->first_name." ".$reg->last_name;
            $registra_id = User::where('id', $request->input('user_id'))->value('role');
            $name = $fname." ".$lname;
            $defaultPwd = '12345678';
            
            if($request->filled('id')) {
                
                $userId = $request->input('id');
                $user = User::find($userId);
                $username = $request->input('username');
                $bool_userExists = $this->validateUsername($user->username, $username); // User::where('username' ,$username)->exists();
                
                if(!$bool_userExists){
                    
                    if($request->hasFile('photo')){
                        
                        $file = $request->file('photo');
                        $file_extension = $file->extension();
                        if(!empty($user->image)){
                            Storage::disk('public')->delete($user->image);
                        }
                        $fileName = $userId.''.time().'.'.$file_extension;
                        $filePath = $file->storeAs('images/users', $fileName, 'public');
                        $image = $filePath;
                    }else{
                        $image  = $user->image;
                    }
                    
                    $hasUpdated = User::where('id', '=', $userId)
                    ->update([
                        'first_name' => $fname,
                        'last_name' => $lname,
                        'username' => $username,
                        'gender' => $gender,
                        'email' => $email,
                        'phone_number' => $telno,
                        'address' => $address,
                        'image' => $image,
                    ]);
                    
                    if($hasUpdated){
                        $action = "updated profile";
                        $message = Helper::getMessage('success', $action);
                        $data = User::count();
                        return Helper::sendOkHttpResponse(['message' => $message, 'data' => $data]);
                    }else{
                        $message ="Unable to update user account details!";
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                }
                else{
                    $message = "username ".$request->input('username')." has already been taken, choose another one";
                    return Helper::sendFailedHttpResponse($message);
                    
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
                            $file = $request->file('photo');
                            $file_name = $file->getClientOriginalName();
                            $file_extension = $file->extension();
                            $fileName = $file_name.''.time().'.'.$file_extension;
                            $filePath = $file->storeAs('images/users', $fileName, 'public');
                            $image = $filePath;
                        }else{
                            $image = null;
                        }
                        
                        $user->first_name = $fname;
                        $user->last_name = $lname;
                        $user->username = $username;
                        $user->gender = $gender;
                        $user->email = $email;
                        $user->role = $role;
                        $user->phone_number = $telno;
                        $user->address = $address;
                        $user->image = $image;
                        $user->password = $password;
                        $user->is_active = 1;
                        
                        $save_status = $user->save();
                        if($save_status){
                            $name = $fname." ".$lname;
                            $subject = 'User Registration';
                            $registraPosition = 'User';
                            $registraEmail = 'info@parkproug.com'; //$request->user()->email;
                            $default_password = $defaultPwd;
                            $now = now();
                            $registeredRole = Helper::getUserRole($role);
                            $action =  "registered user ".$name." as ".$registeredRole."";
                            
                            $company = Company::whereNotNull('name')->first();
                            if(isset($company->name)){
                                $company_name = $company->name;
                            }else{
                                $company_name = env('APP_NAME');
                            }
                            
                            $sendAction = "You have been registered as ".$registeredRole."  at ".$company_name." today at ".$now."";
                            Helper::logActivity($request, ['name' => $registra, 'role' => Helper::getUserRole($registra_id), 'action' => $action]);
                            $data = array(
                                'name' => $name,
                                'first_name' => $fname,
                                'last_name' => $lname,
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
                                'company' => $company_name,
                            );
                            
                            if(Helper::is_connectedToInternet() == 1){
                                \Mail::to($email)->send(new RegistrationMailSender($data));
                                $message = $action." and email has been sent";
                                
                            }else{
                                $message = $action;
                            }
                            
                            $message = Helper::getMessage('success', $message);
                            $data = User::count();
                            return Helper::sendOkHttpResponse(['message' => $message, 'data' => $data]);
                        }
                        else
                        {
                            $message = "User registration failed!";
                            return Helper::sendFailedHttpResponse($message);
                            
                        }
                    } else{
                        $message = "User with email ".$email." has been already registered";
                        $responseInfo = Helper::getMessage('error', $message);
                        return Helper::sendFailedHttpResponse($responseInfo);
                        
                        
                    }
                    
                    
                }else{
                    $message = "username ".$username." has already been taken, choose another one";
                    return Helper::sendFailedHttpResponse($message);
                    
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
    ;
    $method = "UserController@store";
    
    try{
        
        $formData = $request->input('form-params');
        $formData = json_decode($formData); // json object to array
        
        
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
                        ]);
                        
                        if($hasUpdated){
                            $statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $action = "updated your profile";
                            $dataArr = array("code" => $statusCode,
                            "message" => Str::replaceFirst('your', '', $action),
                            "method" => $method);
                            Helper::LogRequest($request, $dataArr);
                            $message = Helper::getMessage('success', $action);
                            $data = User::count();
                            return Helper::sendOkHttpResponse(['message'=>$message, 'data' => $data]);
                        }else{
                            $message = "Unable to update user account details!";
                            $statusCode = Globals::$STATUS_CODE_FAILED;
                            $dataArr = array("code" => $statusCode,
                            "message" => $message,
                            "method" => $method);
                            Helper::LogRequest($request, $dataArr);
                            return Helper::sendFailedHttpResponse($message);
                            
                        }
                        
                    }
                    
                    else{
                        $statusCode = Globals::$STATUS_CODE_FAILED;
                        $message =  "username ".$formData->username." has already been taken, choose another one";
                        $dataArr = array("code" => $statusCode,
                        "message" => $message,
                        "method" =>  $method);
                        return Helper::sendFailedHttpResponse($message);
                        
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
                                $message = Helper::getMessage('success', $message);
                                $data = User::count();
                                return Helper::sendOkHttpResponse(['message' => $message, 'data' => $data]);
                            }
                            else
                            {
                                $message = "User registration failed!";
                                $dataArr = array("code" => Globals::$STATUS_CODE_FAILED,
                                "message" => $message,
                                "method" => $method);

                                Helper::LogRequest($request, $dataArr);
                                return Helper::sendFailedHttpResponse($message);
                                
                            }
                        } else{
                            $message = "User with email ".$email." has been already registered";
                            $dataArr = array("code" => Globals::$STATUS_CODE_FAILED,
                            "message" => $message,
                            "method" => $method);
                            $responseInfo = Helper::getMessage('error', $message);
                            return Helper::sendFailedHttpResponse($responseInfo);
                            
                            
                        }
                        
                        
                    }else{
                        $message =  "username ".$username." has already been taken, choose another one";
                        $dataArr = array("code" => Globals::$STATUS_CODE_FAILED,
                        "message" => $message,
                        "method" =>  $method);
                        return Helper::sendFailedHttpResponse($message);
                        
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
        $user = User::find($id);
        return response()->json($user, 200);
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_number' => 'required',
            'gender' => 'required',
            'email' => 'required',
            'address' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
                
            }else{
                
                $author_id = $request->input('user_id');
                $first_name = $request->input('first_name');
                $last_name = $request->input('last_name');
                $phone_number = $request->input('phone_number');
                $gender = $request->input('gender');
                $email = $request->input('email');
                $address = $request->input('address');
                $user = User::find($id);
                $names = Helper::getUserNames($id);
                
                $user->first_name = $first_name;
                $user->last_name = $last_name;
                $user->gender = $gender;
                $user->email = $email;
                $user->phone_number = $phone_number;
                $user->address = $address;
                
                if($user->save()){
                    $author = Helper::getUserNames($author_id);
                    $role = Helper::getUserRoleName($author_id);
                    $action = "updated ".$names." details";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $message = Helper::getMessage('success', $action);
                    return Helper::sendOkHttpMessage($message);
                    
                }else{
                    $message ="Unable to update user details!";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }
        }catch(\Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
            
        }      
    }
    
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy(Request $request, $id)
    {
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
                
            }else{
                
                $author_id = $request->input('user_id');
                $author = Helper::getUserNames($author_id);
                $user = User::find($id);
                $is_deleted = $user->is_deleted;
                $undo = !$is_deleted;
                $activity = $undo ? 'deleted': 'restored';
                
                $names = Helper::getUserNames($id); 
                $user->is_deleted = $undo;
                $user->deleted_by = $author;
                if($user->save()){
                    $role = Helper::getUserRoleName($author_id);
                    $action = "".$activity." ".$names."'s account";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $message = Helper::getMessage('success', $action);
                    return Helper::sendOkHttpMessage($message);
                    
                }else{
                    $message ="Unable to delete user!";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }
        }catch(\Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
        
        
    }
    
    
    public function changePassword(Request $request)
    {
        ;
        try {
            
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
                            $name = $user->first_name." ".$user->last_name;
                            Helper::logActivity($request, ['name' => $name, 'role' => Helper::getUserRole($user->role), 'action' => $action]);
                            $message = Helper::getMessage('success', $action);
                            return Helper::sendOkHttpMessage(['message' => $message, 'data' => $user]);
                            
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
    
    
    public function changeAccountStatus(Request $request, $id)
    {
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'status' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
                
            }else{
                
                $author_id = $request->input('user_id');
                $status = $request->input('status');
                
                $statusAction = $status == 1 ? 'activated' : 'deactivated';
                if(User::where('id', $id)->exists()){
                    $user = User::find($id);
                    $names = Helper::getUserNames($id); 
                    $user->is_active = $status;
                    if($user->save()){
                        $author = Helper::getUserNames($author_id);
                        $role = Helper::getUserRoleName($author_id);
                        $action = "".$statusAction." ".$names."'s account";
                        Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                        $message = Helper::getMessage('success', $action);
                        return Helper::sendOkHttpMessage(['$message' => $message, 'data' => $user]);
                        
                    }else{
                        $message ="Unable to ".$statusAction." user account!";
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                }else{
                    $message ="User account doesn't exist!";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
            }
        }catch(\Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
            
        }
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
