<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository{

   // property

   public $users;

   // Method
   public function getUsers(){

    $this->users = User::orderBy('id', 'desc')->get();
    if(count((array)$this->users) > 0){
        foreach($this->users as $user){
            $user->name = $user->first_name." ".$user->last_name;
        }
    }
    return $this->users;

   }






}