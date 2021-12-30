<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingArea extends Model
{
    use HasFactory;
    protected $table="parking_areas";
    
    public $fillable = [
      'client_id', 'name', 'address', 'description',
      'opens_at', 'closes_at', 'latitude', 'longitude',
      'rating', 'total_space', 'current_free_space', 'is_deleted'
    ];
}
