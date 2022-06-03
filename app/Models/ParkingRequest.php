<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingRequest extends Model
{
    use HasFactory;
    protected $table = 'parking_requests';
    public $timestamps = true;
}
