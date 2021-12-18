<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ParkingRequest;
use App\Models\ParkingFee;
use App\Models\ParkingArea;
use App\Models\VehicleCategory;
use App\Helpers\ApiResponse;
use Carbon\Carbon;
use Helper;
use TokenAuth;
use Globals;


class ParkingRequestController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        
        $resp = new ApiResponse();
        try {
            $authToken   =   $request->header('AuthToken');
            if (!empty($authToken) && TokenAuth::validate($authToken)) {
                $parking_requests = ParkingRequest::all();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $parking_requests;
                
            }else{
                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                $resp->message = "Unauthorized access";
                $resp->data = "Unauthorized access";
                
            }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = $ex->getMessage();
        }
        
        return response()->json($resp);
    }
    
    public function getTransactionHistory(Request $request)
    {
        
        $resp = new ApiResponse();
        try {
            // $authToken   =   $request->header('AuthToken');
            // if (!empty($authToken) && TokenAuth::validate($authToken)) {
                
                if($request->has('telephone_no') && $request->filled('telephone_no')){
                    $telephone_no = $request->input('telephone_no');
                    $transactions = ParkingRequest::where('telephone_no', '=', $telephone_no)
                    ->where('status', '=', 'APPROVED')
                    ->orderBy('approval_date', 'desc')
                    ->wherenotNull('approval_date')->get();
                }else{
                    $transactions = ParkingRequest::where('status', '=', 'APPROVED')
                    ->orderBy('approval_date', 'desc')
                    ->wherenotNull('approval_date')->get();
                }
                if(count($transactions->toArray()) > 0) {
                    foreach($transactions as $transaction){
                        $transaction->approval_date = date('Y-m-d', strtotime($transaction->approval_date));
                        $transaction->description = 'Payment';
                        $transaction->amount = number_format($transaction->amount);
                    }
                }
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $transactions;
                
                // }else{
                    //     $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    //     $resp->message = "Unauthorized access";
                    //     $resp->data = "Unauthorized access";
                    
                    // }
                    
                } catch (\Exception $ex) {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = $ex->getMessage();
                    $resp->data = $ex->getMessage();
                }
                
                return response()->json($resp);
            }
            
            
            
            public function pendingRequests(Request $request)
            {
                $resp = new ApiResponse();
                try {
                    $parking_requests = ParkingRequest::where('status', '=', 'PENDING')
                    ->orderBy('request_date', 'desc')
                    ->get();
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                    $resp->data = $parking_requests;
                } catch (\Exception $ex) {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = $ex->getMessage();
                    $resp->data = $ex->getMessage();
                }
                
                return response()->json($resp);
            }
            
            
            public function approvedRequests(Request $request)
            {
                
                $resp = new ApiResponse();
                try {
                    $approved_parking_requests = ParkingRequest::where('status', '=', 'APPROVED')
                    ->orderBy('approval_date', 'desc')
                    ->wherenotNull('approval_date')->get();
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                    $resp->data = $approved_parking_requests;
                } catch (\Exception $ex) {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = $ex->getMessage();
                    $resp->data = $ex->getMessage();
                }
                
                return response()->json($resp);
            }
            
            
            public function rejectedRequests(Request $request)
            {
                
                $resp = new ApiResponse();
                try {
                    $approved_parking_requests = ParkingRequest::where('status', '=', 'REJECTED')
                    ->orderBy('reject_date', 'desc')
                    ->wherenotNull('reject_date')->get();
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                    $resp->data = $approved_parking_requests;
                } catch (\Exception $ex) {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = $ex->getMessage();
                    $resp->data = $ex->getMessage();
                }
                
                return response()->json($resp);
            }
            
            
            public function approveRequest(Request $request)
            {
                
                $resp = new ApiResponse();
                try {
                    $authToken   =   $request->header('AuthToken');
                    if (!empty($authToken) && TokenAuth::validate($authToken)) {
                        if($request->filled(['request_id', 'telephone_no', 'vehicle_number'])){
                            
                            $request_id = $request->input('request_id');
                            $telephone_no = $request->input('telephone_no'); 
                            $vehicle_number = $request->input('vehicle_number');
                            
                            $doesRequestExists = ParkingRequest::where('id', '=', $request_id)
                            ->where('telephone_no', '=', $telephone_no)
                            ->where('vehicle_number', '=', $vehicle_number)->exists();
                            
                            if($doesRequestExists){
                                
                                $order_no = Str::random(12);
                                $status = Globals::$APPROVED_STATUS;
                                $approval_date = Carbon::now()->toDateTimeString();
                                
                                $isApproved = ParkingRequest::where('id', '=', $request_id)
                                ->where('telephone_no', '=', $telephone_no)
                                ->where('vehicle_number', '=', $vehicle_number)
                                ->update(['order_no'=> $order_no, 'status' => $status, 'approval_date' => $approval_date]);
                                if($isApproved){
                                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                                    $data = array(
                                        'request_id' => $request_id,
                                        'order_no' => $order_no,
                                        'telephone_no' => $telephone_no,
                                        'status' => $status,
                                        'approval_date' => $approval_date,
                                        'statusCode' => $resp->statusCode,
                                        'message' => $resp->message,
                                    );
                                    $resp->data = $data;
                                }else{
                                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                    $resp->message = "Unable to approve request";
                                }
                            }else{
                                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                                $resp->message = "Request doesn't exist";
                            }
                        }else{
                            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                            $resp->message = "Unable to process request: missing parameters";
                        }
                        
                    }else{
                        $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                        $resp->message = "Unauthorized access";
                    }
                    
                } catch (\Exception $ex) {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = $ex->getMessage();
                }
                return response()->json($resp);
            }
            
            
            public function rejectRequest(Request $request)
            {
                
                $resp = new ApiResponse();
                try {
                    $authToken   =   $request->header('AuthToken');
                    if (!empty($authToken) && TokenAuth::validate($authToken)) {
                        if($request->filled(['request_id', 'telephone_no', 'vehicle_number'])){
                            
                            $request_id = $request->input('request_id');
                            $telephone_no = $request->input('telephone_no'); 
                            $vehicle_number = $request->input('vehicle_number');
                            
                            $doesRequestExists = ParkingRequest::where('id', '=', $request_id)
                            ->where('telephone_no', '=', $telephone_no)
                            ->where('vehicle_number', '=', $vehicle_number)->exists();
                            
                            if($doesRequestExists){
                                
                                $order_no = Str::random(12);
                                $status = Globals::$REJECTED_STATUS;
                                $reject_date = Carbon::now()->toDateTimeString();
                                
                                $isRejected = ParkingRequest::where('id', '=', $request_id)
                                ->where('telephone_no', '=', $telephone_no)
                                ->where('vehicle_number', '=', $vehicle_number)
                                ->update(['order_no'=> null, 'status' => $status, 'approval_date' => null, 'reject_date' => $reject_date]);
                                if($isRejected){
                                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                                    $data = array(
                                        'request_id' => $request_id,
                                        'telephone_no' => $telephone_no,
                                        'status' => $status,
                                        'reject_date' => $reject_date,
                                        'statusCode' => $resp->statusCode,
                                        'message' => $resp->message,
                                    );
                                    $resp->data = $data;
                                }else{
                                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                    $resp->message = "Unable to reject request";
                                }
                            }else{
                                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                                $resp->message = "Request doesn't exist";
                            }
                        }else{
                            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                            $resp->message = "Unable to process request: missing parameters";
                        }
                        
                    }else{
                        $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                        $resp->message = "Unauthorized access";
                    }
                    
                } catch (\Exception $ex) {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = $ex->getMessage();
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

            private function differenceInHours($startdate, $enddate){
                $starttimestamp = strtotime($startdate);
                $endtimestamp = strtotime($enddate);
                $difference = abs($endtimestamp - $starttimestamp)/3600;
                return $difference;
            }

            private function generateOrderNo(){
                if(ParkingRequest::count() > 0){
                    $latestRequest = ParkingRequest::orderBy('created_at','DESC')->first();
                    $id = $latestRequest->id;
                }else{
                    $id = 0;
                }
                $orderNo= '#'.str_pad($id + 1, 8, "0", STR_PAD_LEFT);
                return $orderNo;
            }

            private function  isParkingAreaOpen($id){
                $isParkingOpen = false;
                try{
                    if(ParkingArea::where('id', $id)->exists()){
                        $parking = ParkingArea::find($id);
                        if(date('H') < date('H', strtotime($parking->closes_at))){ 
                            $isParkingOpen = true;
                        } 
                    }
                    
                }catch(Exception $e){
                    throw $e;
                }
                return $isParkingOpen;
            }

            private function isParkingAreaFree($id){
                $isSpaceAvailable = false;
                try{
                    if(ParkingArea::where('id', $id)->exists()){
                        $parking = ParkingArea::find($id);
                        $free_spots = $parking->current_free_space;
                        if($free_spots >= 1){
                            $isSpaceAvailable = true;
                        }
                    }
                    
                }catch(Exception $e){
                    throw $e;
                }
                return $isSpaceAvailable;
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
                    
                    
                    $authToken   =   $request->header('AuthToken');
                    if (!empty($authToken) && TokenAuth::validate($authToken)) {
                        if($request->filled(['parking_area_id', 'telephone_no', 'vehicle_details', 'vehicle_category',
                                             'start_time', 'end_time'])){
                            
                                $parking_area_id = $request->input('parking_area_id'); 
                                $telephone_no = $request->input('telephone_no'); 
                                $vehicle_details = $request->input('vehicle_details');
                                $vehicle_category = $request->input('vehicle_category');
                                $start_time = $request->input('start_time');
                                $end_time = $request->input('end_time');

                                if($this->isParkingAreaOpen($parking_area_id) === true){

                                if($this->isParkingAreaFree($parking_area_id) === true){

                                      if(VehicleCategory::where('name', 'like', '%'.$vehicle_category.'%')->exists()){
                                        $vehicle_cat_id = VehicleCategory::where('name', 'like', '%'.$vehicle_category.'%')->value('id');
                                        $parking_hours = $this->differenceInHours($start_time, $end_time);
                                        $fee_per_hour = ParkingFee::where('parking_area_id', $parking_area_id)
                                        ->where('vehicle_cat_id', $vehicle_cat_id)->value('fee_per_hour');
                                        $amount = floatval($fee_per_hour*$parking_hours);
            
                                        $request_date = Carbon::now()->toDateTimeString();
                                        $status = Globals::$APPROVED_STATUS;
                                   
                                        $fee = $this->getParkingFee($parking_area_id, $vehicle_cat_id);
                                      
                                        $amount = floatval($parking_hours)*floatval($fee);
                                        $orderNo = $this->generateOrderNo();
                                        $parkingRequest = new ParkingRequest();
        
                                        $parkingRequest->order_no = $orderNo;
                                        $parkingRequest->parking_area_id = $parking_area_id;
                                        $parkingRequest->telephone_no = $telephone_no;
                                        $parkingRequest->vehicle_details = $vehicle_details;
                                        $parkingRequest->vehicle_cat_id = $vehicle_cat_id;
                                        $parkingRequest->start_time = $start_time;
                                        $parkingRequest->end_time = $end_time;
                                        $parkingRequest->parking_hours = $parking_hours;
                                        $parkingRequest->amount = $amount;
                                        $parkingRequest->status = $status;
                                        $parkingRequest->request_date = $request_date;
                                        $parkingRequest->approval_date = Carbon::now()->toDateTimeString();

                                        if($parkingRequest->save()){
                                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                            $resp->message  = "Your request has been submitted and approved successfully";
                                            $data = array(
                                                'order_no' => $orderNo,
                                                'telephone_no' => $telephone_no,
                                                'status' => $status,
                                                'request_date' => $request_date,
                                                'statusCode' => $resp->statusCode,
                                                'message' => $resp->message,
                                            );
                                            $resp->data = $data;
                                        }else{
                                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                                            $resp->message = "Unable to submit request";
                                        }
                                    }else {
                                        $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                                        $resp->message = "Unable to get supplied vehicle type";
                                    }   
                                   
                                }else {
                                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                                    $resp->message = "Parking area is currently fully occupied";
                                }
                            }else {
                                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                                $resp->message = "Parking area is currently closed";
                            }
                              
                        }else{
                            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                            $resp->message = "Unable to process request: missing parameters";
                        }
                        
                    }else{
                        $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                        $resp->message = "Unauthorized access";
                    }
                    
                } catch (\Exception $ex) {
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = $ex->getMessage();
                }
                return response()->json($resp);
            }
            
            
            
            private function getParkingFee($parking_area_id, $vehicle_category_id){
                try{
                        $parking_fee = ParkingFee::where('parking_area_id', $parking_area_id)
                        ->where('vehicle_cat_id', $vehicle_category_id)
                        ->value('fee_per_hour');
                        return $parking_fee;

                }catch(Exception $ex){
                    throw $x;
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
