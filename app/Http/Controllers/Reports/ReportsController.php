<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Models\House;
use App\Models\Payment;
use App\Models\Tenant;
use Helper;

class ReportsController extends Controller
{
    //

    public function GetDefaulters(Request $request){
        try{

            return view('reports.defaulters');

        }catch(\Exception $ex){
            dd($ex->getMessage());
        }
    }


     public function GetStats(Request $request){
            $resp = new ApiResponse();
         try {

                $totl_payments = Payment::count();
                $totl_tenants = Tenant::count();
                $totl_houses = House::count();


                $stats = array(
                              'totl_payments' => $totl_payments,
                              'totl_houses' => $totl_houses,
                              'totl_tenants' => $totl_tenants,
                );

                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $stats;

            } catch (\Exception $ex) {
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = Globals::$STATUS_DESC_ERROR;
                $resp->data = $ex->getMessage();
            }

            return response()->json($resp);
      }




}
