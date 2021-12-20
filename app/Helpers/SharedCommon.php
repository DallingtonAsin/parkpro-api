<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\Models\RequestResponse;
use App\Models\ActivityLog;
use App\Models\ErrorLog;
use App\Models\Role;
use App\Models\Client;
use App\Models\ParkingArea;
use App\Models\VehicleCategory;
use App\Models\Customer;
use Carbon\Carbon;
use Globals;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SharedCommon
{

    public static function getParkingAreaName($areaId){
           $resp = new ApiResponse();
            try{
                $exists = ParkingArea::where('id', '=', $areaId)->exists();
                if($exists){
                    $name = ParkingArea::where('id', '=', $areaId)->value('name');
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message = "Results found";
                    $resp->data = $name;
                }else{
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = "No parking area found";
                }

            }catch(Exception $ex){
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = $ex->getMessage();
            }
            return $resp;
      }

      public static function getCustomerData($customer_id){
          try{
                $customer = Customer::find($customer_id);
                if($customer->account_balance >= 1000){
                      $customer->account_balance = number_format($customer->account_balance);
                 }else{
                     $customer->account_balance = $customer->account_balance;  
                 }
                
                if(isset($customer->image)){
                    $customer->image =  Storage::disk('public')->url($customer->image);
                }else{
                    $customer->image = $customer->image;
                }
            return $customer;
          }catch(\Exception $ex){
              throw $ex;
          }
      }

      public static function getClientName($id){
        $resp = new ApiResponse();
         try{
             $exists = Client::where('id', '=', $id)->exists();
             if($exists){
                 $name = Client::where('id', '=', $id)->value('name');
                 $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                 $resp->message = "Results found";
                 $resp->data = $name;
             }else{
                 $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                 $resp->message = "No client found";
             }

         }catch(Exception $ex){
             $resp->statusCode = Globals::$STATUS_CODE_ERROR;
             $resp->message = $ex->getMessage();
         }
         return $resp;
   }


   public static function getVehicleTypeName($id){
    $resp = new ApiResponse();
     try{
         $exists = VehicleCategory::where('id', '=', $id)->exists();
         if($exists){
             $name = VehicleCategory::where('id', '=', $id)->value('name');
             $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
             $resp->message = "Results found";
             $resp->data = $name;
         }else{
             $resp->statusCode = Globals::$STATUS_CODE_FAILED;
             $resp->message = "No vehicle type found";
         }

     }catch(Exception $ex){
         $resp->statusCode = Globals::$STATUS_CODE_ERROR;
         $resp->message = $ex->getMessage();
     }
     return $resp;
}





    public static function logError($data)
    {
        try {
            $username = $data['username'];
            $error_code = $data['error_code'];
            $error_message = $data['error_message'];
            $error_severity = $data['error_severity'];
            $controller = $data['controller'];
            $method = $data['method'];
            
            $error = new ErrorLog();
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
        $log = new ActivityLog();
        $log->name = $data['name'];
        $log->role = $data['role'];
        $log->description = $data['action'];
        $log->ip_address = \Request::getClientIp();
        $log->date = Carbon::now();
        $log->save();
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
    
    public static function Numberize($input){
        try{
            $result = floatval(preg_replace('/[^\d.]/','', $input));
            return $result;
        }catch(\Exception $ex){
            dd($ex->getMessage());
        }
    }
    
    public static function getMessage($status, $activity)
    {
        $status == 'error'
        ? $message = $activity
        : $message = "You have successfully ".$activity."";
        return $message;
    }

    public static function getUserRole($id){
        $role = Role::find($id);
        return $role->name;
    }
    
    
}
