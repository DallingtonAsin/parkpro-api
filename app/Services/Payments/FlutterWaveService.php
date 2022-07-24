<?php

namespace App\Services\Payments;

use KingFlamez\Rave\Facades\Rave as Flutterwave;

class FlutterWaveService
{
    
    /**
    * Initialize Rave mobile money payment process
    * @return void
    */
    public function initializeMobileMoneyPayment($customer, $customerPhoneNumber, $amount){
        try{
            
            $tx_ref = Flutterwave::generateReference();
            $order_id = Flutterwave::generateReference('momo');
            $customerEmail = empty($customer->email) ? "info@parkproug.com" : $customer->email;
            
            $data = [
                'amount' => $amount,
                'email' => $customerEmail,
                'redirect_url' => route('momo-collection-callback'),
                'phone_number' => $customerPhoneNumber,
                'tx_ref' => $tx_ref,
                'order_id' => $order_id
            ];
            
            $charge = Flutterwave::payments()->momoUG($data);
            $charge['data']['amount'] = $amount;
            $charge['data']['reference'] = $tx_ref;
            $charge['data']['order_id'] = $order_id;
            
            return $charge;
            
        }catch(\Exception $ex){
            throw $ex;
        }
    }
    
    
}