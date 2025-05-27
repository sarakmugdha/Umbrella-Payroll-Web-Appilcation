<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignments extends Model
{
    protected $table = "assignments";

    protected $primaryKey = "assignment_id";
    protected $fillable = [          
                        'organization_id',
                        'company_id',
                        'people_id',
                        'customer_id',
                        'start_date',
                        'end_date',
                        'role',
                        'location',
                        'status',
                        'type',      
                    ];
    
    protected $hidden = [
        'created_by',
        'updated_by',
        'is_deleted',
        'created_at',
        'updated_at',
    ];
    

    public function peoples()
{
    return $this->belongsTo(Peoples::class, 'people_id', 'people_id');
}

public function customers()
{
    return $this->belongsTo(Customers::class, 'customer_id', 'customer_id');
}


}
