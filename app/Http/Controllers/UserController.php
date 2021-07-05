<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use Helper;
use TokenAuth;

class UserController extends Controller
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
            $parking_requests = User::all();
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
        //
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
