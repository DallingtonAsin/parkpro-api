<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Firebase\FCMService;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ApiResponse;
use Globals;


class NotificationController extends Controller
{
    protected $firebaseService;
    
    public function __construct(FCMService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendPushNotificationToUser(Request $request){
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $response['message'] = $validator->errors()->all();
            }else{

                $fcmToken = $request->input('fcm_token');
                $notification = [
                    'title' => 'Greeting',
                    'body' => 'How are you sir',
                ];
                $resp = $this->firebaseService->send($fcmToken, $notification);
                dd($resp);
            }
        }catch(\Exception $ex){
            $response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $response['message'] = $ex->getMessage();
        }
        return response()->json($response, 200);
    }



}
