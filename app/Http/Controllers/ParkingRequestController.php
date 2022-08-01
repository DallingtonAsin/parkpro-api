<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ParkingRequest;
use App\Models\ParkingFee;
use App\Models\ParkingArea;
use App\Models\VehicleCategory;
use App\Models\Customer;
use App\Helpers\formattedApiResponse;
use Illuminate\Support\Facades\DB;
use App\Repositories\Parking\ParkingRequestRepository;
use App\Repositories\Parking\ParkingAreaRepository;
use Carbon\Carbon;
use Globals;
use Helper;


class ParkingRequestController extends Controller
{
    
    
    protected $response;
    
    
    public function __construct(){
        
    }
    
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(ParkingRequestRepository $parkingReqRepo)
    {
        try{
            $parking_requests = $parkingReqRepo->getAll();
            return formattedApiResponse::getJson($parking_requests);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }  
        
    }
    
    // pending parking requests
    public function pendingRequests(ParkingRequestRepository $parkingReqRepo)
    {
        try{
            $pending_requests = $parkingReqRepo->getPendingRequests();
            return formattedApiResponse::getJson($pending_requests);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        } 
        
    }
    
    // approved parking requests
    public function approvedRequests(ParkingRequestRepository $parkingReqRepo)
    {
        try{
            $approved_parking_requests = $parkingReqRepo->getApprovedRequests();
            return formattedApiResponse::getJson($approved_parking_requests);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
    }
    
    // rejected parking requests
    public function rejectedRequests(ParkingRequestRepository $parkingReqRepo){
        try{
            $rejected_requests = $parkingReqRepo->getRejectedRequests();
            return formattedApiResponse::getJson($rejected_requests);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
    }
    
    
    
    public function getTransactionHistory(Request $request)
    {
        
        ;
        try {  
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
            return Helper::sendOkHttpResponse($transactions);
            
        } catch (\Exception $ex) {
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
    }
    
    
    public function approveRequest(Request $request)
    {
        
        ;
        try {
            
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
                        
                        $data = array(
                            'request_id' => $request_id,
                            'order_no' => $order_no,
                            'telephone_no' => $telephone_no,
                            'status' => $status,
                            'approval_date' => $approval_date
                        );
                        return Helper::sendOkHttpResponse(['message' => $message, 'data' => $data]);
                        
                    }else{
                        $message = "Unable to approve request";
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                }else{
                    $message = "Request doesn't exist";
                    return Helper::sendFailedHttpResponse($message);
                }
            }else{
                $message = "Unable to process request: missing parameters";
                return Helper::sendFailedHttpResponse($message);
                
            }
            
        } catch (\Exception $ex) {
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
    }
    
    
    public function rejectRequest(Request $request)
    {
        
        ;
        try {
            
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
                        
                        $data = array(
                            'request_id' => $request_id,
                            'telephone_no' => $telephone_no,
                            'status' => $status,
                            'reject_date' => $reject_date
                        );
                        return Helper::sendOkHttpResponse(['message' => $message , 'data' => $data]);
                    }else{
                        $message = "Unable to reject request";
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                }else{
                    $message = "Request doesn't exist";
                    return Helper::sendFailedHttpResponse($message);
                }
            }else{
                $message = "Unable to process request: missing parameters";
                return Helper::sendFailedHttpResponse($message);
                
            }
            
        } catch (\Exception $ex) {
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
    
    private function differenceInHours($startdate, $enddate){
        $starttimestamp = strtotime($startdate);
        $endtimestamp = strtotime($enddate);
        $difference = abs($endtimestamp - $starttimestamp)/3600;
        return $difference;
    }
    
    private function generateOrderNo(){
        try{
            
            $query = DB::select("SELECT GenerateParkingRequestOrderNo() as orderNo");
            $order_no =  $query[0]->orderNo;
            return $order_no;
            
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    private function isParkingAreaOpen($id){
        try{
            
            $parkingAreaRepo = new ParkingAreaRepository();
            
            if(ParkingArea::where('id', $id)->exists()){
                $parking = ParkingArea::find($id);
                $isParkingOpen = $parkingAreaRepo->isParkingAreaOpen($parking->opens_at, $parking->closes_at);
                return $isParkingOpen;
            }else{
                return false;
            }
        }catch(Exception $e){
            throw $e;
        }
        
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
    
    private function convertTime($dec){
        $seconds = ($dec * 3600);
        $hours = floor($dec);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;
        $hrLabel = $hours > 1 ? 'hrs' : 'hr';
        return $this->lz($hours)." ".$hrLabel." ".$this->lz($minutes)." min"; // .$this->lz($seconds);
    }
    
    private function lz($num){
        return (strlen($num) < 2) ? "{$num}" : $num;
    }
    
    public function getRequestOrderInfo(Request $request) {
        try{
            
            if($request->filled(['order_no' , 'customer_id'])){
                $order_no = $request->input('order_no');
                $customer_id = $request->input('customer_id');
                $order = ParkingRequest::where('order_no', $order_no)->where('customer_id', $customer_id)->get();
                if(count((array)$order) > 0){
                    foreach($order as $info){
                        $start_time = date('h:i A', strtotime($info->start_time));
                        $end_time = date('h:i A', strtotime($info->end_time));
                        $info->booking_period = $start_time." - ".$end_time;
                        $customer = Customer::find($info->customer_id);
                        $info->name = $customer->first_name." ".$customer->last_name;
                        $info->parking_area = ParkingArea::where('id', $info->parking_area_id)->value('name');
                        $info->car_type = VehicleCategory::where('id', $info->vehicle_cat_id)->value('name');
                        $info->parking_hours = $this->convertTime($info->parking_hours);
                        $info->fee_per_hour = number_format(ParkingFee::where('parking_area_id', $info->parking_area_id)->where('vehicle_cat_id', $info->vehicle_cat_id)->value('fee_per_hour'));
                        $info->approval_date = date('Y-m-d H:i A', strtotime($info->approval_date));
                        $info->amount = number_format($info->amount);
                    }
                }else{
                    $order = [];
                }
                return Helper::sendOkHttpResponse($order);
                
            }else{
                $message = "Unable to process request, missing parameters!";
                return Helper::sendFailedHttpResponse($message);
                
            }
        }catch(Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
    }
    
    
    public function getMyParkingRequests(Request $request) {
        try{
            
            if($request->filled('id')){
                $id = $request->input('id');
                $requests = ParkingRequest::where('customer_id', $id)->orderBy('id', 'desc')->get();
                if(count((array)$requests) > 0){
                    foreach($requests as $request){
                        $request->approval_date = date('Y-m-d H:i A', strtotime($request->approval_date));
                        $request->amount = number_format($request->amount);
                        $parking = ParkingArea::find($request->parking_area_id);
                        $request->parking_area = $parking->name;
                    }
                }else{
                    $requests = [];
                }
                return Helper::sendOkHttpResponse($requests);
                
            }else{
                $message = "Unable to process request: missing parameters";
                return Helper::sendFailedHttpResponse($message);
                
            }
        }catch(Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
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
        try {
            
            if($request->filled(['customer_id', 'parking_area_id', 
            'telephone_no', 'vehicle_details', 'vehicle_category',
            'start_time', 'end_time'])){
                
                $customer_id = $request->input('customer_id'); 
                $parking_area_id = $request->input('parking_area_id'); 
                $telephone_no = $request->input('telephone_no'); 
                $vehicle_details = $request->input('vehicle_details');
                $vehicle_category = $request->input('vehicle_category');
                $start_time = $request->input('start_time');
                $end_time = $request->input('end_time');
                
                if($this->isParkingAreaOpen($parking_area_id) == true){
                    
                    if($this->isParkingAreaFree($parking_area_id) == true){
                        
                        if(VehicleCategory::where('name', 'like', '%'.$vehicle_category.'%')->exists()){
                            $vehicle_cat_id = VehicleCategory::where('name', 'like', '%'.$vehicle_category.'%')->value('id');
                            $parking_hours = round($this->differenceInHours($start_time, $end_time), 1);
                            $fee_per_hour = ParkingFee::where('parking_area_id', $parking_area_id)
                            ->where('vehicle_cat_id', $vehicle_cat_id)->value('fee_per_hour');
                            // $amount = floatval($fee_per_hour*$parking_hours);
                            
                            $request_date = Carbon::now()->toDateTimeString();
                            $status = Globals::$APPROVED_STATUS;
                            
                            $fee = $this->getParkingFee($parking_area_id, $vehicle_cat_id);
                            
                            $amount = floatval($parking_hours)*floatval($fee);
                            $amount = round($amount);
                            $orderNo = $this->generateOrderNo();
                            $parkingRequest = new ParkingRequest();
                            
                            $parkingRequest->order_no = $orderNo;
                            $parkingRequest->customer_id = $customer_id;
                            $parkingRequest->telephone_no = $telephone_no;
                            $parkingRequest->parking_area_id = $parking_area_id;
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
                                
                                $message  = "Your request has been submitted and approved successfully.";
                                $customerData = Helper::getCustomerData($customer_id);
                                $data = array(
                                    'customer_id' => $customer_id,
                                    'order_no' => $orderNo,
                                    'telephone_no' => $telephone_no,
                                    'status' => $status,
                                    'request_date' => $request_date
                                );
                                return Helper::sendOkHttpResponse(["message" => $message, "data" => $customerData]);
                                
                            }else{
                                $message = "Unable to submit request";
                                return Helper::sendFailedHttpResponse($message);
                                
                                
                            }
                        }else {
                            $message = "Unable to get supplied vehicle type";
                            return Helper::sendFailedHttpResponse($message);
                            
                        }   
                        
                    }else {
                        $message = "Parking area is currently fully occupied";
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                }else {
                    $message = "Parking area is currently closed";
                    return Helper::sendFailedHttpResponse($message);
                    
                }
                
            }else{
                $message = "Unable to process request: missing parameters";
                return Helper::sendFailedHttpResponse($message);
                
            }
            
            
            
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
            
        }
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
