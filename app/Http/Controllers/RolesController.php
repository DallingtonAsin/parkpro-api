<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Repositories\User\UserRoleRepository;
use App\Helpers\formattedApiResponse;
use App\Helpers\SharedCommon as Helper;
use App\Helpers\Globals as Globals;

class RolesController extends Controller
{
    
    protected $response ;
    
    
    public function __construct(){
        
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(UserRoleRepository $roleRepo)
    {
        try{
            $roles = $roleRepo->getRoles();
            return formattedApiResponse::getJson($roles);
        }catch(\Exception $ex){
            return Helper::sendFailedHttpResponse($ex->getMessage());
        }
        
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
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
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
                        $message = Helper::getMessage('success', $action);
                        return Helper::sendOkHttpMessage($message);
                        
                    }else{
                        $message ="Unable to add role ".$name."!";
                        return Helper::sendFailedHttpResponse($message);
                    }
                    
                } else {
                    $messageErr = "role ".$name." has already been added";
                    $responseInfo = Helper::getMessage('error', $messageErr);
                    return Helper::sendFailedHttpResponse($responseInfo);
                }
                
            }
            
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
        
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
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
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
                    $message = Helper::getMessage('success', $action);
                    return Helper::sendOkHttpMessage($message);
                }else{
                    $message ="Unable to update role details!";
                    return Helper::sendFailedHttpResponse($message);
                }
            }
        }catch(\Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
        
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
                $message = $validator->errors()->all();
                return Helper::sendFailedHttpResponse($message);
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
                    $message = Helper::getMessage('success', $action);
                    return Helper::sendOkHttpMessage($message);
                    
                }else{
                    $message ="Unable to delete role!";
                    return Helper::sendFailedHttpResponse($message);
                }
            }
        }catch(\Exception $ex){
            $message = $ex->getMessage();
            return Helper::sendFailedHttpResponse($message);
        }
        
        
    }
    
    
    
    
}
