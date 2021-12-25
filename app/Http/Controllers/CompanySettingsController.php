<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\User;
use App\Helpers\ApiResponse;
use Helper;
use Globals;

class CompanySettingsController extends Controller
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
                $company = Company::all();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $company;
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
        $resp = new ApiResponse();
        $method = "UserController@store";
        
        try{
                
                if($request->filled(['user_id', 'name', 'email', 'address', 'mobile_no']))
                {
                  
                    if($request->has('company_id') && $request->filled('company_id')){
                        $company_id = $request->input('company_id');
                        $company_exists = Company::where('id','=', $company_id)->exists();
                        if($company_exists){
                            $company = Company::find($company_id);
                            $name = $company->name;
                            $action = "updated details of company ".$name."";
                            $arr = $this->addUpdateCompany($request, $company, $action, 'edit');
                            $resp->statusCode = $arr['statusCode'];
                            $resp->message = $arr['message'];
                            $resp->data = $arr['data'];
                        }else{
                            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                            $resp->message = "Unable to find company with specified id";
                        }
                    }else{

                        $name = trim($request->input('name'));
                        $email = trim($request->input('email'));

                        $name_exists = Company::where('name','=', $name)->exists();
                        $email_exists = Company::where('email', '=', $email)->exists();
                        if(!$name_exists){
                            if(!$email_exists){
                                $company = new Company();
                                $action = "added details for company ".$name."";
                                $arr = $this->addUpdateCompany($request, $company, $action, 'add');
                                $resp->statusCode = $arr['statusCode'];
                                $resp->message = $arr['message'];
                                $resp->data = $arr['data'];
                            }else{
                                $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                                $resp->message = "Company with email ".$email." already exists";
                            }
                        } else{
                            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                            $resp->message = "Company with name ".$name." already exists";
                        }
                    }
                    
                }else{
                    $resp->statusCode = Globals::$STATUS_CODE_ERROR;
                    $resp->message = "Unable to process request: missing parameters";
                }
            
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $message = $ex->getMessage();
        }
        
        $dataArr = array("code" => $resp->statusCode,
        "message" => $resp->message,
        "method" => $method);
        Helper::LogRequest($request, $dataArr);
        return response()->json($resp);
        
    }
    
    private function addUpdateCompany(Request $request, $company, $action, $type){

        if($request->has('abbreviation') && $request->filled('abbreviation')) {
            $company->abbreviation = $request->input('abbreviation');
        }
        if($request->has('motto') && $request->filled('motto')) {
            $company->motto = $request->input('motto');  
        }

        $company->name = trim($request->input('name'));
        $company->email = trim($request->input('email'));
        $company->address = trim($request->input('address'));
        $company->mobile_no = trim($request->input('mobile_no'));
        $user = User::find($request->input('user_id'));
        $registra = $user->first_name." ".$user->last_name;
        $registra_id = User::where('id', $request->input('user_id'))->value('role');

        if($company->save()){
            $statusCode = Globals::$STATUS_CODE_SUCCESS;
            $message = Helper::getMessage('success', $action);
            $data = Company::count();
            Helper::logActivity($request, ['name' => $registra, 'role' => Helper::getUserRole($registra_id), 'action' => $action]);
        }else{
            $statusCode = Globals::$STATUS_CODE_FAILED;
            $message = "Unable to ".$type." company details";
            $data = null;
        }
        
        return ['statusCode' => $statusCode, 'message' => $message, 'data' => $data];
        
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
