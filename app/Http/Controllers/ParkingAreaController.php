<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ParkingArea;
use App\Models\Client;
use App\Repositories\Parking\ParkingAreaRepository;
use App\Helpers\formattedApiResponse;
use Helper;
use Globals;
use Validator;


class ParkingAreaController extends Controller
{
    
    public function __construct()
    {
        
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(ParkingAreaRepository $parkingAreaRepo)
    {
        $parking_areas = $parkingAreaRepo->getParkingAreas();
        return formattedApiResponse::getJson($parking_areas);
    }
    
    public function findParking(ParkingAreaRepository $parkingAreaRepo, Request $request)
    {
        
        try{
            if($request->filled(['id'])){
                $id = $request->input('id');
                $data = $parkingAreaRepo->searchParking($id);
                return Helper::sendOkHttpResponse($data);
            }else{
                $messageErr = "Unable to process request:missing parameters";
                $responseInfo = Helper::getMessage('error', $messageErr);
                return Helper::sendFailedHttpResponse($responseInfo);
            } 
            
        }catch (\Exception $ex) {
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
    }
    
    
    
    public function getParkingSpots(Request $request)
    {
        
        try {
            if($request->filled(['client_id'])){
                $client_id = $request->input('client_id');
                $parking_areas = ParkingArea::where('client_id',  '=', $client_id)->get();
                return Helper::sendOkHttpResponse($parking_areas);
            }else{
                $messageErr = "Unable to process request:missing parameters";
                $responseInfo = Helper::getMessage('error', $messageErr);
                return Helper::sendFailedHttpResponse($responseInfo);
                
            }
        } catch (\Exception $ex) {
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
    }
    
    public function searchParkingArea(Request $request)
    {
        
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
                return Helper::sendOkHttpResponse($searchResults);
            }else{
                $messageErr = "Unable to process request:missing parameters";
                $responseInfo = Helper::getMessage('error', $messageErr);
                return Helper::sendFailedHttpResponse($responseInfo);
                
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
    
    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        
        try{
            
            if($request->filled(['client_id', 'name', 'phone_number', 'address',
            'description', 'opens_at', 'closes_at', 'latitude',
            'longitude', 'total_space'])){
                
                $client_id = $request->input('client_id');
                $name = $request->input('name');
                $phone_number = $request->input('phone_number');
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
                        
                        if($request->hasFile('photo')){
                            $file = $request->file('photo');
                            $file_name = $file->getClientOriginalName();
                            $file_extension = $file->extension();
                            $fileName = time().'.'.$file_extension;
                            $filePath = $file->storeAs('images/parking-areas', $fileName, 'public');
                            $photo = $filePath;
                        }else{
                            $photo = null;
                        }
                        
                        $parkingArea->client_id = $client_id;
                        $parkingArea->name = $name;
                        $parkingArea->phone_number = $phone_number;
                        $parkingArea->address = $address;
                        $parkingArea->description = $description;
                        $parkingArea->opens_at = date('H:i:s', strtotime($opens_at));
                        $parkingArea->closes_at = date('H:i:s', strtotime($closes_at));
                        $parkingArea->latitude = floatval($latitude);
                        $parkingArea->longitude = floatval($longitude);
                        $parkingArea->total_space = $total_space;
                        $parkingArea->current_free_space = $total_space;
                        $parkingArea->photo = $photo;
                        
                        
                        if ($parkingArea->save()) {
                            $action = "added parking area ".$name." for client ".$client_name."";
                            $responseInfo = Helper::getMessage('success', $action);
                            Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                            $data = ParkingArea::count();
                            return Helper::sendOkHttpResponse($data);
                        } else {
                            $messageErr = "Adding parking area ".$name." for client '.$client_name.' failed!";
                            $responseInfo = Helper::getMessage('error', $messageErr);
                            return Helper::sendFailedHttpResponse($responseInfo);
                        }
                        
                    }else{
                        $messageErr = "Parking area ".$name." for client ".$client_name." has been already added";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        return Helper::sendFailedHttpResponse($responseInfo);
                    }
                }else{
                    $messageErr = "Details of the client not found";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    return Helper::sendFailedHttpResponse($responseInfo);
                }
            } else {
                $messageErr = "Unable to process request:missing parameters";
                $responseInfo = Helper::getMessage('error', $messageErr);
                return Helper::sendFailedHttpResponse($responseInfo);
            }
        } catch (\Exception $ex) {
            return Helper::sendFailedHttpResponse($ex->getMessage());
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
        $parkingArea = ParkingArea::find($id);
        return response()->json($parkingArea);
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
            'phone_number' => 'required',
            'address' => 'required',
            'description' => 'required',
            'opens_at' => 'required',
            'closes_at' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'slots' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
            }else{
                
                $author_id = $request->input('user_id');
                $name = $request->input('name');
                $phone_number = $request->input('phone_number');
                $address = $request->input('address');
                $description = $request->input('description');
                $opens_at = $request->input('opens_at');
                $closes_at = $request->input('closes_at');
                $latitude = $request->input('latitude');
                $longitude = $request->input('longitude');
                $slots = $request->input('slots');
                
                $parking = ParkingArea::find($id);
                $parking_name = $parking->name;
                if($request->hasFile('photo')){
                    $file = $request->file('photo');
                    $file_extension = $file->extension();
                    if(!empty($parking->photo)){
                        Storage::disk('public')->delete($parking->photo);
                    }
                    $fileName = $userId.''.time().'.'.$file_extension;
                    $filePath = $file->storeAs('images/parking-areas', $fileName, 'public');
                    $photo = $filePath;
                }else{
                    $photo  = $parking->photo;
                }
                
                $parking->name = $name;
                $parking->phone_number = $phone_number;
                $parking->address = $address;
                $parking->description = $description;
                $parking->opens_at = $opens_at;
                $parking->closes_at = $closes_at;
                $parking->latitude = $latitude;
                $parking->longitude = $longitude;
                $parking->total_space = $slots;
                $parking->photo = $photo;
                
                
                if($parking->save()){
                    
                    $author = Helper::getUserNames($author_id);
                    $role = Helper::getUserRoleName($author_id);
                    $action = "updated parking ".$parking_name." details";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $message = Helper::getMessage('success', $action);
                    return Helper::sendOkHttpMessage($message);
                    
                }else{
                    $responseInfo ="Unable to update parking area details!";
                    return Helper::sendFailedHttpResponse($responseInfo);
                }
            }
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
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
                $parking = ParkingArea::find($id);
                $parking_name = $parking->name; 
                
                $is_deleted = $parking->is_deleted;
                $undo = !$is_deleted;
                $activity = $undo ? 'deleted': 'restored';
                $author = Helper::getUserNames($author_id);
                
                $parking->is_deleted = $undo;
                $parking->deleted_by = $author_id;
                
                if($parking->save()){
                    
                    $role = Helper::getUserRoleName($author_id);
                    $action = "".$activity." parking ".$parking_name."";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $message = Helper::getMessage('success', $action);
                    return Helper::sendOkHttpMessage($message);
                    
                }else{
                    $message ="Unable to delete parking!";
                    return Helper::sendFailedHttpResponse($message);
                }
            }
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
    }
    
    
    public function getNearByParkings(Request $request, ParkingAreaRepository $parkingAreaRepo){
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
            }else{
                $latitude = floatval($request->input("latitude"));
                $longitude = floatval($request->input("longitude"));
                $nearByParkings = $parkingAreaRepo->fetchNearByParkingAreas($latitude, $longitude);
                return Helper::sendOkHttpResponse($nearByParkings);
            }
            
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
    }
    
    
    public function getTopRatedParkingAreas(Request $request, ParkingAreaRepository $parkingAreaRepo){
        try{
            $topRatedParkings = $parkingAreaRepo->fetchTopRatedParkingAreas();
            return Helper::sendOkHttpResponse($topRatedParkings);
            
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
    }
    
    
    
}
