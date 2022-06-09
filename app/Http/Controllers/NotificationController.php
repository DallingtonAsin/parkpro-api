<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Firebase\FCMService;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use App\Models\Customer;
use Globals;


class NotificationController extends Controller
{
    protected $firebaseService, $response;
    
    public function __construct(FCMService $firebaseService, ApiResponse $response)
    {
        $this->firebaseService = $firebaseService;
        $this->response = $response;
    }
    
     /**
    * Send push notification to a given device.
    *
    * @return \Illuminate\Http\Response
    */
    public function sendPushNotificationToUser(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        
        try{
           
            if($validator->fails()){
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $validator->errors()->all();
            }else{
                
                $userId = $request->input('id');
                $exists = Customer::where("id", $userId)->exists();
                if($exists){
                    
                    $customer = Customer::find($userId);
                    $fcmToken = $customer->fcm_token;
                    
                    $notification = [
                        'title' => 'Goodnight',
                        'body' => 'Have a blessed night sir?',
                    ];
                    $res = $this->firebaseService->send($fcmToken, $notification);
                    $respCode= $res->getStatusCode();

                    if($respCode == '200'){
                        $this->response->statusCode = $respCode;
                        $this->response->message = "Notification sent successfully";
                        $this->response->data['fcm_token'] = $fcmToken;
                    }else{
                        $this->response->statusCode = $respCode;
                        $this->response->message = Globals::$STATUS_CODE_ERROR;
                    }
                    
                }else{
                    $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                    $this->response->message = "Unable to find customer";
                }
                
            }
        }catch(\Exception $ex){
            $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->response->message = $ex->getMessage();
        }
        return response()->json($this->response);
    }
    
    
    
}
