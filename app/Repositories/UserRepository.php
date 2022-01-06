<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository{

   // property

   public $users;

   public function getUsers(){

    $users = User::orderBy('id', 'desc')->get();
    if(count((array)$users) > 0){
        foreach($users as $user){
            $user->name = $user->first_name." ".$user->last_name;
        }
    }
    return $users;

   }






}