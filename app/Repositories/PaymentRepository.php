<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\CustomersLedger;
use App\Models\Customer;
use Carbon\Carbon;

class PaymentRepository{
    
    // property
    
    public $transaction_history;
    
    // Method
    public function getTransactionRecords($customer_id){
        
        $this->transactions = CustomersLedger::where('customer_id', $customer_id)->orderBy('created_at', 'desc')->get()->groupBy(function($val) {
            return Carbon::parse($val->created_at)->format('Y');
        });
        if(count($this->transactions->toArray()) > 0){
            foreach($this->transactions as $key){
                foreach($key as $item){
                    $customer = Customer::find($customer_id);
                    $item->name = $customer->first_name." ".$customer->last_name;
                    $item->credit = number_format($item->credit);
                    $item->debt = number_format($item->debt);
                    $item->balance = number_format($item->balance);
                }
            }
        }
        return $this->transactions;
    }

    public function mapNotifications($transactions){
        $data = $result = [];
        foreach($transactions as $key => $value){
            $data = array(
                'year' => $key,
                'data' => $value,
            );
            array_push($result, $data);
        }
        return $result;
    }
    
    
    
    
    
    
}