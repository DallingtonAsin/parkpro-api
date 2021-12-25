<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Helper;
use Globals;

class RolesController extends Controller
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
                $roles = Role::all();
                $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                $resp->message  = Globals::$STATUS_DESC_SUCCESS;
                $resp->data = $roles;
                
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
        
        try{
            
            if( ($request->has('name') && $request->filled('name')) &&
                ($request->has('user_id') && $request->filled('user_id'))){

                $user_id = $request->input('user_id');
                $user = User::find($user_id);

                if($request->filled('role_id')){
                    $role_id = $request->input('role_id');
                    $role = Role::find($role_id);
                    $name = ucfirst($role->name);
                    $action = "updated role ".$name."";
                    $arr = $this->addUpdateRole($request, $role, $action, 'edit');
                    $resp->statusCode = $arr['statusCode'];
                    $resp->message = $arr['message'];
                      
                }else{

                $name = ucfirst($request->input('name'));
                $count = Role::where('name', '=', $name)->count();

                if($count == 0){
                    $role = new Role();
                    $action = "added role ".$name."";
                    $arr = $this->addUpdateRole($request, $role, $action, 'add');
                    $resp->statusCode = $arr['statusCode'];
                    $resp->message = $arr['message'];
                } else {
                    $messageErr = "role ".$name." has already been added";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
            }
                
            }else{
                $messageErr = "Unable to process request";
                $responseInfo = Helper::getMessage('error', $messageErr);
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        $resp->data = Role::count();
        return response()->json($resp);
    }


    private function addUpdateRole(Request $request, $role, $action, $type){
       
        $registra = User::where('id', $request->input('user_id'))->value('name');
        $registra_id = User::where('id', $request->input('user_id'))->value('role');

        $role->name = $request->input('name');
        $role->created_by = $registra;

        if ($role->save()) {
            $responseInfo = Helper::getMessage('success', $action);
            Helper::logActivity($request, ['name' => $registra, 'role' => Helper::getUserRole($registra_id), 'action' => $action]);
            $statusCode = Globals::$STATUS_CODE_SUCCESS;
            $message = $responseInfo; 
        } else {
            $messageErr = "Unable to ".$type." role";
            $responseInfo = Helper::getMessage('error', $messageErr);
            $statusCode = Globals::$STATUS_CODE_FAILED;
            $message = $responseInfo;
        }
        return ['statusCode' => $statusCode, 'message' => $message];
        
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
    public function destroy(Request $request)
    {
        $resp = new ApiResponse();
        
        try{
            
            if( ($request->has('role_id') && $request->filled('role_id')) &&
                ($request->has('user_id') && $request->filled('user_id'))){

                $role_id = $request->input('role_id');
                $user_id = $request->input('user_id');

                $role = Role::find($role_id);
                $user = User::find($user_id);

                $count = Role::where('id', $role_id)->count();
                $name = $role->name;
              
                if($count != 0){

                    if ($role->delete()) {
                        $action = "removed role ".$name."";
                        $responseInfo = Helper::getMessage('success', $action);
                        Helper::logActivity($request, ['name' => $user->name, 'role' => Helper::getUserRole($user->role), 'action' => $action]);
                        $resp->data = Role::count();
                        $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
                        $resp->message = $responseInfo; 
                    } else {
                        $messageErr = "removing role failed!";
                        $responseInfo = Helper::getMessage('error', $messageErr);
                        $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                        $resp->message = $responseInfo;
                    }
                } else {
                    $messageErr = "Unable to find role ".$name."";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                    $resp->message = $responseInfo;
                }
                
            }else{
                $messageErr = "Unable to process request";
                $responseInfo = Helper::getMessage('error', $messageErr);
                $resp->statusCode = Globals::$STATUS_CODE_FAILED;
                $resp->message = $responseInfo;
            }
        } catch (\Exception $ex) {
            $resp->statusCode = Globals::$STATUS_CODE_ERROR;
            $resp->message = $ex->getMessage();
        }
        return response()->json($resp);
    }
}
