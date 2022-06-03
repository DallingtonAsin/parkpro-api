<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Channels\CustomPaymentDbChannel;
use Illuminate\Support\Facades\Notification;
use App\Repositories\UserRepository;
use App\Services\Billing\PaymentGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    $this->app->bind('UserRepository', function(){
        return new UserRepository();
    });

    $this->app->singleton('Payment', function(){
        return new PaymentGateway();
    });
       
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Notification::extend('payment', function ($app) {
            return new CustomPaymentDbChannel();
        });
    }


}
