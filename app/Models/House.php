<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    use HasFactory;
    protected $table = 'houses';
    public $timestamps = true;
    protected $fillable = [
            'id',
            'house_number',
            'features',
            'rent',
            'status'
    ];


}
