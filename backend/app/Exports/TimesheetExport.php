<?php

namespace App\Exports;

use App\Models\TimesheetDetails;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TimesheetExport implements FromCollection, WithHeadings
{
    protected $timesheetId;
    public function __construct($timesheetId){
        $this->timesheetId = $timesheetId;

    }
    public function collection()
    {
        return TimesheetDetails::select('assignment_id','people_name','customer_name','hours_worked','hourly_pay','total_pay')->where('timesheet_id',$this->timesheetId)->where('is_mapped',1)->where('is_deleted',0)->get();
    }
    public function headings(): array{
        return['assignment_id','people_name','customer_name','hours_worked','hourly_pay','total_pay'];
    }
}
