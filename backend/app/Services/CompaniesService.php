<?php

namespace App\Services;

use App\Models\Assignments;
use App\Models\Companies;
use App\Models\Customers;
use App\Models\Organizations;
use App\Models\Peoples;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompaniesService{
    public function insertComapany($request) {
        try{
        DB::beginTransaction();
            $exists = Companies::where('is_deleted', 0)
            ->where('email', $request['email'])
            ->exists();

        if ($exists) {
            return [
                "message" => "Email Already Exists",
                "status" => 422,
                "company" => ""
            ];
        }

        $image = $request->input('company_logo');

        if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $image)) {
            return [
                'status' => 415,
                'message' => 'Logo must be a .jpeg or .jpg format',
                'company'=> ''
            ];
        }

        $base64Image = base64_encode(file_get_contents($image));
        $user = Auth::user();

        Companies::insert([
            "organization_id" => $user -> organization_id,
            "company_name"=> $request -> company_name,
            "email"=> $request -> email,
            "phone_number" => $request -> phone_number,
            "vat_percent" =>$request -> vat_percent,
            "domain" => $request -> domain,
            "address" => $request -> address,
            "state" => $request -> state,
            "city" => $request ->city,
            "pincode" => $request -> pincode,
            "country" => $request -> country,
            "company_logo" => $base64Image ,
        ]);

        DB::commit();
        return ["status" => 200];
        }
        catch(Exception $e){
            DB::rollBack();
            Log::error($e);
            Log::error("CompaniesServcies :: insertCompany");
            return ["status" => 422, "message" => "Exception occured", "company" =>""];
        } 
    }

    public function updateCompany ($request, $id){
        try{
            DB::beginTransaction();

            $image = $request->input('company_logo');

            if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $image)) {
                return [
                    'status' => 415,
                    'message' => 'Logo must be a .jpeg or .jpg format',
                    'company'=> ''
                ];
            }

            $base64Image = base64_encode(file_get_contents($image));
            $user = Auth::user();

            Companies::where("company_id",$id)
                        ->update([
                            "organization_id" => $user -> organization_id,
                            "company_name"=> $request -> company_name,
                            "email"=> $request -> email,
                            "phone_number" => $request -> phone_number,
                            "vat_percent" =>$request -> vat_percent,
                            "domain" => $request -> domain,
                            "address" => $request -> address,
                            "state" => $request -> state,
                            "city" => $request ->city,
                            "pincode" => $request -> pincode,
                            "country" => $request -> country,
                            "company_logo" => $base64Image,
                        ]);
            DB::commit();
            return ["status" => 200];
        }
        catch(Exception $e){
            DB::rollBack();
            Log::error($e);
            Log::error("CompaniesServcies :: updateCompany");
            return ["status" => 422, "message" => "Exception occured", "company" =>""];
        }
    }

    public function deleteCompany($id){
        
        try{
            DB::beginTransaction();
            Companies::where("company_id", $id)
                       ->update([
                         "is_deleted" => 1,
                        ]);
            Peoples::where('company_id',$id)
                        ->update([
                            "is_deleted" => 1,
                        ]);
            Assignments::where('company_id', $id)
                        ->update([
                            "is_deleted"=>1,
                        ]);
            Customers::where('company_id',$id)
                        ->update([
                            "is_deleted"=>1,
                        ]);
            DB::commit();
            return ["status"=> 200];
        }
        catch(Exception $e){
            DB::rollBack();
            Log::error($e);
            Log::error("CompaniesServcies :: deleteCompany");
            return ["status" => 422, "message" => "Exception occured"];
        }
    }

    public function companyList( $page, $pageSize, $filter){
        try{

            $currentPage = $page+1;
            $user = Auth::user();

            $query = Companies::where("is_deleted",0)
                ->where("organization_id",$user->organization_id)
                ->select('company_id',
                        'organization_id',
                        'company_name',
                        'email',
                        'phone_number',
                        'vat_percent',
                        'domain',
                        'address',
                        'city',
                        'state',
                        'country',
                        'pincode',
                        'company_logo'
                        );
            
            if (!empty($filter)) {
                foreach ($filter as $filterField => $filterValue) {
                    if ($filterValue !== null && $filterValue !== '') {
                        if($filterField === "vat_percent"){
                            $query->where("vat_percent","=","$filterValue");
                        }
                        $query->where($filterField, 'like', "%$filterValue%");
                    }
                }
            }           
                     
            $company = $query->orderBy("company_id",'desc')
                            ->paginate($pageSize, ['*'], 'page', $currentPage );

            $company->getCollection()->transform(function ($item) {
                    $item->company_logo = 'data:image/png;base64,' . $item->company_logo;
                    return $item;
            });


            return ["status"=> 200, "company" => $company];
        }
        catch(Exception $e){
            Log::error($e);
            Log::error("CompaniesServcies :: companyList");
            return ["status" => 422, "message" => "Exception occured"];
        }
    }

    public function companiesListDetails(){
        try{
            $user = Auth::user();

            $Company = Companies::where("is_deleted",0)
                ->where("organization_id",$user->organization_id)
                ->select("company_name","company_id")
                ->orderBy("company_id",'desc')
                ->get();
            Log::info($Company);
           
            return ["status" => 200, "company" => $Company];
        }
        catch(Exception $e){
            Log::error($e);
            Log::error("CompaniesServcies :: companyListDetails");
            return ["status" => 422, "message" => "Exception occured"];
        }
    }

    public function getDashboardDetails(){
        try{
            $user = Auth::user();

            $totalCompanies = Companies:: where('is_deleted',0)
                            ->where('organization_id',$user->organization_id)
                            ->count();

            $totalPeople = Peoples:: where('is_deleted',0)
                        ->where('organization_id', $user->organization_id)
                        ->count();

            $totalAssignments = Assignments:: where('is_deleted', 0)
                        ->where('organization_id', $user->organization_id)
                        ->count();
            
            $orgName = Organizations:: where('organization_id', $user->organization_id)
                                ->select('organization_name')
                                ->pluck('organization_name');

            return [
                'totalCompanies' => $totalCompanies,
                'totalPeople' => $totalPeople,
                'totalAssignments' => $totalAssignments, 
                'orgName' => $orgName,
                'status' => 200,
            ];
        }
        catch(Exception $e){
            Log::error($e);
            Log::error("CompaniesServcies :: getDashboardDetails");
            return ["status" => 422, "message" => "Exception occured"];
        }
    }
    public function ganttDetails() {
        try {

            $user = Auth::user();
            $allAssignments = Assignments::join('Peoples','peoples.people_id',"=",'assignments.people_id')
                            ->join('Customers','customers.customer_id',"=",'assignments.customer_id')
                            ->join('companies','companies.company_id',"=",'assignments.company_id')
                            ->where('assignments.organization_id', $user->organization_id)
                            ->where('assignments.is_deleted', 0)
                            ->select(
                                'assignments.company_id',
                                'companies.company_name',
                                'customers.customer_name',
                                'assignments.people_id',
                                'peoples.name',
                                'assignments.assignment_id',
                                'start_date',
                                'end_date',
                                'location',
                                'role')
                            ->orderBy('assignment_id','desc')
                            ->get();

                           
                return [
                    'allAssignments' => $allAssignments,
                    'status' => 200
                ];

        } catch (Exception $e) {
            Log::error($e);
            return ['status' => 500, 'message' => 'Server Error'];
        }
    }
    
}