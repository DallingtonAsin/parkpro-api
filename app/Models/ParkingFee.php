<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingFee extends Model
{
    use HasFactory;
    protected $table = 'parking_fees';
    public $fillable = [
      'parking_area_id', 'vehicle_cat_id', 'fee_per_hour', 'is_deleted'
    ];
}
