<?php

namespace App\Imports;
use App\Models\Assignments;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\TimesheetDetails;
use Illuminate\Support\Facades\Log;
class TimesheetDetailsImport implements ToModel,WithHeadingRow
{
   protected $timesheetId;
   protected $companyId;
   public function __construct( $timesheetId,$companyId)
    {
        $this->companyId = $companyId;
        $this->timesheetId = $timesheetId;
        Log::channel('timesheet')->info('comp',[$this->companyId]);
    }
    public function model(array $r)
    {

        Log::channel('timesheet')->info('comp',[$this->companyId]);

        $assignment_id=($r['assignment'])?$r['assignment']:false;
        Log::channel('timesheet')->info($assignment_id);
        $assignmentIdList=Assignments::where('company_id',$this->companyId)->pluck('assignment_id')->toArray();
        Log::channel('timesheet')->info($assignmentIdList);
        $is_exist=in_array($assignment_id,$assignmentIdList);
        Log::channel('timesheet')->info($is_exist);
        if($is_exist){
        $retrivedAssignmentDetails = Assignments::join('peoples','assignments.people_id','=','peoples.people_id')
        ->join('customers','assignments.customer_id','=','customers.customer_id')
        ->select('peoples.name','customers.customer_name','assignments.people_id','assignments.customer_id','assignments.organization_id','assignments.company_id')->where('assignment_id',$assignment_id)->get();

        Log::channel('timesheet')->info($retrivedAssignmentDetails);
        }

        return new TimesheetDetails([
            "assignment_id"=>($is_exist) ? $assignment_id :null,
            "is_mapped"=>($is_exist) ? 1 : 0,
            "timesheet_id"=> $this->timesheetId,
            "people_name"=> ($is_exist) ? $retrivedAssignmentDetails[0]->name : $r['name'],
            "customer_name"=> ($is_exist) ? $retrivedAssignmentDetails[0]->customer_name : $r['company'],
            "hours_worked"=> $r['hours'],
            "hourly_pay"=> $r['pay'],
            "people_id" => ($is_exist) ? $retrivedAssignmentDetails[0]->people_id : null,
            "customer_id" => ($is_exist) ? $retrivedAssignmentDetails[0]->customer_id : null,
            "organization_id" => ($is_exist) ? $retrivedAssignmentDetails[0]->organization_id : null,
            "company_id" => ($is_exist) ? $retrivedAssignmentDetails[0]->company_id : null,

        ]);
    }
}
