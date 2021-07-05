<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class TokenAuth
{
   
     public static function validate($token){
        try{
            if($token == 'test1234'){
                return true;
            }else{
                return false;
            }
        }catch(\Exception $ex){
            $name = $ex->getMessage();
        }
        return $name;
    }

}
