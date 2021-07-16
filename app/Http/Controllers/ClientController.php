<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use App\Models\Client;
use Helper;

class ClientController extends Controller
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
            $clients = Client::all();
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
            
            if($request->has('client_name') && $request->filled('client_name')){
                $client_name = $request->input('client_name');
                $mobile_number = $request->input('mobile_number');
                $email = $request->input('email');
                $count = Client::where('name', '=', $client_name)->count();
                if($count == 0){
                    
                    $client = new Client();
                    $client->name = $client_name;
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
        $resp = new ApiResponse();

        try {

            if($id){

                $client = Client::find($id);
                $name = $client->name;

                $client_name = $request->input('client_name');
                $mobile_number = $request->input('mobile_number');
                $email = $request->input('email');

                $client->name = $client_name;
                $client->mobile_number = $mobile_number;
                $client->email = $email;

                if ($client->save()) {
                    $action = "updated details of client ".$name."";
                    $responseInfo = Helper::getMessage('success', $action);
                    Helper::logActivity($request, ['name' => $request->user()->name, 'role' => 'admin', 'action' => $action]);
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message = $responseInfo ;
                } else {
                    $messageErr = "Client update failed!";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            }else{
                $messageErr = "Unable to get client id from the request";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = "ERROR Here ".$ex->getMessage();
        }
        $resp->data = Client::count();
        return response()->json($resp);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $resp = new ApiResponse();

        try {

            if($id){

            $client = Client::find($id);
            $name = $client->name;

            if ($client->delete()) {
                $action = "removed client ".$name." from the system";
                Helper::logActivity($request, ['name' => 'Dallington', 'role' => 'admin', 'action' => $action]);
                $responseInfo = Helper::getMessage('success', $action);

                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message = $responseInfo ;
            } else {
                $messageErr = "Client not removed!";
                $responseInfo = Helper::getMessage('error', $messageErr);

                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        }else{
               $messageErr = "Unable to get client id from the request";
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
}
