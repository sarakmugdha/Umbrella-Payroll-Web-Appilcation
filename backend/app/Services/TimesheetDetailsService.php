<?php


namespace App\Services;

use App\Exports\TimesheetExport;
use App\Imports\TimesheetDetailsImport;
use App\Models\Assignments;
use App\Models\Companies;
use App\Models\Timesheet;
use App\Models\TimesheetDetails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Maatwebsite\Excel\Facades\Excel;


class TimesheetDetailsService
{

    public function retriveTimesheetDetails($timesheetId,$companyId,$page,$pageSize,$filter){

        try{
            $currentPage = $page+1;

            $retrivedData = TimesheetDetails::where("timesheet_id", $timesheetId )->where('is_deleted',0);
            $retrivedAssignmentNumber = Assignments::where('company_id',$companyId)->where('is_deleted',0)->where('status',1)->pluck('assignment_id')->toArray();
            $invoiceSent = Timesheet::where("timesheet_id",$timesheetId)->value('invoice_sent');

            if ($filter !== null) {

              $retrivedData->where(function ($filterQuery) use ($filter) {
                  if (!empty($filter['assignment_number'])) {
                      $filterQuery->Where('assignment_id',$filter['assignment_number']);
                  }

                  if (!empty($filter['people_name'])) {
                      $filterQuery->Where('people_name', 'like', '%' . $filter['people_name'] . '%');
                  }
                  if (!empty($filter['customer_name'])){



                      $filterQuery->Where('customer_name','like',"%".$filter['customer_name']."%");
                  }
                  if (!empty($filter['hours_worked'])) {
                    $filterQuery->Where('hours_worked',$filter['hours_worked']);
                }
                if (!empty($filter['hourly_pay'])) {
                    $filterQuery->Where('hourly_pay',$filter['hourly_pay']);
                }
              });
          }

            $retrivedData = $retrivedData->orderBy('timesheet_detail_id', 'desc')->paginate($pageSize, ['*'], 'page', $currentPage);
            $retrivedCompanyName = Companies::where('company_id',$companyId)->value('company_name');
            $retrivedTimesheetName = Timesheet::where('timesheet_id',$timesheetId)->value('name');
            Log::channel('timesheet')->info('TD',['items'=>$retrivedData->items(),'total'=>$retrivedData->total(),'assignmentId'=>$retrivedAssignmentNumber,'invoiceSent'=>$invoiceSent,'companyName'=>$retrivedCompanyName,'timesheetName'=>$retrivedTimesheetName]);

            return['items'=>$retrivedData->items(),'total'=>$retrivedData->total(),'assignmentId'=>$retrivedAssignmentNumber,'invoiceSent'=>$invoiceSent,'companyName'=>$retrivedCompanyName,'timesheetName'=>$retrivedTimesheetName];
           }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }

    }



    public function insertTimesheetDetails($timesheetDetailId,$request){

        try{
            DB::beginTransaction();
            $assignmentDetails=Assignments::select('company_id','people_id','customer_id','organization_id')->where('assignment_id',$request['assignment_id'])->get();

            if(!$timesheetDetailId){
                TimesheetDetails::create([
                   'timesheet_id'=> $request['timesheet_id'],
                   'people_name'=> $request['people_name'],
                   'customer_name'=> $request['customer_name'],
                   'hours_worked'=> $request['hours_worked'],
                   'hourly_pay'=> $request['hourly_pay'],
                   'assignment_id'=> $request['assignment_id'],
                   'organization_id'=> $assignmentDetails[0]->organization_id,
                   'company_id'=> $assignmentDetails[0]->company_id,
                   'people_id'=> $assignmentDetails[0]->people_id,
                   'customer_id'=> $assignmentDetails[0]->customer_id,
               ]);
            }
           else{
               TimesheetDetails::where('timesheet_detail_id',$request['timesheet_detail_id'])->update($request);
           }
            DB::commit();

        }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }

    }

    public function deleteUnmappedTimesheetDetails($timesheetId){
        try{
            DB::beginTransaction();
            TimesheetDetails::where('timesheet_id',$timesheetId)->where('is_mapped',0)->update(['is_deleted'=>1]);
            DB::commit();

        }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }

    }

    public function deleteTimesheetDetail($timesheetDetailId){
        try{
            DB::beginTransaction();
            TimesheetDetails::where('timesheet_detail_id',$timesheetDetailId)->update(['is_deleted'=>1]);
            DB::commit();
        }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }

    }

    public function fetchAssignmentDetails($assignmentId){
        try{
            $retrivedAssignmentDetails = Assignments::join('peoples','assignments.people_id','=','peoples.people_id')
            ->join('customers','assignments.customer_id','=','customers.customer_id')
            ->select('peoples.name','customers.customer_name')->where('assignment_id',$assignmentId)->get();
            return $retrivedAssignmentDetails;
        }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            return response()->json(['error' => 'Transaction failed'], 500);
        }

    }

    public function extractCsvData($file,$timesheetId,$companyId){
        try{
            Excel::import(new TimesheetDetailsImport($timesheetId,$companyId),$file );


        }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            return response()->json(['error' => 'Transaction failed'], 500);
        }
    }
    public function downloadTImesheet($timesheetId){

        try{
            $name=Timesheet::where('timesheet_id',$timesheetId)->value('name');
            Excel::download(new TimesheetExport($timesheetId),"$name.xlsx");


        }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            return response()->json(['error' => 'Transaction failed'], 500);
        }

    }

}