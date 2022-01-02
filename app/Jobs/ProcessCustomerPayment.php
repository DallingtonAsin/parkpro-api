<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Events\PaymentProcessed;
use App\Models\CustomersLedger;
use App\Models\Customer;
use Helper;
use Globals;



class ProcessCustomerPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $transData;
    
    /**
    * Create a new job instance.
    *
    * @return void
    */
    public function __construct($data)
    {
        $this->transData =  $data;
    }
    
    /**
    * Execute the job.
    *
    * @return void
    */
    public function handle()
    {
        return $this->processTopUp();
    }
    
    
    public function processTopUp(){

        $amount = $this->transData['amount'];
        $customer_id = $this->transData['customer_id'];
        $amount = Helper::Numberize($amount);
        $hasUpdated = Customer::where('id', $customer_id)->increment('account_balance', $amount);
        $customer = Customer::find($customer_id);
        $hasProcessed = false;
        $customerName = null;
        
        if ($hasUpdated) {

            $customerName = $customer->first_name. " ".$customer->last_name;
            $ledgerInput = [
                'reference' => time().''.$customer_id,
                'customer_id' => $customer_id,
                'type' => ucfirst('deposit'),
                'description' => ucfirst('deposit'),
                'credit' => $amount,
                'debt' => 0,
                'balance' => $customer->account_balance,
                'date' => date('Y-m-d'),
            ];

            CustomersLedger::create($ledgerInput);
            $paymentNotificationData = [
                'id' => $customer_id,
                'type' => ucfirst('payment'),
                'name' => $customerName,
                'body' => 'Congratulations, You have deposited amount '.number_format($amount).' successfully',
                'thanks' => 'Thank you',
                'offerText' => 'Please keep using the app to get better offers',
            ];

            PaymentProcessed::dispatch($paymentNotificationData);
            $hasProcessed = true;
        }

        return [
            'hasProcessed' => $hasProcessed,
            'customer' => $customerName,
        ];
        
    }


  


}
