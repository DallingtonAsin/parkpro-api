<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use App\Models\Payment;
use App\Models\Tenant;
use Helper;

class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
          $resp = new ApiResponse();
         try {

                $payments = Payment::all();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $payments;

            } catch (\Exception $ex) {
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = $ex->getMessage();
                $resp->data = $ex->getMessage();
            }

            return response()->json($resp);
    }


       public function recentPayments(){
          $resp = new ApiResponse();
         try {

                $payments = Payment::orderBy('id', 'desc')
                ->take(5)
                ->get();

                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $payments;

            } catch (\Exception $ex) {
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = Globals::$STATUS_DESC_ERROR;
                $resp->data = $ex->getMessage();
            }

            return response()->json($resp);
    }

   

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
          $resp = new ApiResponse();
    
        try {
            
            $month = $request->input('month');
            $year = $request->input('year');
            $tenant_id = $request->input('tenant_id');
            $monthName = date("F", mktime(0, 0, 0, $month, 10));
            $period = $monthName.' '.$year;
            $tenantName = Tenant::where('id', $tenant_id)->value('name');

            if (Payment::where('tenant_id', '=', $tenant_id)->where('month', '=', $month)->where('year', '=', $year)->exists()) {
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = 'Sorry, A payment transaction for period '.$period.' for tenant '.$tenantName.' has already been recorded';
            } else {

                
                $month = $request->input('month');
                $year = $request->input('year');
                $expected_amount = $request->input('expected_amount');
                $paid_amount = $request->input('paid_amount');
                $date_of_payment = $request->input('date_of_payment');
                $comment = $request->input('comment');
                $user = $request->input('user');

                $payment = new Payment();
                $payment->tenant_id = $tenant_id;
                $payment->month = $month;
                $payment->year = $year;
                $payment->expected_amount = $expected_amount;
                $payment->paid_amount = $paid_amount;
                $payment->date_of_payment = $date_of_payment;
                $payment->comment =  $comment;

                if ($payment->save()) {
                   
                    $action = "recorded payment details of rent amount ".number_format($paid_amount)." for tenant ".$tenantName." for the period of ".$period."";
                    $sessionVariable = 'success';
                    $responseInfo = $this->getMessage('success', $action);
                    Helper::logActivity($request, $user, $action);
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message = $responseInfo ;
                } else {
                    $messageErr = "recording of payment details for ".$tenantName." failed!";
                    $responseInfo = $this->getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $arr = $this->getPaymentStats();
        $resp->data = $arr['totl'];
        return response()->json($resp);


    }

     protected function getPaymentStats()
    {
        $total_payments = Payment::count();
        $data = array(
          'totl' => $total_payments,
      );
        return $data;
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
    public function update(Request $request)
    {
        
        $resp = new ApiResponse();
    
        try {

            $payment_id = $request->input('id');
            $month = $request->input('month');
            $year = $request->input('year');
            $tenant_id = $request->input('tenant_id');

            $monthName = date("F", mktime(0, 0, 0, $month, 10));
            $period = $monthName.' '.$year;
            $tenantName = Tenant::where('id', $tenant_id)->value('name');

            if (Payment::where('id', '=', $payment_id)->exists()) {
                
                $month = $request->input('month');
                $year = $request->input('year');
                $expected_amount = $request->input('expected_amount');
                $paid_amount = $request->input('paid_amount');
                $date_of_payment = $request->input('date_of_payment');
                $comment = $request->input('comment');
                $user = $request->input('user');

                $payment = Payment::find($payment_id);

                $payment->tenant_id = $tenant_id;
                $payment->month = $month;
                $payment->year = $year;
                $payment->expected_amount = $expected_amount;
                $payment->paid_amount = $paid_amount;
                $payment->date_of_payment = $date_of_payment;
                $payment->comment =  $comment;

                if ($payment->save()) {
                   
                    $action = "updated payment details of rent amount ".number_format($paid_amount)." for tenant ".$tenantName." for the period of ".$period."";
                    $sessionVariable = 'success';
                    $responseInfo = $this->getMessage('success', $action);
                    Helper::logActivity($request, $user, $action);
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message = $responseInfo ;
                } else {
                    $messageErr = "updating of payment details for ".$tenantName." failed!";
                    $responseInfo = $this->getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }

            } else {

                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = "Sorry, we couldn't find payment transaction with payment id '.$payment_id.'";
         
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $arr = $this->getPaymentStats();
        $resp->data = $arr['totl'];
        return response()->json($resp);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $resp = new ApiResponse();

        try {
            
            $id = $request->input('id');
            $month = $request->input('month');
            $year = $request->input('year');
            $tenant_id = $request->input('tenant_id');
            $user = $request->input('user');

            $monthName = date("F", mktime(0, 0, 0, $month, 10));
            $period = $monthName.' '.$year;
            $tenantName = Tenant::where('id', $tenant_id)->value('name');

            $payment = Payment::where('id', '=', $id)
                      ->where('tenant_id', '=', $tenant_id)
                      ->where('month', '=', $month)
                      ->where('year', '=', $year)
                      ->first();

            if ($payment->delete()) {
                $action = "deleted payment details of tenant ".$tenantName." for the period ".$period." from the system";
                Helper::logActivity($request, $user, $action);
                $responseInfo = $this->getMessage('success', $action);

                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message = $responseInfo ;
            } else {
                $messageErr = "payment details of tenant ".$tenantName."  not removed!";
                $responseInfo = $this->getMessage('error', $messageErr);

                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }

        $arr = $this->getPaymentStats();
        $resp->data = $arr['totl'];
        return response()->json($resp);
    }

     protected function getMessage($status, $activity)
    {
        $status == 'error'
        ? $message = $activity
        : $message = "You have successfully ".$activity."";
        return $message;
    }


}
