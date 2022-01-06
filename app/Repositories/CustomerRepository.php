<?php

namespace App\Repositories;

use App\Models\Customer;

class CustomerRepository{

   // property
   public $customers;

   // Method
   public function getCustomers(){

    $this->customers = Customer::orderBy('id', 'desc')->get();
    return $this->customers;

   }






}