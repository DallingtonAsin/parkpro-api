<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\SendMail;
use App\Models\Customer;
use App\Helpers\ApiResponse;
use Helper;
use Globals;
use Mail;

class MailController extends Controller
{
    


    public function postCustomerSuggestion(Request $request){
        $resp = new ApiResponse();
        try {
                if($request->filled(['id', 'reaction', 'email','subject','description'])){

                    $customer_id = $request->input('id');
                    $reaction = $request->input('reaction');
                    $email = $request->input('email');
                    $subject = $request->input('subject');
                    $description = $request->input('description');

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
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message = 'Unable to send suggestion';
                        }else{
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->message =  "Your suggestion has been sent successfully"; 
                        }

                    }else{
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = "Unable to find customer with supplied details";
                    }
                }
                else{
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request";
                }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }




}
