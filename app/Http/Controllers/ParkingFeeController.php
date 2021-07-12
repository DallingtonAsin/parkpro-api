<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use App\Models\ParkingFee;
use App\Models\VehicleCategory;
use Helper;

class ParkingFeeController extends Controller
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
            $tenants = ParkingFee::all();
            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message  = Globals::$STATUS_DESC_SUCCESS;
            $resp->data = $tenants;
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
        
        try{
            
            if(($request->has('vehicle_category') && $request->filled('vehicle_category')) && ($request->has('fee') && $request->filled('fee'))){
                $vehicle_category_id = $request->input('vehicle_category');
                $item = VehicleCategory::find($vehicle_category_id);
                $name = $item->name;
                $count = ParkingFee::where('vehicle_cat_id', '=', $vehicle_category_id)->count();
                if($count == 0){
                    $fee = $request->input('fee');
                    $parkingFee = new ParkingFee();
                    $parkingFee->vehicle_cat_id = $vehicle_category_id;
                    $parkingFee->fee = Helper::Numberize($fee);
                    if ($parkingFee->save()) {
                        $action = "added parking fee for vehicle category ".$name."";
                        $responseInfo = Helper::getMessage('success', $action);
                        Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                        $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                        $resp->message = $responseInfo; 
                    } else {
                        $messageErr = "Adding parking fee for vehicle category '.$name.' failed!";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                    
                }else{
                    $messageErr = "Fee for category ".$name." has been added already";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            } else {
                $messageErr = "Failed to get vehicle category and fee from request";
                $responseInfo = Helper::getMessage('error', $messageErr);
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $resp->data = ParkingFee::count();
        return response()->json($resp);
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
