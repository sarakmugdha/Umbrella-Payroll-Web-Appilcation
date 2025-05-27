<?php
namespace App\Http\Controllers;
use App\Services\TimeSheetServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class TimeSheetController extends Controller
{
    public $timesheetService;
    public function __construct(TimeSheetServices $timesheetService){
            $this->timesheetService=$timesheetService;
    }
    public function retriveTimesheetData(Request $request){
        $id=$request->input('companyId');
        $page=$request->input('page');
        $pageSize=$request->input('pageSize');
        Log::channel('timesheet')->info($request);
        $retrivedData = $this->timesheetService->retriveTimesheetData($id,$page,$pageSize,$request);
        Log::channel("timesheet")->info($retrivedData);
        $companyName=$this->timesheetService->retriveCompanyName($id);
        return response()->json(['items'=>$retrivedData->items(),'total'=>$retrivedData->total(),'company'=>$companyName]);
    }
    public function insertTimesheetIntoDb(Request $request){
        $request->validate([
            'name' => 'required|string',
            'period_end_date' => 'required',
            'company_id' => 'numeric',
        ]);
        $timesheetId = $request->timesheet_id;
        Log::channel("timesheet")->info($request->all());
         $this->timesheetService->insertTimesheetIntoDb($timesheetId,$request->all());
        return response()->json(['message' => 'Timesheet inserted successfully']);
    }
    public function deleteTimesheet($id){
        $this->timesheetService->deleteTimesheet($id);
        return response()->json(['message' => 'Timesheet deleted successfully']);
    }
}
