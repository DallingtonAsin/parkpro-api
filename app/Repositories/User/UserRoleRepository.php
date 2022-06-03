<?php

namespace App\Repositories\User;

use App\Models\Role;

class UserRoleRepository{

   // property

   public $roles;

   // Method
   public function getRoles(){

    $this->roles  = Role::orderBy('id', 'desc')->get();
    return $this->roles;

   }






}