<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Services\OrganizationService;


class OrganizationController extends Controller
{

    public $OrganizationService;
    public function __construct(OrganizationService $OrganizationService){
        $this->OrganizationService = $OrganizationService;
    }

    public function store(Request $request){

        $request->validate([
            'organization_name'=>'required',
            'email' => 'required',
            'address'=> 'required',
            'state'=> 'required',
            'pincode'=> 'required'
        ]);


        $organization = $this->OrganizationService->store($request);
        if($organization['status']===409){
            return response()->json([
                "status"=>409,
            ]);
            
        }
        
        return response()->json([
            "status"=>200,
        ]);
      
    }


public function update(Request $request){

        $id= $request->input("organization_id");



        $request->validate([
            'organization_id'=>'required',
            'organization_name'=>'required',
            'email' => 'required',
            'address'=> 'required',
            'state'=> 'required',
            'pincode'=> 'required',
            'status'=>'required'

    ]);


        $organizationData = $this->OrganizationService->update($request,$id);
        return response()->json(["data"=>$organizationData]);
}



public function delete(Request $request)
{
  $id =  $request->input('organization_id');

  $organizationData = $this->OrganizationService->delete($request, $id);
  return response()->json(["data"=>$organizationData]);
}

public function getOrganizationDetails(Request $request)
{
    $organizationData = $this->OrganizationService->getOrganizationDetails($request);
    return response()->json($organizationData);

}
public function countOrganization(){

        $organizationData = $this->OrganizationService->countOrganization();
        return response()->json($organizationData);
}

public function countActiveOrganization(){
   $activeCount = $this->OrganizationService->countActiveOrganization( );
    return response()->json($activeCount);

}

public function getOrgDetails(Request $request){
    $id = $request->input('organization_id');

    $organizationData = $this->OrganizationService->getOrgDetails($id);
    return response()->json($organizationData);
}

public function getOrgLogo(){


    $organizationLogo = $this->OrganizationService->getOrgLogo();
    return response()->json($organizationLogo);
}

}

