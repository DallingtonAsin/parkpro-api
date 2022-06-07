<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = "customers";
    public $timestamps = true;

    protected $guarded=['id'];
    protected $fillable = [
       'first_name',
       'last_name',
       'country_iso_code',
       'country_code',
       'phone_number',
       'email',
       'account_balance',
       'image',
       'otp',
       'pin',
       'unique_device_id',
       'fcm_token',
       'current_version',
       'ip_address',
       'device_language',
       'profile_status',
       'is_active',
       'is_blocked',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAuthPassword(){
        return $this->otp;
    }

}
