<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository{

   // property

   public $company;

   // Method
   public function getCompanyData(){
    $this->company  = Company::orderBy('id', 'desc')->get();
    return $this->company;
   }






}