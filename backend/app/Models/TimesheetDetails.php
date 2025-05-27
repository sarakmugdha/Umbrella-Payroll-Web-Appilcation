<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimesheetDetails extends Model
{
    protected $table = 'timesheet_details';
    protected $primaryKey = 'timesheet_detail_id';
    protected $fillable = ['timesheet_id','people_name','customer_name','hours_worked','hourly_pay','assignment_id','is_mapped','organization_id','company_id','people_id','customer_id'];

    protected $hidden = ['created_at','updated_at','is_deleted','created_by','updated_by'];
}
