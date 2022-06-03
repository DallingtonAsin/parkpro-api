<?php

namespace App\Repositories;

use App\Models\ActivityLog;

class SystemAuditRepository{

   // property

   public $logs;

   // Method
   public function getLogs(){
    $this->logs = ActivityLog::orderBy('id', 'desc')->get();
    return $this->logs;
   }






}