<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleCategory;
use App\Repositories\VehicleCategoryRepository;
use App\Helpers\formattedApiResponse;
use Helper;
use Globals;
use Validator;

class VehicleCategoryController extends Controller
{
    
    public function __construct(){
        
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(VehicleCategoryRepository $vehicleRepo)
    {
        try{
            $vehicle_categories = $vehicleRepo->getVehicleCategories();
            return formattedApiResponse::getJson($vehicle_categories);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
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
        ;
        
        try{
            
            if($request->has('name') && $request->filled('name')){
                $name = $request->input('name');
                $count = VehicleCategory::where('name', '=', $name)->count();
                if($count == 0){
                    
                    $vehicleCat = new VehicleCategory();
                    $vehicleCat->name = ucfirst($name);
                    if ($vehicleCat->save()) {
                        $action = "added vehicle category ".$name."";
                        $responseInfo = Helper::getMessage('success', $action);
                        Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                        return Helper::sendOkHttpResponse(['message' => $responseInfo, 'data' => $vehicleCat]);
                        
                    } else {
                        $messageErr = "adding vehicle category failed!";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        return Helper::sendFailedHttpResponse($responseInfo);
                    }
                } else {
                    $messageErr = "Vehicle category ".$name." has already been added";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    return Helper::sendFailedHttpResponse($responseInfo);
                }
                
            }else{
                $messageErr = "Failed to get vehicle category and fee from request";
                $responseInfo = Helper::getMessage('error', $messageErr);
                return Helper::sendFailedHttpResponse($responseInfo);
            }
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
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
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
            }else{
                
                $author_id = $request->input('user_id');
                $name = $request->input('name');
                
                $vehicleType = VehicleCategory::find($id);
                $vehicle_type = $vehicleType->name;
                
                $vehicleType->name = ucfirst($name);
                
                if($vehicleType->save()){
                    $author = Helper::getUserNames($author_id);
                    $role = Helper::getUserRoleName($author_id);
                    $action = "updated vehicle type ".$vehicle_type." details";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $message = Helper::getMessage('success', $action);
                    return Helper::sendOkHttpResponse(['message' => $responseInfo, 'data' => $vehicleType]);
                    
                }else{
                    $message ="Unable to update vehicle type details!";
                    return Helper::sendFailedHttpResponse($message);
                }
            }
        }catch(\Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
        
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
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
            }else{
                
                $author_id = $request->input('user_id');
                $vehicleType = VehicleCategory::find($id);
                $vehicle_type = $vehicleType->name; 
                
                $is_deleted = $vehicleType->is_deleted;
                $undo = !$is_deleted;
                $activity = $undo ? 'deleted': 'restored';
                $author = Helper::getUserNames($author_id);
                
                $vehicleType->is_deleted = $undo;
                $vehicleType->deleted_by = $author_id;
                
                if($vehicleType->save()){
                    $role = Helper::getUserRoleName($author_id);
                    $action = "".$activity." vehicle category ".$vehicle_type."";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $message = Helper::getMessage('success', $action);
                    return Helper::sendOkHttpMessage($responseInfo);
                    
                }else{
                    $message ="Unable to delete vehicle category!";
                    return Helper::sendFailedHttpResponse($message);
                }
            }
        }catch(\Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
        
    }
}
