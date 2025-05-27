<?php

namespace App\Services;

use App\Models\Assignments;
use App\Models\Companies;
use App\Models\Customers;
use App\Models\Peoples;
use App\Models\TimesheetDetails;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignmentService{
    public function checkAssignmentOverlap($people_id, $start_date, $end_date, $type, $action){
        $allAssignments = Assignments::where('people_id', $people_id)
                        ->where('is_deleted', 0)
                        ->select('start_date', 'end_date', 'type')
                        ->get();

        $existingAssignments = [];

        foreach ($allAssignments as $assignment){
            $existingStart = $assignment->start_date;
            $existingEnd = $assignment->end_date;

            if(($start_date <= $existingEnd) && ($end_date >= $existingStart)){
                $existingAssignments[] = $assignment;
            }
        }

        $overlapCount = 0;

        foreach ($existingAssignments as $assignment) {
            $existingStart = $assignment->start_date;
            $existingEnd = $assignment->end_date;

            if (($start_date <= $existingEnd) && ($end_date >= $existingStart)) {
                $overlapCount++;
            }
        }

        if (($type === 'full_time' && $action=='insert' && $overlapCount > 0) || ($type === 'part_time' && $action=='insert' && $overlapCount >= 2) || ($type === 'full_time' && $action=='edit' && $overlapCount > 1) || ($type === 'part_time' && $action=='edit' && $overlapCount >= 3)) {
            return true;
        }
        return false;
    }
    public function insertAssignment(Request $request){

        try{
        DB::beginTransaction();

        $people_id = $request->input("people_id");
        $start_date = $request->input("start_date");
        $end_date = $request->input("end_date");
        $type = $request->input("type");

        if ($this->checkAssignmentOverlap($people_id, $start_date, $end_date, $type, 'insert')) {
            return ["status" => 409, "message" => "Assignment limit exceeded for the selected type."];
        }

        $start_date = Carbon::parse($start_date)->setTimezone('Asia/Kolkata')->format('Y-m-d');
        $end_date = Carbon::parse($end_date)->setTimezone('Asia/Kolkata')->format('Y-m-d');
        $user = Auth::user();

        Assignments::insert([
            'people_id'=> $request['people_id'],
            'customer_id'=> $request['customer_id'],
            'start_date'=> $start_date,
            'end_date'=> $end_date,
            'role'=> $request['role'],
            'location'=> $request['location'],
            'status'=> $request['status'],
            'type'=> $request['type'],
            'company_id' =>$request['company_id'],
            'organization_id' => $user->organization_id
        ]);
        DB::commit();

        return ["status" => 1, "message" => "Inserted Successfully"];

        }

        catch(Exception $e){
            DB::rollBack();
            Log::error($e);
            Log::error("Assignment Service :: insertAssignment");
            return ["status" => 422, "message" => "Exception occured"];
        }

    }

    public function updateAssignments(Request $request, $id){
        try {
            DB::beginTransaction();
            $people_id = $request->input("people_id");
            $start_date = $request->input("start_date");
            $end_date = $request->input("end_date");
            $type = $request->input("type");

            if ($this->checkAssignmentOverlap($people_id, $start_date, $end_date, $type, 'edit')) {
                return ["status" => 409, "message" => "Assignment limit exceeded for the selected type."];
            }

            $start_date = Carbon::parse($start_date)->setTimezone('Asia/Kolkata')->format('Y-m-d');
            $end_date = Carbon::parse($end_date)->setTimezone('Asia/Kolkata')->format('Y-m-d');
            $user = Auth::user();

            Assignments::where("assignment_id", $id)
                        ->update([
                            'people_id'=> $request['people_id'],
                            'customer_id'=> $request['customer_id'],
                            'start_date'=> $start_date,
                            'end_date'=> $end_date,
                            'role'=> $request['role'],
                            'location'=> $request['location'],
                            'status'=> $request['status'],
                            'type'=> $request['type'],
                            'organization_id' =>$user->organization_id,
                    ]);
            DB::commit();
            return ["status"=> 200, "message" => "Assignment Updated Successfully"];
        }
        catch(Exception $e){
            DB::rollBack();
            Log::error($e);
            Log::error("Assignment Service :: updateAssignment");
            return ["status" => 422, "message" => "Exception occured"];
        }

    }

    public function deleteAssignments($id){
        try{
            DB::beginTransaction();
            Assignments::where("assignment_id",$id)
                ->update([
                    "is_deleted"=>1,
                ]);
            DB::commit();
            return ["status"=> 200, "message"=> "Assignment Deleted Successfully"];

        }
        catch(Exception $e){
            DB::rollBack();
            Log::error($e);
            Log::error("AssignmentService :: deleteAssignments");
            return ["status" => 422, "message" => "Exception occured"];
        }
    }

    public function getAssignments( $id, $page, $pageSize, $filter ){

        try{
            $currentPage = $page+1;
            $query = Assignments::join("peoples","peoples.people_id","=","assignments.people_id")
            ->join("customers","customers.customer_id","=","assignments.customer_id")
            ->where("assignments.is_deleted",0)
            ->where('assignments.company_id',$id)
            ->select("assignment_id",
                    "assignments.people_id",
                    "assignments.customer_id",
                    "start_date",
                    "end_date",
                    "role",
                    "location",
                    "status",
                    "type",
                    "peoples.name",
                    "customers.customer_name",
                    );

            if(!empty($filter)){
                foreach($filter as $filterField => $filterValue){
                    if($filterValue != null && $filterField != ''){
                        if($filterField === 'start_date' || $filterField === 'end_date'){
                            $filterValue = Carbon::parse($filterValue)->setTimezone('Asia/Kolkata')->format('Y-m-d');
                            $query->where($filterField, '=', $filterValue);
                            continue;
                        }
                        if($filterField === 'name'){
                            $query->where('peoples.name', 'like', "%$filterValue%");
                        }
                        else{
                            $query->where($filterField, 'like', "%$filterValue%");
                        }
                    }
                }
            }

            $assignments = $query->orderBy("assignment_id","desc")
                                 ->paginate($pageSize, ['*'], 'page', $currentPage);

            $company_name = Companies::where('company_id', $id)
                                ->select('company_name')
                                ->pluck('company_name');

            return ["assignments" => $assignments->items(),'company_name' => $company_name, "status" => 200];
        }
        catch(Exception $e){
            Log::error($e);
            Log::error("AssignmentService :: getAssignments");
            return ["status" => 422, "message" => "Exception occured", "assignments" => ""];
        }

    }

    public function getCustomers($id){

        try{
            $companyLists = Customers::where("company_id", $id)
                        ->where("is_deleted",operator: '0')
                        ->select("customer_name","customer_id")
                        ->orderBy("customer_id","desc")
                        ->get();

            return["companyLists" => $companyLists, "status" => 200];
        }
        catch(Exception $e){
            Log::error($e);
            Log::error("AssignmentService :: getCustomers");
            return ["status" => 422, "message" => "Exception occured", "companyLists" =>""];
        }
    }

    public function getPeople($id){

        try{
            $peopleLists = Peoples::where("company_id", $id)
                    ->where("is_deleted",0)
                    ->select("people_id","name")
                    ->orderBy("people_id","desc")
                    ->get();

        return ["peopleLists" => $peopleLists, "status" => 200];
        }
        catch(Exception $e){
            Log::error($e);
            Log::error("AssignmentService :: getPeople");
            return ["status" => 422, "message" => "Exception occured", "peopleLists" => ""];
        }

    }

    public function getTimesheet(){
        try{
            $timesheetDetails = TimesheetDetails::where('is_deleted',0)
                        ->select('assignment_id')
                        ->pluck('assignment_id')
                        ->toArray();

            return ["timesheetDetails" => $timesheetDetails, "status" => 200];
        }
        catch(Exception $e){
            Log::error($e);
            Log::error("AssignmentService :: getTimesheet");
            return ["status" => 422, "message" => "Exception occured", "timesheetDetails" =>""];
        }
    }

    public function getCardDetails($id){
        try{
            $company_details = Companies::where('company_id',$id)
                            ->select('company_name',
                                    'email',
                                    'domain',
                                    'city',
                                    'country',
                                    'company_logo',
                                    'phone_number',
                                    )
                            ->get()
                            ->map(function($company_details){
                                $company_details->company_logo = 'data:image/png;base64,' . $company_details->company_logo;
                                return $company_details;
                            });
            return ["company_details" => $company_details, "status" => 200];
        }
        catch(Exception $e){
            Log::error($e);
            Log::error("AssignmentService :: getCardDetails");
            return ["status" => 422, "message" => "Exception occured", "company_details" =>""];
        }
    }

    public function searchCompany($search){

        $user = Auth::user();
       
        $company_list = Companies::where('is_deleted', 0)
                                ->where("organization_id", $user->organization_id)
                                ->where('company_name','like',"$search%")
                                ->select("company_id","company_name")
                                ->get();

        return ['company_list'=> $company_list];


    }
}
