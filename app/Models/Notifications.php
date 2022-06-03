<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notifications extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    public $timestamp = true;

    public static function boot(){
        parent::boot();
        static::creating( function ($model){
            $model->uuid = Str::uuid();
        });
    }

}
