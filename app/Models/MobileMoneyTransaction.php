<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileMoneyTransaction extends Model
{
    use HasFactory;

    protected $table = "mobile_money_transactions";
    public $timestamps = true;
}
