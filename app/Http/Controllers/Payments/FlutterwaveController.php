<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use App\Services\Payments\FlutterWaveService;
use App\Services\Transaction\MobileMoney\MMService;
use App\Models\MobileMoneyTransaction;
use Illuminate\Support\Facades\Log;
use Globals;

class FlutterwaveController extends Controller
{
    
    protected $flutterWaveService, $moMoService;
    
    public function __construct(FlutterWaveService $flutterWaveService, MMService $moMoService)
    {
        $this->flutterWaveService = $flutterWaveService;
        $this->moMoService = $moMoService;
    }
    
    /**
    * Obtain Rave mobile money callback information
    * @return void
    */
    public function mobileMoneyCollectionCallback()
    {
        
        $status = request()->status;
        $transactionID = Flutterwave::getTransactionIDFromCallback();

        Log::channel('single')->info("Transaction ID ".$transactionID);
        Log::channel('single')->info("I have actually received a response ".$status);

        //if payment is successful
        if ($status ==  'successful') {
            
           
            // $this->moMoService->updateTransaction($transactionID);
            $data = Flutterwave::verifyTransaction($transactionID);
            Log::channel('single')->info("Response data ".json_encode($data));

            if(MobileMoneyTransaction::where('tranReference', $transactionID)->exists()){
                $mm = MobileMoneyTransaction::where('tranReference', $transactionID)
                ->update([
                    'status' => Globals::$SUCCESS_STATUS,
                ]);
            }
            
            
            Log::channel('single')->info("MoMo API Response code ".$status);
            Log::channel('single')->info($data);
            // dd($data);
        }
        elseif ($status ==  'cancelled'){
            $mm = MobileMoneyTransaction::where('tranReference', $transactionID)
            ->update([
                'status' => Globals::$CANCELED_STATUS,
            ]);
            Log::channel('single')->info($status);
            //Put desired action/code after transaction has been cancelled here
        }
        else{
            $mm = MobileMoneyTransaction::where('tranReference', $transactionID)
            ->update([
                'status' => Globals::$FAILED_STATUS,
            ]);
            //Put desired action/code after transaction has failed here
        }
        // Get the transaction from your DB using the transaction reference (txref)
        // Check if you have previously given value for the transaction. If you have, redirect to your successpage else, continue
        // Confirm that the currency on your db transaction is equal to the returned currency
        // Confirm that the db transaction amount is equal to the returned amount
        // Update the db transaction record (including parameters that didn't exist before the transaction is completed. for audit purpose)
        // Give value for the transaction
        // Update the transaction to note that you have given value for the transaction
        // You can also redirect to your success page from here
        
    }
    
}
