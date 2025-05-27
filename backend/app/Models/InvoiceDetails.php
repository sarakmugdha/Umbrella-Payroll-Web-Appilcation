<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceDetails extends Model
{
    protected $primaryKey='invoice_detail_id';
    protected  $fillable=['invoice_id','timesheet_detail_id','company_id','organization_id','description','hours_worked','hourly_pay','total_pay','is_deleted'];
}
