<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $table = 'clients';
    public $fillable = [
       'name', 'address', 'mobile_number', 'email', 'is_deleted'
    ];
    public $timestamps = true;

}
