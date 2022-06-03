<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomersLedger extends Model
{
    use HasFactory;
    protected $table = 'customers_ledger';
    public $timestamp = true;
    protected $fillable = [
        'reference',
        'type',
        'customer_id',
        'description',
        'credit',
        'debt',
        'balance',
        'date',
    ];
}
