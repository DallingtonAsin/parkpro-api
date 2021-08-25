<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ParkingRequest;
use App\Models\ParkingFee;
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
                                
                                $ticket_no = Str::random(12);
                                $status = Globals::$APPROVED_STATUS;
                                $approval_date = Carbon::now()->toDateTimeString();
                                
                                $isApproved = ParkingRequest::where('id', '=', $request_id)
                                ->where('telephone_no', '=', $telephone_no)
                                ->where('vehicle_number', '=', $vehicle_number)
                                ->update(['ticket_no'=> $ticket_no, 'status' => $status, 'approval_date' => $approval_date]);
                                if($isApproved){
                                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                    $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                                    $data = array(
                                        'request_id' => $request_id,
                                        'ticket_no' => $ticket_no,
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
                                
                                $ticket_no = Str::random(12);
                                $status = Globals::$REJECTED_STATUS;
                                $reject_date = Carbon::now()->toDateTimeString();
                                
                                $isRejected = ParkingRequest::where('id', '=', $request_id)
                                ->where('telephone_no', '=', $telephone_no)
                                ->where('vehicle_number', '=', $vehicle_number)
                                ->update(['ticket_no'=> null, 'status' => $status, 'approval_date' => null, 'reject_date' => $reject_date]);
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
                        if($request->filled(['telephone_no', 'vehicle_number', 'vehicle_type_id', 'client_id',
                        'parking_area_id', 'start_time', 'end_time',
                        'parking_hours'])){
                            
                            $telephone_no = $request->input('telephone_no'); 
                            $vehicle_number = $request->input('vehicle_number');
                            $vehicle_type_id = $request->input('vehicle_type_id');
                            $client_id = $request->input('client_id');
                            $parking_area_id = $request->input('parking_area_id'); 
                            $start_time = $request->input('start_time');
                            $end_time = $request->input('end_time');
                            $parking_hours = $request->input('parking_hours');
                            $request_date = Carbon::now()->toDateTimeString();
                            $status = Globals::$PENDING_STATUS;
                            
                            $result = $this->getParkingFee($client_id, $parking_area_id, $vehicle_type_id);
                            $data = $result->data;
                            if(count($data) > 0){
                                
                                $fee = $data['fee'];
                                $amount = floatval($parking_hours)*floatval($fee);
                                
                                $parkingRequest = new ParkingRequest();
                                $parkingRequest->telephone_no = $telephone_no;
                                $parkingRequest->vehicle_number = $vehicle_number;
                                $parkingRequest->vehicle_type_id = $vehicle_type_id;
                                $parkingRequest->client_id = $client_id;
                                $parkingRequest->parking_area_id = $parking_area_id;
                                $parkingRequest->start_time = $start_time;
                                $parkingRequest->end_time = $end_time;
                                $parkingRequest->parking_hours = $parking_hours;
                                $parkingRequest->amount = $amount;
                                $parkingRequest->status = $status;
                                $parkingRequest->request_date = $request_date;
                                if($parkingRequest->save()){
                                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                                    $resp->message  = "Request has been submitted successfully, wait shortly for notification of request approval.";
                                    $data = array(
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
                                
                            }else{
                                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                                $resp->message = "Unable to get parking fee";
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
            
            
            
            public function getParkingFee($client_id, $parking_area_id, $vehicle_category_id){
                $resp = new ApiResponse();
                try{
                    
                    $doesRequestExist = ParkingFee::where('client_id', $client_id)
                    ->where('parking_area_id', $parking_area_id)
                    ->where('vehicle_cat_id', $vehicle_category_id)
                    ->exists();
                    
                    if($doesRequestExist){
                        $parking_fee = ParkingFee::where('client_id', $client_id)
                        ->where('parking_area_id', $parking_area_id)
                        ->where('vehicle_cat_id', $vehicle_category_id)
                        ->value('fee');
                        $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                        $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                        $data = array(
                            'client' => Helper::getClientName($client_id)->data,
                            'parking_area' => Helper::getParkingAreaName($parking_area_id)->data,
                            'vehicle_type' => Helper::getVehicleTypeName($vehicle_category_id)->data,
                            'statusCode' => $resp->statusCode,
                            'message' => $resp->message,
                            'fee' => $parking_fee,
                        );
                        $resp->data = $data;
                        
                    }else{
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = "No results found";
                    }
                    
                }catch(Exception $ex){
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = $ex->getMessage();
                }
                
                return $resp;
                
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
