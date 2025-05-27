<?php

namespace App\Http\Controllers;

use App\Exports\TimesheetExport;
use App\Models\Timesheet;
use App\Services\TimesheetDetailsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
class TimesheetDetailsController extends Controller
{
    public $timesheetDetailsService;
    public function __construct(TimesheetDetailsService $timesheetDetailsService){
        $this->timesheetDetailsService=$timesheetDetailsService;
    }

    public function retriveTimesheetDetails(Request $request)
    {

        $timesheetId = $request->input('timesheetId');
        $companyId = $request->input('companyId');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize');
        $retrivedData = $this->timesheetDetailsService->retriveTimesheetDetails($timesheetId,$companyId,$page,$pageSize,$request);

        return response()->json($retrivedData);


    }

    public function insertTimesheetDetails(Request $request)
    {


        Log::channel('timesheet')->info('request received',$request->all());
        $request->validate([
            "people_name" => "required|string",
            "customer_name" => "required|string",
            "hours_worked" => "required|numeric",
            "hourly_pay" => "required|numeric",
            "assignment_id" => "required|numeric",


        ]);
        $timesheetDetailId = $request->timesheet_detail_id;
        $this->timesheetDetailsService->insertTimesheetDetails($timesheetDetailId,$request->all());

        return response()->json(['message' => 'Timesheet inserted successfully']);




    }

    public function extractCsvData(Request $request){
        Log::channel('timesheet')->info('request',$request->all());
        $request->validate([
           "file" => "required|mimes:csv,txt",
           "timesheet_id"=> "required",
        ]);
        $file = $request->file('file');
        $timesheetId = (int) $request->input('timesheet_id');
        $companyId = (int) $request->input('company_id');
        $this->timesheetDetailsService->extractCsvData($file,$timesheetId,$companyId);
        return response()->json(['message' => 'Timesheet uploaded successfully']);

    }

    public function deleteTimesheetDetail($timesheetDetailId){
        $this->timesheetDetailsService->deleteTimesheetDetail($timesheetDetailId);
        return response()->json(['message' => 'Timesheet detail deleted successfully']);


    }


    public function deleteUnmappedTimesheetDetails($timesheetId){
        $this->timesheetDetailsService->deleteUnmappedTimesheetDetails($timesheetId);
        return response()->json(['message' => 'TimesheetDetail deleted successfully']);

    }
    public function fetchAssignmentDetails($assignmentId){

        $retrivedAssignmentDetails = $this->timesheetDetailsService->fetchAssignmentDetails($assignmentId);
        Log::channel('timesheet')->info($retrivedAssignmentDetails);
        return response()->json($retrivedAssignmentDetails);

    }

    public function downloadTImesheet($timesheetId){
         try{
            $name=Timesheet::where('timesheet_id',$timesheetId)->value('name');
            return Excel::download(new TimesheetExport($timesheetId),"$name.xlsx");


        }
        catch(Exception $e){
            Log::channel('timesheet')->info($e);
            return response()->json(['error' => 'Transaction failed'], 500);
        }

    }
}
