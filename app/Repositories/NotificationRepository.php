<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\Notifications;


class NotificationRepository{

   // property

   public $notifications;

   // Method
   public function getUserNotification($customer_id){

    $this->notifications = Notifications::where('notifiable_id', $customer_id)->orderBy('created_at', 'desc')->get();
    $this->notifications->makeHidden(['notifiable_type']);
    if(count($this->notifications->toArray()) > 0){
        foreach($this->notifications as $item){
            $customer = Customer::find($item->notifiable_id);
            $item->name = $customer->first_name." ".$customer->last_name;
            $item->date = date('Y-m-d H:i A', strtotime($item->created_at));
        }
    }
    return $this->notifications;

   }






}