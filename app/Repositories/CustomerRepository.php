<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Helpers\SharedCommon as Helper;

class CustomerRepository{
   
   // property
   public $customer, $customers;
   
   // Method
   public function getCustomers(){
      try{
         $this->customers = Customer::orderBy('id', 'desc')->get();
         return $this->customers;
      }catch(\Exception $ex){
         throw $ex;
      }
   }

   public function findCustomer($customerId){
      try{
         $this->customer = Helper::getCustomerData($customerId);
         return $this->customer;
      }catch(\Exception $ex){
         throw $ex;
      }
   }
   
   
   
   
   
   
}