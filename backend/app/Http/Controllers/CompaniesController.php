<?php

namespace App\Http\Controllers;

use App\Services\CompaniesService;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    public $companiesService;
    public function __construct(CompaniesService $companiesService){
        $this->companiesService = $companiesService;
    }
    public function insertCompany(Request $request){

        $request->validate([
            "company_name" => "required",
            "email" => "required",
            "phone_number" => "required",
            "vat_percent" => "required",
            "address" => "required",
            "city" => "required",
            "state" => "required",
            "country"=> "required",
            "pincode"=> "required",
        ]);
        
        $companies = $this -> companiesService ->insertComapany($request);
        $status = $companies ['status'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);  
        }
        if($status === 415){
            return response()->json([
                "status"=> $status,
                "message" => $companies['message']
            ]);
        }

        return response()->json($status);
    }

    public function updateCompanyDetails(Request $request){

        $id = $request -> company_id;

        if(!$id){
            return response()->json([
                "status" => 422,
                "message" => "Error",
            ]);
        }
   
        $request->validate([
            "company_name" => "required",
            "email" => "required",
            "phone_number" => "required",
            "vat_percent" => "required",
            "address" => "required",
            "city" => "required",
            "state" => "required",
            "country"=> "required",
            "pincode"=> "required",
        ]);

        $companies = $this->companiesService->updateCompany($request, $id);
        $status = $companies["status"];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);
        }
        if($status === 415){
            return response()->json([
                "status"=> $status,
                "message" => $companies['message']
            ]);
        }

        return response()->json([
            "status"=> $status,
        ]);

    }


    public function deleteCompany(Request $request){
        $id = $request -> input('company_id');
        
        if (!$id) {
            return response()->json([
                'status' => 422,
                'message' => 'company_id is required',
            ]);
        }

        $company = $this->companiesService->deleteCompany($id);
        $status = $company["status"];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);
        }

        return response()->json([
            "status"=> $status,
        ]);
    }


    public function CompanyList(Request $request){

        $page = $request->input('page');
        $pageSize = $request->input('pageSize');
        $filter = $request->input('filter');

        $companies = $this->companiesService->companyList( $page, $pageSize, $filter);
        $company = $companies['company'];
        $status = $companies['status'];

        if($status === 422 ){
            return response()->json([
                'status' => 422,
            ]);
        }

        return response()->json([
            "company" => $company->items(),
            "total" => $company->total(),
        ]);
    }

    public function getCompaniesList(){
    
        $companies = $this->companiesService->companiesListDetails();

        $company = $companies['company'];
        $status = $companies['status'];
        
        if($status === 422){
            return response()->json([
                'status' => 422,
            ]);
        }

        return response()->json($company);
    }
    public function getDashboardDetails(){

        $dashboardDetails = $this->companiesService ->getDashboardDetails();
        $totalCompanies = $dashboardDetails['totalCompanies'];
        $totalPeople = $dashboardDetails['totalPeople'];
        $totalAssignments = $dashboardDetails['totalAssignments'];
        $status = $dashboardDetails['status'];
        $orgName = $dashboardDetails['orgName'];

        if($status === 422){
            return response()->json([
                'status' => 422,
            ]);
        }

        return response()->json([
            'totalCompanies' => $totalCompanies,
            'totalPeople' => $totalPeople,
            'totalAssignments' => $totalAssignments,
            'orgName' => $orgName,
            'status' => $status,
        ]);
    }

    public function ganttDetails(){

        $chartDetails = $this->companiesService->ganttDetails();
        $allAssignments = $chartDetails['allAssignments'];

        return response()->json([
                'allAssignments' => $allAssignments,
                'status' => 200
        ]);
    }

}
