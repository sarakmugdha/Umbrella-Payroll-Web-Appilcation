<?php

namespace App\Services;

use App\Models\Companies;
use App\Models\Timesheet;
use App\Models\TimesheetDetails;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TimeSheetServices
{

    public function retriveTimesheetData($id,$page,$pageSize,$filter){

        try{
            $currentPage = $page+1;

            $retrivedData = Timesheet::where('company_id',$id)->where('is_deleted','0');

            if ($filter !== null) {
                  Log::channel('timesheet')->info(!empty($filter['timesheet_name']));
                $retrivedData->where(function ($filterQuery) use ($filter) {
                    if (!empty($filter['timesheet_name'])) {
                        $filterQuery->Where('name', 'like', '%' . $filter['timesheet_name']. '%');
                    }

                    if (!empty($filter['period_end_date'])) {
                        $filterValue = Carbon::parse($filter['period_end_date'])->setTimezone('Asia/Kolkata')->format('Y-m-d');
                        $filterQuery->Where('period_end_date', 'like', '%' . $filterValue . '%');
                    }
                    if (!empty($filter['status'])){

                        $status = $filter['status'] === 'sent' ? 1 : ($filter['status'] === 'draft' ? 0 : 2);

                        $filterQuery->Where('invoice_sent',$status);
                    }
                });
            }

            return $retrivedData->orderBy('timesheet_id', 'desc')->paginate($pageSize, ['*'], 'page', $currentPage);
        }
        catch(Exception $e){
            Log::info($e);
        }


        ;
    }

    public function retriveCompanyName($id){
        return Companies::where('company_id',$id)->value('company_name');
    }

    public function insertTimesheetIntoDb($timesheetId,$request){

        try{

            $user = Auth::user();
            Log::channel('timesheet')->info($user);
            DB::beginTransaction();
            if(!$timesheetId){
               Timesheet::Create([
                    'organization_id' => $user->organization_id,

                    'name' => $request['name'],
                    'period_end_date' => $request['period_end_date'],
                    'company_id' => $request['company_id'],
               ]);

            }
            else{

               Timesheet::where("timesheet_id",$timesheetId)->update($request);
            }
            DB::commit();



        }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }


    }

    public function deleteTimesheet($id){

        try{
            DB::beginTransaction();
            TimesheetDetails::where('timesheet_id',$id)->update(['is_deleted'=>'1']);
            Timesheet::where('timesheet_id',$id)->update(['is_deleted'=>'1']);
            DB::commit();
        }
        catch(Exception $e){
            Log::info($e);
            DB::rollBack();
            return response()->json(['error' => 'Transaction failed'], 500);
        }


    }

}