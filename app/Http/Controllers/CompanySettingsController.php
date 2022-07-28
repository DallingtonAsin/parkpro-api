<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Models\User;
use App\Helpers\formattedApiResponse;
use Helper;
use Globals;

class CompanySettingsController extends Controller
{
    
    
    protected $response;
    
    public function __construct()
    {
        
    }
    
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(CompanyRepository $companyRepo)
    {
        
        $company = $companyRepo->getCompanyData();
        return formattedApiResponse::getJson($company);
        
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
                        $message= $arr['message'];
                        $data= $arr['data'];
                        return Helper::sendOkHttpResponse(['messsage' => $message, 'data' => $data]);
                    }else{
                        $message= "Unable to find company with specified id";
                        return Helper::sendFailedHttpResponse($message);
                        
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
                            $message= $arr['message'];
                            $data= $arr['data'];
                            return Helper::sendOkHttpResponse(['message' => $message, 'data' => $data]);
                        }else{
                            $message= "Company with email ".$email." already exists";
                            return Helper::sendFailedHttpResponse($message);
                            
                        }
                    } else{
                        $message= "Company with name ".$name." already exists";
                        return Helper::sendFailedHttpResponse($message);
                        
                    }
                }
                
            }else{
                $message= "Unable to process request: missing parameters";
                return Helper::sendFailedHttpResponse($message);
                
            }
            
        } catch (\Exception $ex) {
            $message= $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
            
        }
        
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
