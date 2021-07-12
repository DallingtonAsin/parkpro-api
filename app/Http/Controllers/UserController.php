<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Mail\RegistrationMailSender;
use Helper;
use TokenAuth;
use Mail;

class UserController extends Controller
{
    public $apiResponse = [];
    public function login(Request $request){ 
        if($request->isMethod('post')){
            if ($request->filled(['username', 'password'])) {
                $postData = $request->only(['username', 'password']);
                $postData['username'] = strtolower($postData['username']);
                // $user = User::where('email', '=', $postData['username'])->get()->first();
                $user = User::all()->firstWhere('email', '=', $postData['username']);
                $checkPass = (new BcryptHasher())->check($postData['password'], $user['password']);
                
                // if($user['role_id'] == '1' || $user['role_id'] == '2'  ){
                    if (!empty($checkPass) && $checkPass == '1') {
                        $authToken = (new BcryptHasher())->make($postData['username'] . time());
                        if(!empty($authToken)){
                            $userAuthData['user_id'] =  $user['_id'];
                            $userAuthData['user_email'] =  $user['email'];
                            $userAuthData['auth_token'] =  $authToken;
                            $userAuthData['platform'] =  'web';
                            $userAuthData['is_session'] =  'Y';
                            $userAuthData['status'] =  '1';
                        }
                        
                        $this->apiResponse['success'] = 1;
                        $this->apiResponse['message'] = 'User logged in successfully';
                        $this->apiResponse['data']['user_id'] = $user['id'];
                        $this->apiResponse['data']['name'] = $user['first_name'];
                        $this->apiResponse['data']['email'] = $user['email'];
                        $this->apiResponse['data']['mobile_number'] = $user['tel_no'];
                        
                    } else {
                        $this->apiResponse['success'] = 0;
                        $this->apiResponse['message'] = 'Invalid credentials';
                    }
                    // }else{
                        //     $this->apiResponse['success'] = 0;
                        //     $this->apiResponse['message'] = "Unauthorized Access";
                        // }
                    } else {
                        $this->apiResponse['success'] = 0;
                        $this->apiResponse['message'] = "Username or password should not be empty";
                    }
                    return response()->json($this->apiResponse, 200);
                }
                
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
                        $parking_requests = User::all();
                        $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                        $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                        $resp->data = $parking_requests;
                        
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
            
            /**
            * Store a newly created resource in storage.
            *
            * @param  \Illuminate\Http\Request  $request
            * @return \Illuminate\Http\Response
            */
            
            public function store(Request $request)
            {
                $resp = new ApiResponse();
                
                try{
                    
                    $method = "UserController@store";
                    $fname = trim($request->input('first_name'));
                    $lname = trim($request->input('last_name'));
                    $address = trim($request->input('address'));
                    $email = trim($request->input('email'));
                    $telno = trim($request->input('mobile_no'));
                    $nin = trim($request->input('nin'));
                    $gender = trim($request->input('gender'));
                    $role = 1; //$request->input('role');
                    $registra = 'Dallington'; // $request->user()->name;
                    $name = $fname." ".$lname;
                    $defaultPwd = '12345678';
                    
                    if($request->has('id') && $request->filled('id')) {
                        $user = User::find($request->input('id'));
                        $username = $user->username;
                        $password = $user->password; 
                        
                    }else {
                        $user = new User();
                        $name = $fname." ".$lname;
                        $username = strtolower(Str::random(6).".".$fname);
                        $password = Hash::make($defaultPwd, ['rounds' => 12]);
                    }
                    
                    $bool_userExists = User::where('username' ,$username)->exists();
                    
                    if(!$request->filled('id') && $bool_userExists)
                    {
                        $message =  "username ".$name." has already been taken, choose another one";
                        $dataArr = array("code" => Globals::$STATUS_CODE_FAILED,
                        "message" => $message,
                        "method" =>  $method);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;          
                    }
                    else
                    {
                        
                        $count = User::where('email', '=', $email)->count();
                        if($count == 0){
                            
                            $user->first_name = $fname;
                            $user->last_name = $lname;
                            $user->name = $name;
                            $user->username = $username;
                            $user->gender = $gender;
                            $user->email = $email;
                            $user->user_role = $role;
                            $user->mobile_no = $telno;
                            $user->address = $address;
                            $user->national_id_no = $nin;
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
                                $action =  "registered user ".$name."";
                                $sendAction = "You have been registered as a client at parksmart today at ".$now."";
                                Helper::logActivity($request, ['name' => $name, 'role' => $role, 'action' => $action]);
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
                                    $message = "User ".$name." has been registered successfully and email has been sent";
                                    
                                }else{
                                    $message = "User ".$name." has been registered successfully";
                                }
                                $dataArr = array("code" => Globals::$STATUS_CODE_SUCCESS,
                                "message" => $message,
                                "method" => $method);
                                Helper::LogRequest($request, $dataArr);
                                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                $resp->data = User::count();
                            }
                            else
                            {
                                $message = "User registration failed!";
                                $dataArr = array("code" => Globals::$STATUS_CODE_FAILED,
                                "message" => $message,
                                "method" => $method);
                                Helper::LogRequest($request, $dataArr);
                                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            }
                            
                        }else{
                            $message = "User with email ".$email." has been added registered";
                            $dataArr = array("code" => Globals::$STATUS_CODE_FAILED,
                            "message" => $message,
                            "method" => $method);
                            $responseInfo = Helper::getMessage('error', $message);
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message  = $responseInfo;
                            
                        }
                    }
                } catch (\Exception $ex) {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $message = $ex->getMessage();
                    $dataArr = array("code" => Globals::$STATUS_CODE_ERROR,
                    "message" => $message,
                    "method" => $method);
                }
                
                $resp->message = $message;
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
        }
