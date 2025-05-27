<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    protected $table = "timesheets";
    protected $fillable = ['name','timesheet_date','company_id','period_end_date','organization_id','invoice_sent'];

    protected $primaryKey = 'timesheet_id';

    protected $hidden = ['created_at','updated_at'];
}
