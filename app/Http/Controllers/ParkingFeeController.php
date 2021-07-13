<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use App\Models\ParkingFee;
use App\Models\ParkingArea;
use App\Models\Client;
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
            $parking_fees = ParkingFee::all();
            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message  = Globals::$STATUS_DESC_SUCCESS;
            $resp->data = $parking_fees;
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
                $client_id = $request->input('client');
                $parking_area_id = $request->input('parking_area');
                $vehicle_category_id = $request->input('vehicle_category');
                
                $clientName = Client::where('id', $client_id)->value('name');
                $parkingAreaName = ParkingArea::where('id', $parking_area_id)->value('area');
                $vehicleCatName = VehicleCategory::where('id', $vehicle_category_id)->value('name');

                $count = ParkingFee::where('client_id', '=', $client_id)
                ->where('parking_area_id', '=', $parking_area_id)
                ->where('vehicle_cat_id', '=', $vehicle_category_id)
                ->count();
                
                if($count == 0){
                    $fee = $request->input('fee');
                    $parkingFee = new ParkingFee();
                    
                    $parkingFee->client_id = $client_id;
                    $parkingFee->parking_area_id = $parking_area_id;
                    $parkingFee->vehicle_cat_id = $vehicle_category_id;
                    $parkingFee->fee = Helper::Numberize($fee);
                    
                    if ($parkingFee->save()) {
                        $action = "added parking fee for vehicle category ".$vehicleCatName." for ".$clientName."'s parking area ".$parkingAreaName." ";
                        $responseInfo = Helper::getMessage('success', $action);
                        Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                        $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                        $resp->message = $responseInfo; 
                    } else {
                        $messageErr = "Failed to add parking fee for vehicle category '.$vehicleCatName.' for ".$clientName."'s parking area ".$parkingAreaName."!";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                    
                }else{
                    $messageErr = "Fee for vehicle category ".$vehicleCatName." for ".$clientName."'s parking area ".$parkingAreaName." has been already added";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            } else {
                $messageErr = "Failed to get details from request for adding fee";
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
