<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Customer;
use Helper;
use TokenAuth;
use Globals;
use LaramanBeyonic;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $resp = new ApiResponse();
        $paymentData = array(
            'phonenumber' => '256774014727',
            'amount'      => '1000',
            'currency'    => 'UGX',
            'description' => 'Pay Dallington this money',
            /* Information used by application to identify transaction */
            'metadata'    => "{ 'appId': '2952025', 'xactId': '1000000' }"
        );
        // f62a81d491fb2921d99797f3825c3fbf014b2f17
        
        try {
          $response = LaramanBeyonic::createCollectionRequest($paymentData);
          $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
          $resp->message  = Globals::$STATUS_DESC_SUCCESS;
          $resp->data = $response;
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = null;
        }

        return response()->json($resp);
    }


    public function topupUserAccount(Request $request){
        $resp = new ApiResponse();

        try{
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                
                if($request->filled(['customer_id', 'amount'])){
                    $customer_id = $request->input('customer_id');
                    $amount = $request->input('amount');
                    $exists = Customer::where('id', $customer_id)->exists();
                    
                    if($exists){

                        $amount = Helper::Numberize($amount);
                        $hasUpdated = Customer::where('id', $customer_id)->increment('account_balance', $amount);
                        $customer = Customer::find($customer_id);
                        
                        if ($hasUpdated) {
                            $customer_names = $customer->first_name. " ".$customer->last_name;
                            $action = "topped up ".$customer_names." account's with amount worth ".$amount;
                            $responseInfo = Helper::getMessage('success', $action);
                            Helper::logActivity($request, ['name' => 'System', 'role' => 'system', 'action' => $action]);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->message = $responseInfo; 
                        } else {
                            $messageErr = "Unable to top up customer account!";
                            $responseInfo = Helper::getMessage('error', $messageErr);
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message = $responseInfo;
                        }
                        
                    }else{
                        $messageErr = "Failed to find supplied customer id";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                } else {
                    $messageErr = "Unable to process requuest: missing parameters";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            }else{
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = "Unauthorized access";
                $resp->data = "Unauthorized access";
                
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        return response()->json($resp);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
