<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use App\Models\Invoice;
use Helper;

class InvoicesController extends Controller
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

                $invoices = Invoice::all();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $invoices;

            } catch (\Exception $ex) {
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = $ex->getMessage();
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
           
                $tenant_id = $request->input('tenant_id');
                $house = $request->input('house');
                $phone = $request->input('phone');
                $month = $request->input('month');
                $year = $request->input('year');
                $total = $request->input('total');
                $particulars = $request->input('particulars');
                $status = $request->input('status');
                $comment = $request->input('comment');
                $user = $request->input('user');

                $invoice = new Invoice();

                $invoice->tenant_id = $tenant_id;
                $invoice->house = $house;
                $invoice->phone = $phone;
                $invoice->month =  $month;
                $invoice->year = $year;
                $invoice->total = $total;
                $invoice->particulars = $particulars;
                $invoice->status =  $status;
                $invoice->comment = $comment;

                if ($invoice->save()) {

                      $tenantName = Helper::getTenantName($tenant_id);
                      $monthName = date("F", mktime(0, 0, 0, $month, 10));
                      $period = $monthName.' '.$year;

                    $action = "recorded details of invoice for tenant ".$tenantName." for the period of ".$period."";
                    
                    $responseInfo = $this->getMessage('success', $action);
                    Helper::logActivity($request, $user, $action);
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message = $responseInfo ;
                } else {
                    $messageErr = "storing new invoice failed!";
                    $responseInfo = $this->getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $arr = $this->getinvoiceStats();
        $resp->data = $arr['totl'];
        return response()->json($resp);
    }


     protected function getinvoiceStats()
    {
        $total_invoices = Invoice::count();
        $data = array(
          'totl' => $total_invoices,
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

            
            $invoice_id = $request->input('id');
            $tenant_id = $request->input('tenant_id'); 
            if (Invoice::where('id', '=', $invoice_id)->where('tenant_id', '=', $tenant_id)->exists()) {
                
                $house = $request->input('house');
                $phone = $request->input('phone');
                $month = $request->input('month');
                $year = $request->input('year');
                $total = $request->input('total');
                $particulars = $request->input('particulars');
                $status = $request->input('status');
                $comment = $request->input('comment');
                $user = $request->input('user');

                $monthName = date("F", mktime(0, 0, 0, $month, 10));
                $period = $monthName.' '.$year;
                $tenantName = Helper::getTenantName($tenant_id);
              

                $invoice = Invoice::find($invoice_id);

                $invoice->tenant_id = $tenant_id;
                $invoice->house = $house;
                $invoice->phone = $phone;
                $invoice->month =  $month;
                $invoice->year = $year;
                $invoice->total = $total;
                $invoice->particulars = $particulars;
                $invoice->status =  $status;
                $invoice->comment = $comment;

                if ($invoice->save()) {
                   
                    $action = "updated invoice details for tenant ".$tenantName." for the period of ".$period."";
                    $responseInfo = $this->getMessage('success', $action);
                    Helper::logActivity($request, $user, $action);
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message = $responseInfo ;
                } else {
                    $messageErr = "updating of invoice details for ".$tenantName." failed!";
                    $responseInfo = $this->getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }

            } else {

                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = "Sorry, we couldn't find invoice with invoice id '.$invoice_id.'";
         
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $arr = $this->getinvoiceStats();
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
            $user = $request->input('user');
            $invoice = Invoice::find($id);

            $tenantName = Helper::getTenantName($invoice->tenant_id);
            $monthName = date("F", mktime(0, 0, 0, intval($invoice->month), 10));
            $period = $monthName.' '.$invoice->year;

            if ($invoice->delete()) {
                $action = "deleted invoice for tenant ".$tenantName." for the period of ".$period."";
                Helper::logActivity($request, $user, $action);
                $responseInfo = $this->getMessage('success', $action);

                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message = $responseInfo ;
            } else {
                $messageErr = "invoice not removed!";
                $responseInfo = $this->getMessage('error', $messageErr);

                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }

        $arr = $this->getinvoiceStats();
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
