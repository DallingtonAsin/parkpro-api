<?php 


namespace App\Facades;

class Payment{
    
    public static function __callStatic($method, $arguments){
        return (self::resolveFacade('Payment'))
        ->$method(...$arguments);
    }
    
    protected static function resolveFacade($name){
        return  app()[$name];
    }

  
    
    
    
}
