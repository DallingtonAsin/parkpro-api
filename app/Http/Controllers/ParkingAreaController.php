<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\ParkingArea;
use App\Models\Client;
use Helper;
use Globals;
use TokenAuth;

class ParkingAreaController extends Controller
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
            $parking_areas = ParkingArea::orderBy('id', 'desc')->get();
            $num = 1000;
            foreach($parking_areas as $parking){
                $parking->address = Client::where('id', $parking->client_id)->value('address');
                $parking->client = Client::where('id', $parking->client_id)->value('name');
                $parking->image = "https://picsum.photos/".$num++."";
            }
            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message  = Globals::$STATUS_DESC_SUCCESS;
            $resp->data = $parking_areas;
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = Globals::$STATUS_DESC_ERROR;
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }
    
    public function getParkingSpots(Request $request)
    {
        $resp = new ApiResponse();
        try {
            if($request->filled(['client_id'])){
                $client_id = $request->input('client_id');
                $parking_spots = ParkingArea::where('client_id',  '=', $client_id)->get();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $parking_spots;
            }else{
                $messageErr = "Unable to process request:missing parameters";
                $responseInfo = Helper::getMessage('error', $messageErr);
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
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
            
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                
                if($request->filled(['client_name', 'parking_area', 'total_space'])){
                    
                    $client_id = $request->input('client_name');
                    $parking_area = $request->input('parking_area');
                    $total_space = $request->input('total_space');
                    
                    $client = Client::find($client_id);
                    $client_name = $client->name;
                    
                    $count = ParkingArea::where('client_id', '=', $client_id)->where('name', '=', $parking_area)->count();
                    if($count == 0){
                        $fee = $request->input('fee');
                        
                        $parkingArea = new ParkingArea();
                        
                        $parkingArea->client_id = $client_id;
                        $parkingArea->name = $parking_area;
                        $parkingArea->total_space = $total_space;
                        $parkingArea->current_free_space = $total_space;
                        
                        if ($parkingArea->save()) {
                            $action = "added parking area ".$parking_area." for client ".$client_name."";
                            $responseInfo = Helper::getMessage('success', $action);
                            Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->message = $responseInfo; 
                        } else {
                            $messageErr = "Adding parking area ".$parking_area." for client '.$client_name.' failed!";
                            $responseInfo = Helper::getMessage('error', $messageErr);
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message = $responseInfo;
                        }
                        
                    }else{
                        $messageErr = "Parking area ".$parking_area." for client ".$client_name." has been already added";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                } else {
                    $messageErr = "Unable to process request:missing parameters";
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
        $resp->data = ParkingArea::count();
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
