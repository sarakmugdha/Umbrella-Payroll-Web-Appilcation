<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AssignmentService;

class AssignmentsController extends Controller
{
    public $assignmentService;
    public function __construct(AssignmentService $assignmentsServices){
        $this->assignmentService = $assignmentsServices;
    }
    public function insertAssignmentDetails(Request $request){

        $request->validate([
            'people_id' =>'required',
            'customer_id'=> 'required',
            'start_date'=> 'required|date',
            'end_date'=> 'required|date',
            'role' => 'required|string',
            'location' => 'required|string',
            'status' => 'required',
            'type' => 'required',
            'company_id' =>'required'
        ]);

        $assignments = $this->assignmentService->insertAssignment($request);

        $status = $assignments['status'];
        $message = $assignments['message'];

        if($status === 409){
            return response()->json([
                "status"=> $status,
                "message"=> $message,
            ]);
        }
        if($status === 422){
            return response()->json([
                "status"=> $status,
                "message"=> $message,
            ]);
        }

        return response()->json([
            "status"=> $status,
            "message"=> $message,
        ]);

    }

    public function updateAssignmentDetails(Request $request){

        $id = $request->input("assignment_id");

        $request->validate([
            "assignment_id" => "required",
            "people_id"=> "required",
            "customer_id"=> "required",
            "start_date"=> "required",
            "end_date"=> "required",
            "role"=> "required",
            "location"=> "required",
            "status"=> "required",
            "type"=> "required"
        ]);

        $assignments = $this->assignmentService->updateAssignments($request, $id);

        $status = $assignments['status'];
        $message = $assignments['message'];

        if($status === 409){
            return response()->json([
                "status"=> $status,
                "message"=> $message,
            ]);
        }

        if($status === 422){
            return response()->json([
                "status"=> $status,
                "message"=> $message,
            ]);
        }

        return response()->json([
            "status"=> $status,
            "message"=> $message,
        ]);

    }

    public function deleteAssignmentDetails(Request $request){

        $id = $request->input("assignment_id");

        $assignments = $this->assignmentService->deleteAssignments( $id);

        $status = $assignments['status'];
        $message = $assignments['message'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
                "message"=> $message,
            ]);
        }

        return response()->json([
            "status"=> $status,
            "message"=> $message,
        ]);
    }

    public function getAssignmentDetails(Request $request){

        $id = $request->input("company_id");
        $page = $request->input('page');
        $pageSize = $request->input('pageSize');
        $filter = $request->input('filter');

        $assignments = $this->assignmentService->getAssignments($id, $page, $pageSize, $filter);

        $status = $assignments['status'];
        $assignments_list = $assignments['assignments'];
        $company_name = $assignments['company_name'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);
        }

        return response()->json([
            "assignments"=>$assignments_list,
            "company_name" => $company_name
        ]);

        }

    public function getCustomersDetails(Request $request){
        $id = $request->input("company_id");

        $assignments = $this->assignmentService -> getCustomers($id);

        $status = $assignments['status'];
        $customerList = $assignments['companyLists'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);
        }

        return response()->json($customerList);
    }

    public function getPeopleDetailsofCompany(Request $request){
        $id = $request->input("company_id");

        $peopleList = $this->assignmentService->getPeople($id);

        $status = $peopleList['status'];
        $peopleLists = $peopleList['peopleLists'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);
        }

        return response()->json($peopleLists);
    }

    public function getTimesheetCreationDetails(){

        $timesheetDetails = $this->assignmentService->getTimesheet();

        $status = $timesheetDetails['status'];
        $timesheetDetail = $timesheetDetails['timesheetDetails'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);
        }

        return response()->json($timesheetDetail);

    }

    public function getCardDetails(Request $request){
        $id = $request->input("company_id");

        $assignments = $this->assignmentService->getCardDetails($id);
        $company_details = $assignments['company_details'];
        $status =$assignments['status'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);
        }

        return response()->json($company_details);

    }

    public function searchCompany(Request $request){
        $search = $request->input('search');
         if ($search!=''){
        $companiesList = $this->assignmentService->searchCompany($search);
        $company_list = $companiesList['company_list'];

        return response()->json([
            "company_list" => $company_list,
        ]);
    }
    return response()->json(["company_list" => null]);
    }

}
