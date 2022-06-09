<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\ParkingFee;
use App\Models\ParkingArea;
use App\Models\Client;
use App\Models\VehicleCategory;
use App\Repositories\Parking\ParkingFeeRepository;
use App\Helpers\formattedApiResponse;
use Helper;
use Globals;
use Validator;

class ParkingFeeController extends Controller
{

    protected $response ;
    
    public function __construct(ApiResponse $response){
        $this->response = $response;
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(ParkingFeeRepository $parkingFeeRepo)
    {
        $parking_fees = $parkingFeeRepo->getParkingFees();
        return formattedApiResponse::getJson($parking_fees);
    }



    public function getParkingFees(Request $request){
        ;
        try {
            $parking_area_id = $request->input('parking_area_id');
            $parking_fees = ParkingFee::where('parking_area_id', $parking_area_id)->get();
            foreach ($parking_fees as $item){
                $item->area = ParkingArea::where('id', $item->parking_area_id)->value('name');
                $item->vehicle_type = VehicleCategory::where('id', $item->vehicle_cat_id)->value('name');
            }

            $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $this->response->message  = Globals::$STATUS_DESC_SUCCESS;
            $this->response->data = $parking_fees;
        } catch (\Exception $ex) {
            $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->response->message = Globals::$STATUS_DESC_ERROR;
            $this->response->data = $ex->getMessage();
        }
        
        return response()->json($this->response);
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
            
                if($request->filled(['parking_area', 'vehicle_category', 'fee'])){
                    $parking_area_id = $request->input('parking_area');
                    $vehicle_category_id = $request->input('vehicle_category');
                    
                    $parkingAreaName = ParkingArea::where('id', $parking_area_id)->value('name');
                    $vehicleCatName = VehicleCategory::where('id', $vehicle_category_id)->value('name');
                    
                    $count = ParkingFee::where('parking_area_id', '=', $parking_area_id)
                    ->where('vehicle_cat_id', '=', $vehicle_category_id)
                    ->count();
                    
                    if($count == 0){
                        $fee = $request->input('fee');
                        $parkingFee = new ParkingFee();
                        
                        $parkingFee->parking_area_id = $parking_area_id;
                        $parkingFee->vehicle_cat_id = $vehicle_category_id;
                        $parkingFee->fee_per_hour = Helper::Numberize($fee);
                        
                        if ($parkingFee->save()) {
                            $action = "added parking fee for vehicle category ".$vehicleCatName." for parking area ".$parkingAreaName." ";
                            $responseInfo = Helper::getMessage('success', $action);
                            Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                            $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $this->response->message = $responseInfo; 
                        } else {
                            $messageErr = "Failed to add parking fee for vehicle category '.$vehicleCatName.' for parking area ".$parkingAreaName."!";
                            $responseInfo = Helper::getMessage('error', $messageErr);
                            $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                            $this->response->message = $responseInfo;
                        }
                        
                    }else{
                        $messageErr = "Fee for vehicle category ".$vehicleCatName." parking area ".$parkingAreaName." has been already added";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                        $this->response->message = $responseInfo;
                    }
                } else {
                    $messageErr = "Failed to get details from request for adding fee";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                    $this->response->message = $responseInfo;
                }
        } catch (\Exception $ex) {
            $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->response->message = $ex->getMessage();
        }
        $this->response->data = ParkingFee::count();
        return response()->json($this->response);
    }
    
    
    public function getParkingFee($parking_area_id, $vehicle_category_id){
        ;
        try{
            
            $doesRequestExist = ParkingFee::where('parking_area_id', $parking_area_id)
            ->where('vehicle_cat_id', $vehicle_category_id)
            ->exists();
            
            if($doesRequestExist){
                $parking_fee = ParkingFee::where('parking_area_id', $parking_area_id)
                ->where('vehicle_cat_id', $vehicle_category_id)
                ->value('fee_per_hour');
                $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $this->response->message  = Globals::$STATUS_DESC_SUCCESS;
                $data = array(
                    'parking_area' => Helper::getParkingAreaName($parking_area_id)->data,
                    'vehicle_type' => Helper::getVehicleTypeName($vehicle_category_id)->data,
                    'statusCode' => $this->response->statusCode,
                    'message' => $this->response->message,
                    'fee' => $parking_fee,
                );
                $this->response->data = $data;
                
            }else{
                $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                $this->response->message = "No results found";
            }
            
        }catch(Exception $ex){
            $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->response->message = $ex->getMessage();
        }
        
        return response()->json($this->response);
        
    }
    
    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $parkingFee = ParkingFee::find($id);
        return response()->json($parkingFee, 200);
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
            'fee_per_hour' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $validator->errors()->all();
            }else{
                
                $author_id = $request->input('user_id');
                $fee_per_hour = $request->input('fee_per_hour');

                $parkingFee = ParkingFee::find($id);
                $parking = ParkingArea::find($parkingFee->parking_area_id);
                $vehicleType = VehicleCategory::find($parkingFee->vehicle_cat_id);
            
                $input = [
                    'fee_per_hour' => $fee_per_hour,
                ];

                if($parkingFee->update($input)){
                    $author = Helper::getUserNames($author_id);
                    $role = Helper::getUserRoleName($author_id);
                    $action = "updated fee for parking ".$parking->name." on vehicle type ".$vehicleType->name."";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response->message = Helper::getMessage('success', $action);
                    $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                }else{
                    $this->response->message ="Unable to update parking fee!";
                    $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                }
            }
        }catch(\Exception $ex){
            $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->response->message = $ex->getMessage();
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
                $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
                $this->response->message = $validator->errors()->all();
            }else{
                
                $author_id = $request->input('user_id');
                $parkingFee = ParkingFee::find($id);
                $parking = ParkingArea::find($parkingFee->parking_area_id);
                $vehicleType = VehicleCategory::find($parkingFee->vehicle_cat_id);

                $is_deleted = $parkingFee->is_deleted;
                $undo = !$is_deleted;
                $activity = $undo ? 'deleted': 'restored';
                $author = Helper::getUserNames($author_id);
                
                $parkingFee->is_deleted = $undo;
                $parkingFee->deleted_by = $author_id;

                if($parkingFee->save()){
                    $role = Helper::getUserRoleName($author_id);
                    $action = "".$activity." fee for parking ".$parking->name." on vehicle type ".$vehicleType->name."";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response->message = Helper::getMessage('success', $action);
                    $this->response->statusCode = Globals::$STATUS_CODE_SUCCESS;
                }else{
                    $this->response->message ="Unable to delete parking fee!";
                    $this->response->statusCode = Globals::$STATUS_CODE_FAILED;
                }
            }
        }catch(\Exception $ex){
            $this->response->statusCode = Globals::$STATUS_CODE_ERROR;
            $this->response->message = $ex->getMessage();
        }
        
        return response()->json($this->response, 200); 
    }
}
