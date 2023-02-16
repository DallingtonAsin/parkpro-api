<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\SendMail;
use App\Models\Customer;
use App\Helpers\SharedCommon as Helper;
use App\Helpers\Globals as Globals;
use Mail;

class MailController extends Controller
{
    
    
    public function __construct()
    {
        
    }
    
    public function postFeedback(Request $request){
        
        try {
            if($request->filled(['id', 'reaction' ,'description'])){
                
                $customer_id = $request->input('id');
                $reaction = $request->input('reaction');
                $email = $request->input('email');
                $subject = $request->input('subject');
                $description = $request->input('description');
                
                if(empty($email)){
                    $email = "Didn't supply email";
                }
                
                if(empty($subject)){
                    $subject = "Customer Feedback";
                }
                
                $doesCustomerExist = Customer::where('id', $customer_id)->exists();
                
                if($doesCustomerExist){
                    $customer = Customer::find($customer_id);
                    $name = $customer->first_name." ".$customer->last_name;
                    $data = array(
                        'name' => $name,
                        'email' => $email,
                        'reaction' => $reaction,
                        'subject' => $subject,
                        'description' => $description,
                    );
                    $company_email = config('app.email');
                    Mail::to($company_email)->send(new SendMail($data));
                    if(Mail::failures()){
                        $message = 'Unable to send suggestion';
                        return Helper::sendFailedHttpResponse($message);
                        
                    }else{
                        $message =  "Your suggestion has been sent successfully"; 
                        return Helper::sendOkHttpResponse(['message' => $message, 'data' => $customer]);
                        
                    }
                    
                }else{
                    $message = "Unable to find customer with supplied details";
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
    
    
    
    
}
