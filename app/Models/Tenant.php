<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';
    public $timestamps = true;
    protected $fillable = [
              'first_name',
              'last_name',
              'phone_no',
              'email',
              'date_of_birth',
              'district',
              'country',
              'parent_name',
              'parent_residence',
              'parent_tel',
              'local_language',
              'university',
              'course',
              'year_of_study',
              'year_of_entry',
              'company',
              'position',
    ];
}
