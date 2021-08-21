<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Helper;
use TokenAuth;
use Globals;
use LaramanBeyonic;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $resp = new ApiResponse();
        $paymentData = array(
            'phonenumber' => '+256774014727',
            'amount'      => '1000',
            'currency'    => 'UGX',
            'description' => 'OptiGrab',
            /* Information used by application to identify transaction */
            'metadata'    => "{ 'appId': 'my-application', 'xactId': '1' }"
        );
        // f62a81d491fb2921d99797f3825c3fbf014b2f17
        
        try {
          $response = LaramanBeyonic::createCollectionRequest($paymentData);
          $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
          $resp->message  = Globals::$STATUS_DESC_SUCCESS;
          $resp->data = $parking_requests;
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
            $resp->data = null;
        }

        return response()->json($resp);
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
