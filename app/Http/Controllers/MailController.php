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
    
    protected $response;
    
    public function __construct(ApiResponse $response)
    {
        $this->response = $response;
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
                            $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                            $this->response->message = 'Unable to send suggestion';
                        }else{
                            $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $this->response->message =  "Your suggestion has been sent successfully"; 
                        }

                    }else{
                        $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                        $this->response->message = "Unable to find customer with supplied details";
                    }
                }
                else{
                    $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                    $this->response->message = "Unable to process request";
                }
            
        } catch (\Exception $ex) {
            $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->response->message = $ex->getMessage();
            $this->response->data = $ex->getMessage();
        }
        
        return response()->json($this->response);
    }




}
