<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{   
    protected $primaryKey='invoice_id';
    protected $fillable=['invoice_number','timesheet_id','assignment_id','people_id',
    'customer_id','company_id','organization_id','due_date','total_pay','status','type'];

    protected $hidden=['created_at','updated_at','is_deleted'];
}
