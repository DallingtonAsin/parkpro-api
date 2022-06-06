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
    protected $firebaseService;
    private $response;
    
    public function __construct(FCMService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
        $this->response = [];
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
            $resp = new ApiResponse();
            if($validator->fails()){
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = $validator->errors()->all();
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
                        $resp->statusCode = $respCode;
                        $resp->message = "Notification sent successfully";
                        $resp->data['fcm_token'] = $fcmToken;
                    }else{
                        $resp->statusCode = $respCode;
                        $resp->message = Globals::$STATUS_CODE_ERROR;
                    }
                    
                }else{
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to find customer";
                }
                
            }
        }catch(\Exception $ex){
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        return response()->json($resp);
    }
    
    
    
}
