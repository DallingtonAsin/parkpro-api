<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Repositories\UserRoleRepository;
use Helper;
use Globals;

class RolesController extends Controller
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
    public function index(UserRoleRepository $roleRepo)
    {
        $resp = new ApiResponse();
        try {
                $roles = $roleRepo->getRoles();
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'name' => 'required',
        ]);
        
        try{
            
            if($validator->fails()){
                $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->response['message'] = $validator->errors()->all();
            }else{

                $user_id = $request->input('user_id');
                $user = User::find($user_id);
                $name = ucfirst($request->input('name'));
                $count = Role::where('name', '=', $name)->count();
                $author = Helper::getUserNames($user_id);

                if($count == 0){
                    $role = new Role();
                    $role->name = $name;
                    $role->created_by = $author;
                    if($role->save()){
                    $role = Helper::getUserRoleName($user_id);
                    $action = "added role ".$name."";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response['message'] = Helper::getMessage('success', $action);
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                    }else{
                        $this->response['message'] ="Unable to add role ".$name."!";
                        $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED; 
                    }

                } else {
                    $messageErr = "role ".$name." has already been added";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    $this->response['statusCode'] = Globals::$STATUS_CODE_FAILED;
                    $this->response['message'] = $responseInfo;
                }

            }
            
        } catch (\Exception $ex) {
            $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
            $this->response['message'] = $ex->getMessage();
        }
     
        return response()->json($this->response, 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        return response()->json($role, 200);
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
        ]);
        
        try{
            if($validator->fails()){
                $this->response['statusCode'] = Globals::$STATUS_CODE_ERROR;
                $this->response['message'] = $validator->errors()->all();
            }else{
                
                $author_id = $request->input('user_id');
                $role_name = $request->input('name');
                $role = Role::find($id);
                $name = $role->name;
                $author = Helper::getUserNames($author_id);
                
                $role->name = $role_name;
                $role->updated_by = $author;
                
                if($role->save()){
                    $role = Helper::getUserRoleName($author_id);
                    $action = "updated role ".$name." details";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response['message'] = Helper::getMessage('success', $action);
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                }else{
                    $this->response['message'] ="Unable to update role details!";
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
                $role = Role::find($id);
                $role_name = $role->name; 

                $is_deleted = $role->is_deleted;
                $undo = !$is_deleted;
                $activity = $undo ? 'deleted': 'restored';
                $author = Helper::getUserNames($author_id);

                $role->is_deleted = $undo;
                $role->deleted_by = $author;

                if($role->save()){
                    $role = Helper::getUserRoleName($author_id);
                    $action = "".$activity." role ".$role_name."";
                    Helper::logActivity($request, ['name' => $author, 'role' => $role, 'action' => $action]);
                    $this->response['message'] = Helper::getMessage('success', $action);
                    $this->response['statusCode'] = Globals::$STATUS_CODE_SUCCESS;
                }else{
                    $this->response['message'] ="Unable to delete role!";
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
