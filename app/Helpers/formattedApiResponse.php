<?php

namespace App\Helpers;

use App\Helpers\ApiResponse;

class formattedApiResponse{
 
  public static function getJson($data){

    $resp = new ApiResponse();
    try {
      $clients = $data;
      $resp->statusCode = Globals::$STATUS_CODE_SUCCESS;
      $resp->message  = Globals::$STATUS_DESC_SUCCESS;
      $resp->data = $clients;
    } catch (\Exception $ex) {
      $resp->statusCode = Globals::$STATUS_CODE_ERROR;
      $resp->message = Globals::$STATUS_DESC_ERROR;
      $resp->data = $ex->getMessage();
    }

    return response()->json($resp);

  }


}

