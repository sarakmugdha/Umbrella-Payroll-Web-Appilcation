<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organizations extends Model
{
    //
    protected $table = "organizations";
    protected $fillable = ["organization_name","email","address","state","pincode"];

    protected $hidden = ["is_deleted","created_at","updated_at"];
}
