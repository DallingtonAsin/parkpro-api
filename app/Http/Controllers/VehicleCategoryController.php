<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use App\Models\VehicleCategory;
use Helper;

class VehicleCategoryController extends Controller
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
            $tenants = VehicleCategory::all();
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
            
            if($request->has('name') && $request->filled('name')){
                $name = $request->input('name');
                $count = VehicleCategory::where('name', '=', $name)->count();
                if($count == 0){
                    
                    $vehicleCat = new VehicleCategory();
                    $vehicleCat->name = $name;
                    if ($vehicleCat->save()) {
                        $action = "added vehicle category ".$name."";
                        $responseInfo = Helper::getMessage('success', $action);
                        Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                        $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                        $resp->message = $responseInfo; 
                    } else {
                        $messageErr = "adding vehicle category failed!";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                } else {
                    $messageErr = "Vehicle category ".$name." has already been added";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
                
            }else{
                $messageErr = "Failed to get vehicle category and fee from request";
                $responseInfo = Helper::getMessage('error', $messageErr);
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $resp->data = VehicleCategory::count();
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
