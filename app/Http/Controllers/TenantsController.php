<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Globals;
use App\Helpers\ApiResponse;
use App\Models\Tenant;
use App\Models\House;
use Helper;

class TenantsController extends Controller
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
            $tenants = Tenant::all();
            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message  = Globals::$STATUS_DESC_SUCCESS;
            $resp->data = $tenants;
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
        try {
            return view('pages.tenants.create');
        } catch (\Exception $ex) {
            dd($ex->getMessage());
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

          $resp = new ApiResponse();

          try{

             
             $tenant = new Tenant();

             $first_name = $request->input('first_name');
             $last_name = $request->input('last_name');
             $name = $request->input('name');
             $gender = $request->input('gender');
             $phone_no= $request->input('phone_no');
             $email = $request->input('email');
             $date_of_birth= $request->input('date_of_birth');
             $district= $request->input('district');
             $country= $request->input('country');
             $national_id = $request->input('national_id');
             $parent_name= $request->input('parent_name');
             $parent_residence= $request->input('parent_residence');
             $parent_tel= $request->input('parent_tel');
             $local_language= $request->input('local_language');
             $university= $request->input('university');
             $course= $request->input('course');
             $year_of_study= $request->input('year_of_study');
             $year_of_entry = $request->input('year_of_entry');
             $company= $request->input('company');
             $position= $request->input('position');
             $house_id = $request->input('house_id');
             $status= $request->input('status');
             $exit_date= $request->input('exit_date');
             $registration_date= $request->input('registration_date');
             $user= $request->input('user');
             $tenantName = $first_name." ".$last_name;

            $house = House::find($house_id);
            $houseNo = $house->house_number;

            $tenant->first_name = $first_name;
            $tenant->last_name = $last_name;
            $tenant->name = $tenantName;
            $tenant->gender = $gender;
            $tenant->phone_no =  $phone_no;
            $tenant->email = $email ;
            $tenant->date_of_birth =  $date_of_birth;
            $tenant->district =  $district;
            $tenant->country =  $country;
            $tenant->national_id = $national_id;
            $tenant->parent_name =  $parent_name;
            $tenant->parent_residence =  $parent_residence;
            $tenant->parent_tel =  $parent_tel;
            $tenant->local_language = $local_language ;
            $tenant->university = $university ;
            $tenant->course = $course ;
            $tenant->year_of_study =  $year_of_study;
            $tenant->year_of_entry = $year_of_entry;
            $tenant->company = $company ;
            $tenant->position = $position;
            $tenant->house = $houseNo;
            $tenant->status = $status;
            $tenant->exit_date = $exit_date;
            $tenant->registration_date = $registration_date;
            
        if ($tenant->save()) {

            $this->ChangeHouseState($house_id, 'occupied');

            $action = "registered tenant ".$tenantName."";
            $responseInfo = $this->getMessage('success', $action);
            Helper::logActivity($request, $user, $action);

            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message = $responseInfo;

        } else {
            $messageErr = "tenant registration failed!";
            $responseInfo = $this->getMessage('error', $messageErr);
            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
            $resp->message = $responseInfo;

        }


      } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }

        $arr = $this->getTenantStats();
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
        $tenant = Tenant::find($id);
        return response()->json($tenant);
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
        try{

             $id = $request->input('id');
             $tenant = Tenant::find($id);
             $tenantName = $tenant->name;

             $first_name = $request->input('first_name');
             $last_name = $request->input('last_name');
             $name = $request->input('name');
             $gender = $request->input('gender');
             $phone_no= $request->input('phone_no');
             $email = $request->input('email');
             $date_of_birth= $request->input('date_of_birth');
             $district= $request->input('district');
             $country= $request->input('country');
             $national_id = $request->input('national_id');
             $parent_name= $request->input('parent_name');
             $parent_residence= $request->input('parent_residence');
             $parent_tel= $request->input('parent_tel');
             $local_language= $request->input('local_language');
             $university= $request->input('university');
             $course= $request->input('course');
             $year_of_study= $request->input('year_of_study');
             $year_of_entry = $request->input('year_of_entry');
             $company= $request->input('company');
             $position= $request->input('position');
             $house= $request->input('house');
             $status= $request->input('status');
             $exit_date= $request->input('exit_date');
             $registration_date= $request->input('registration_date');
             $user= $request->input('user');

             $house_id = House::where('house_number', $house)->value('id');
            
            $tenant->first_name = $first_name;
            $tenant->last_name = $last_name;
            $tenant->name = $first_name." ".$last_name;
            $tenant->gender = $gender;
            $tenant->phone_no =  $phone_no;
            $tenant->email = $email ;
            $tenant->date_of_birth =  $date_of_birth;
            $tenant->district =  $district;
            $tenant->country =  $country;
            $tenant->national_id = $national_id;
            $tenant->parent_name =  $parent_name;
            $tenant->parent_residence =  $parent_residence;
            $tenant->parent_tel =  $parent_tel;
            $tenant->local_language = $local_language ;
            $tenant->university = $university ;
            $tenant->course = $course ;
            $tenant->year_of_study =  $year_of_study;
            $tenant->year_of_entry = $year_of_entry;
            $tenant->company = $company ;
            $tenant->position = $position;
            $tenant->house = $house;
            $tenant->status = $status;
            $tenant->exit_date = $exit_date;
            $tenant->registration_date = $registration_date;
            
        if ($tenant->save()) {
             
            
            $this->ChangeHouseState($house_id, 'occupied');
            $action = "updated details of tenant ".$tenantName."";
            $responseInfo = $this->getMessage('success', $action);
            Helper::logActivity($request, $user, $action);

            $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
            $resp->message = $responseInfo;

        } else {
            $messageErr = "tenant Update failed!";
            $responseInfo = $this->getMessage('error', $messageErr);
            $resp->statusCode = Globals::$STATUS_CODE_FAILED;
            $resp->message = $responseInfo;

        }


      } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }

        $arr = $this->getTenantStats();
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
            $tenant = Tenant::find($id);
            $tenantName = $tenant->name;

            if ($tenant->delete()) {
                $action = "removed tenant ".$tenantName." from the system";
                Helper::logActivity($request, $user, $action);
                $responseInfo = $this->getMessage('success', $action);

                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message = $responseInfo ;
            } else {
                $messageErr = "Tenant not removed!";
                $responseInfo = $this->getMessage('error', $messageErr);

                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = Globals::$STATUS_DESC_ERROR;
        }

        $arr = $this->getTenantStats();
        $resp->data = $arr['totl'];
        return response()->json($resp);

    }


    protected function getTenantName($id)
    {
        try {
            $tenant_name = Tenant::where('id', $id)->value('name');
            $data = ['SUCCESS', $tenant_name];
        } catch (\Exception $ex) {
            $data = ['ERROR', $ex->getMessage()];
        }
        return $data;
    }


    protected function getTenantStats()
    {
        $total_tenants = Tenant::count();
        $data = array(
          'totl' => $total_tenants,
      );
        return $data;
    }

    protected function ChangeHouseState($house_id, $status){
        try{
          
           $affected = House::where('id', $house_id)
              ->update(['status' => $status]);
           ($affected) ? $updateResp = 1 : $updateResp = 0;
           return $updateResp;

        }catch(\Exception $ex){
              throw $ex;
        }
    }

    protected function getMessage($status, $activity)
    {
        $status == 'error'
        ? $message = $activity
        : $message = "You have successfully ".$activity."";
        return $message;
    }
}
