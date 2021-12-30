<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCategory extends Model
{
    use HasFactory;
    protected $table='vehicle_categories';
    public $fillable = [
        'name', 'is_deleted'
     ];
    public $timestamps = true;
}
