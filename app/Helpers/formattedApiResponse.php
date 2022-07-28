<?php

namespace App\Helpers;

use Helper;

class formattedApiResponse{
 
  public static function getJson($data){
    try {
      return response()->json($data);
    } catch (\Exception $ex) {
       throw $ex;
    }
  }


}

