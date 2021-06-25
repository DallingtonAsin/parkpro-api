<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\activityLog;
use App\Models\errorLog;
use App\Models\Tenant;
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


    public static function logActivity(Request $request, $user, $action)
    {
        $log = new activityLog();
        $log->name =$name =  $user;
        $log->role = 'role';
        $log->description = $action;
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

}
