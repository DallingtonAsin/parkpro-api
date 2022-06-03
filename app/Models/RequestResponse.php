<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestResponse extends Model
{
   protected $table = "requests";
   public $timestamps = false;
   protected $fillable = [
                'request',
                'response',
                'method',
                'url',
                'ip_address',
   ];


}
