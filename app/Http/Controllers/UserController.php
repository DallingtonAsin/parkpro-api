<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use Illuminate\Hashing\BcryptHasher;
use Helper;
use TokenAuth;

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
}
