<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\VehicleCategory;
use Helper;
use Globals;
use Validator;

class VehicleCategoryController extends Controller
{

    public $response = [];

    public function __constructor(){
        $this->response = new ApiResponse();
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $resp = new ApiResponse();
        try {
            $vehicle_categories = VehicleCategory::all();
            foreach($vehicle_categories as $cat){
                $cat->name = strtolower($cat->name);
            }
            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message  = Globals::$STATUS_DESC_SUCCESS;
            $resp->data = $vehicle_categories;

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
        $vehicleType = VehicleCategory::find($id);
        return response()->json($vehicleType, 200);
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'name' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->response['message'] = $validator->errors()->all();
            }else{
                
                $author_id = $request->input('user_id');
                $name = $request->input('name');

                $vehicleType = VehicleCategory::find($id);
                $vehicle_type = $vehicleType->name;
                
                $vehicleType->name = $name;

                if($vehicleType->save()){
                    $author = Helper::getUserNames($author_id);
                    $role = Helper::getUserRoleName($author_id);
                    $action = "updated vehicle type ".$vehicle_type." details";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response['message'] = Helper::getMessage('success', $action);
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                }else{
                    $this->response['message'] ="Unable to update vehicle type details!";
                    $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                }
            }
        }catch(\Exception $ex){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $ex->getMessage();
        }
        
        return response()->json($this->response, 200); 
    }
    
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->response['message'] = $validator->errors()->all();
            }else{
                
                $author_id = $request->input('user_id');
                $vehicleType = VehicleCategory::find($id);
                $vehicle_type = $vehicleType->name; 
                $input =[
                    'is_deleted' => 1,
                ];
                if($vehicleType->update($input)){
                    $author = Helper::getUserNames($author_id);
                    $role = Helper::getUserRoleName($author_id);
                    $action = "deleted vehicle category ".$vehicle_type."";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response['message'] = Helper::getMessage('success', $action);
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                }else{
                    $this->response['message'] ="Unable to delete vehicle category!";
                    $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                }
            }
        }catch(\Exception $ex){
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $ex->getMessage();
        }
        
        return response()->json($this->response, 200);  
    }
}
