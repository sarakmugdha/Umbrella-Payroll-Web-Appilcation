<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    
    protected $table = 'Customers';
    //
protected $fillable = [
    'customer_name','email','taxNo','address','state','pincode'
];
}
