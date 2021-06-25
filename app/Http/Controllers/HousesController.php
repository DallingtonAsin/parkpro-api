<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use App\Models\House;
use Helper;

class HousesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function GetHouse(Request $request)
    {
        $resp = new ApiResponse();
        $house_status = $request->status;
        
        try {
            if ($house_status == 'vacant') {
                $houses = House::where('status', '=', 'vacant')->get();
            } elseif ($house_status == 'occupied') {
                $houses = House::where('status', '=', 'occupied')->get();
            } else {
                $houses = House::all();
            }

            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message  = Globals::$STATUS_DESC_SUCCESS;
            $resp->data = $houses;
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
    
        try {
            $house_number = $request->input('house_number');
            if (House::where('house_number', '=', $house_number)->exists()) {
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = 'Sorry, house with number '.$house_number.' has already been registered';
            } else {
                $features = $request->input('features');
                $rent = floatval(preg_replace('/[^\d.]/', '', $request->input('rent')));
                $status = $request->input('status');
                $user = $request->input('user');

                $house = new House();
                $house->house_number = $house_number;
                $house->features = $features;
                $house->rent = $rent;
                $house->status =  $status;

                if ($house->save()) {
                    $action = "recorded details of house ".$house_number."";
                    $sessionVariable = 'success';
                    $responseInfo = $this->getMessage('success', $action);
                    Helper::logActivity($request, $user, $action);
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message = $responseInfo ;
                } else {
                    $messageErr = "storing new house failed!";
                    $responseInfo = $this->getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $arr = $this->getHouseStats();
        $resp->data = $arr['totl'];
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
        $house = House::find($id);
        return response()->json($house);
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
    public function update(Request $request)
    {

         $resp = new ApiResponse();

        try {
               $house_number = $request->input('house_number');
                $id = $request->input('id');
                $house = House::find($id);
                $houseNo = $house->house_number;

                $features = $request->input('features');
                $rent = floatval(preg_replace('/[^\d.]/', '', $request->input('rent')));
                $status = $request->input('status');
                $user = $request->input('user');

                $house->house_number = $house_number;
                $house->features = $features;
                $house->rent = $rent;
                $house->status =  $status;


                if ($house->save()) {
                    $action = "updated details of house ".$houseNo."";
                    $responseInfo = $this->getMessage('success', $action);
                    Helper::logActivity($request, $user, $action);
                    $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                    $resp->message = $responseInfo ;
                } else {
                    $messageErr = "house update failed!";
                    $responseInfo = $this->getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = "ERROR Here ".$ex->getMessage();
        }

        $arr = $this->getHouseStats();
        $resp->data = $arr['totl'];
        return response()->json($resp);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

        $resp = new ApiResponse();

        try {
            $id = $request->input('id');
            $user = $request->input('user');
            $house = House::find($id);
            $house_no = $house->house_number;

            if ($house->delete()) {
                $action = "removed house ".$house_no." from the system";
                Helper::logActivity($request, $user, $action);
                $responseInfo = $this->getMessage('success', $action);

                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message = $responseInfo ;
            } else {
                $messageErr = "house not removed!";
                $responseInfo = $this->getMessage('error', $messageErr);

                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }

        $arr = $this->getHouseStats();
        $resp->data = $arr['totl'];
        return response()->json($resp);
    }


    protected function getHouseStats()
    {
        $total_houses = House::count();
        $data = array(
          'totl' => $total_houses,
      );
        return $data;
    }


    protected function getMessage($status, $activity)
    {
        $status == 'error'
        ? $message = $activity
        : $message = "You have successfully ".$activity."";
        return $message;
    }
}
