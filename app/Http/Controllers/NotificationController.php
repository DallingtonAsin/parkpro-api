<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Firebase\FCMService;
use Illuminate\Support\Facades\Validator;
use App\Models\Customer;
use Globals;
use Helper;


class NotificationController extends Controller
{
    protected $firebaseService, $response;
    
    public function __construct(FCMService $firebaseService, ApiResponse $response)
    {
        $this->firebaseService = $firebaseService;
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
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
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
                        $data['fcm_token'] = $fcmToken;
                        return Helper::sendOkHttpResponse($data);
                        
                    }else{
                        $message = Globals::$STATUS_CODE_ERROR;
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                    
                }else{
                    $message = "Unable to find customer";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
                
            }
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
    }
    
    
    
}
