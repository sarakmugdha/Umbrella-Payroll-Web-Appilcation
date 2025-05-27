<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    protected $table = "Companies";
    protected $primaryKey = "company_id";

    protected $fillable = [
        "company_name",
        "email",
        "phone_number",
        "vat_percent",
        "domain",
        "address",
        "city",
        "state",
        "country",
        "pincode",
        "company_logo"
    ];

    public $hidden = [
        "created_by",
        "updated_by",
        "is_deleted",
    ];
}
