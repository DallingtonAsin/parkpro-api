<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Client;
use App\Repositories\ClientRepository;
use Helper;
use Globals;
use Validator;

class ClientController extends Controller
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
    public function index(ClientRepository $clientRepo)
    {
        $resp = new ApiResponse();
        try {
            $clients = $clientRepo->getClients();
            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message  = Globals::$STATUS_DESC_SUCCESS;
            $resp->data = $clients;
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
            
                if($request->filled(['client_name', 'address', 'mobile_number', 'email'])){
                    
                    $client_name = $request->input('client_name');
                    $address = $request->input('address');
                    $mobile_number = $request->input('mobile_number');
                    $email = $request->input('email');
                    $count = Client::where('name', '=', $client_name)->count();
                    if($count == 0){
                        
                        $client = new Client();
                        $client->name = $client_name;
                        $client->address = $address;
                        $client->mobile_number = $mobile_number;
                        $client->email = $email;
                        if ($client->save()) {
                            $action = "registered client ".$client_name."";
                            $responseInfo = Helper::getMessage('success', $action);
                            Helper::logActivity($request, ['name' => $request->input('creator'), 'role' => 'admin', 'action' => $action]);
                            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                            $resp->message = $responseInfo; 
                        } else {
                            $messageErr = "registering client failed!";
                            $responseInfo = Helper::getMessage('error', $messageErr);
                            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                            $resp->message = $responseInfo;
                        }
                    } else {
                        $messageErr = "Client ".$client_name." has already been registered";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                    
                }else{
                    $messageErr = "Failed to get client from request";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
          
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $resp->data = Client::count();
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
        $client = Client::find($id);
        return response()->json($client, 200);
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
            'address' => 'required',
            'mobile_number' => 'required',
            'email' => 'required',
        ]);
        
        try{
            if($validator->fails()){
                $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->response['message'] = $validator->errors()->all();
            }else{
                
                $author_id = $request->input('user_id');
                $name = $request->input('name');
                $address = $request->input('address');
                $mobile_number = $request->input('mobile_number');
                $email = $request->input('email');

                $client = Client::find($id);
                $client_name = $client->name;
                
                $client->name = $name;
                $client->address = $address;
                $client->mobile_number = $mobile_number;
                $client->email = $email;

                if($client->save()){
                    $author = Helper::getUserNames($author_id);
                    $role = Helper::getUserRoleName($author_id);
                    $action = "updated client ".$client_name." details";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response['message'] = Helper::getMessage('success', $action);
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                }else{
                    $this->response['message'] ="Unable to update client details!";
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
                $client = Client::find($id);
                $client_name = $client->name; 

                $is_deleted = $client->is_deleted;
                $undo = !$is_deleted;
                $activity = $undo ? 'deleted': 'restored';
                $author = Helper::getUserNames($author_id);
                
                $client->is_deleted = $undo;
                $client->deleted_by = $author_id;

                if($client->save()){
                    $role = Helper::getUserRoleName($author_id);
                    $action = "".$activity." client ".$client_name."";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response['message'] = Helper::getMessage('success', $action);
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                }else{
                    $this->response['message'] ="Unable to delete client!";
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
