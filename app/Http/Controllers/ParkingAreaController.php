<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\ParkingArea;
use App\Models\ParkingFee;
use App\Models\Client;
use App\Models\VehicleCategory;
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
                $parking->spots = $parking->total_space;
                $parking->free = $parking->current_free_space;
                $parking->client = Client::where('id', $parking->client_id)->value('name');
                $parking->image = "https://picsum.photos/".$num++."";
                $parking->coordinate = array("latitude" => $parking->latitude, "longitude" => $parking->longitude);
                $parking->distance = 35;
                $parking->fees = $this->getParkingFees($parking->id);
                $parking->is_open = date('H') < date('H', strtotime($parking->closes_at)) ? true : false;
                 
                // unset($parking->total_space);
                // unset($parking->current_free_space);
                // unset($parking->created_at);
                // unset($parking->updated_at);

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


    private function getParkingFees($parking_area_id){
       try{
        $vehicleCats = VehicleCategory::all();
        $fees = [];
        foreach($vehicleCats as $cat){
         $fee_per_hour = ParkingFee::where('parking_area_id', $parking_area_id)
                ->where('vehicle_cat_id', $cat->id)->value('fee_per_hour');
        $fees[$cat->name] = $fee_per_hour; 
        }
       return $fees;
       }catch(Exception $e){
           throw $e;
       }
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

    public function filterParkingAreas(Request $request)
    {
        $resp = new ApiResponse();
        try {
            if($request->filled(['search'])){
                $search = $request->input('search');
                $searchResults = ParkingArea::where('name',  'like', '%'.$search.'%')->get();
                $num = 2000;
                foreach($searchResults as $parking){
                    $parking->address = Client::where('id', $parking->client_id)->value('address');
                    $parking->client = Client::where('id', $parking->client_id)->value('name');
                    $parking->image = "https://picsum.photos/".$num++."";
                }
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $searchResults;
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
                
                if($request->filled(['client_id', 'name', 'address', 'description', 'opens_at', 'closes_at', 'latitude', 'longitude', 'total_space'])){
                    
                    $client_id = $request->input('client_id');
                    $name = $request->input('name');
                    $address = $request->input('address');
                    $description = $request->input('description');
                    $opens_at = $request->input('opens_at');
                    $closes_at = $request->input('closes_at');
                    $latitude = $request->input('latitude');
                    $longitude = $request->input('longitude');
                    $total_space = $request->input('total_space');
                    
                    $doesClientExist  = Client::where('id', '=', $client_id)->exists();
                    if($doesClientExist){

                            $client = Client::find($client_id);
                            $client_name = $client->name;
                            
                            $count = ParkingArea::where('client_id', '=', $client_id)->where('name', '=', $name)->count();
                            if($count == 0){
                                
                                $parkingArea = new ParkingArea();
                                
                                $parkingArea->client_id = $client_id;
                                $parkingArea->name = $name;
                                $parkingArea->address = $address;
                                $parkingArea->description = $description;
                                $parkingArea->opens_at = date('H:i:s', strtotime($opens_at));
                                $parkingArea->closes_at = date('H:i:s', strtotime($closes_at));
                                $parkingArea->latitude = floatval($latitude);
                                $parkingArea->longitude = floatval($longitude);
                                $parkingArea->total_space = $total_space;
                                $parkingArea->current_free_space = $total_space;
                                
                                if ($parkingArea->save()) {
                                    $action = "added parking area ".$name." for client ".$client_name."";
                                    $responseInfo = Helper::getMessage('success', $action);
                                    Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                    $resp->message = $responseInfo; 
                                } else {
                                    $messageErr = "Adding parking area ".$name." for client '.$client_name.' failed!";
                                    $responseInfo = Helper::getMessage('error', $messageErr);
                                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                    $resp->message = $responseInfo;
                                }
                                
                    }else{
                        $messageErr = "Parking area ".$name." for client ".$client_name." has been already added";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                }else{
                    $messageErr = "Details of the client not found";
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
