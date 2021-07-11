<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\activityLog;
use App\Models\errorLog;
use App\Models\Tenant;
use App\Models\RequestResponse;
use Carbon\Carbon;

class SharedCommon
{
    public static function logError($data)
    {
        try {
            $username = $data['username'];
            $error_code = $data['error_code'];
            $error_message = $data['error_message'];
            $error_severity = $data['error_severity'];
            $controller = $data['controller'];
            $method = $data['method'];

            $error = new errorLog();
            $error->username = $username;
            $error->error_code = $error_code;
            $error->error_message = $error_message;
            $error->error_severity = $error_severity;
            $error->controller = $controller;
            $error->method = $method;

            $resp = $error->save();
        } catch (\Exception $ex) {
        }
    }


    public static function logActivity(Request $request, $data)
    {
        $log = new activityLog();
        $log->name = $data['name'];
        $log->role = $data['role'];
        $log->description = $data['action'];
        $log->ip_address = \Request::getClientIp();
        $log->date = Carbon::now();
        $log->save();
    }

  
     public static function getTenantName($id){
        try{
            $name = Tenant::where('id', $id)->value('name');
        }catch(\Exception $ex){
            $name = $ex->getMessage();
        }
        return $name;
    }

    public static function is_connectedToInternet()
    {
      $connected = @fsockopen('www.google.com', 80);
      if($connected){
        $is_conn = 1;
        fclose($connected);
      }
      else{
       $is_conn = 0;
     }
     return $is_conn;
   }

   public static function LogRequest(Request $request, $responseArr)
   {
       $r = new RequestResponse;
       $r->request = json_encode($request->all());
       $r->response = json_encode($responseArr);
       $r->method = $request->method().":".$responseArr["method"];
       $r->url = $request->fullUrl();
       $r->ip_address = $request->ip();
       $r->save();

   }

   public static function getMessage($status, $activity)
   {
       $status == 'error'
       ? $message = $activity
       : $message = "You have successfully ".$activity."";
       return $message;
   }


}
