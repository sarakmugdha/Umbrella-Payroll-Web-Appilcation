<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peoples extends Model
{
    protected $tableName = "Peoples";
    protected $primaryKey = "people_id";
    protected $fillable = [
        'ni_no',
        'company_id',
        'organization_id',
        "name",
        "job_type",
        "company_id",
        "gender",
        "date_of_birth",
        "address",
        "city",
        "state",
        "country",
        "pincode",
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
        "is_deleted",
        "updated_by",
        "created_by"
    ];
}
