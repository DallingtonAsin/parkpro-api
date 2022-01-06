<?php

namespace App\Repositories;

use App\Models\Client;

class ClientRepository{

   // property

   public $clients;

   // Method
   public function getClients(){

    $this->clients = Client::orderBy('id', 'desc')->get();
    return $this->clients;

   }






}