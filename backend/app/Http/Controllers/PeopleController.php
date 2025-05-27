<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PeopleService;

class PeopleController extends Controller
{
    public $peopleService;
    public function __construct(PeopleService $peopleService){
        $this->peopleService = $peopleService;
    }
    function insertPeople(Request $request){ 

        $request->validate([
            "name"=>'required|string',
            'email'=>'required|email',
            'job_type' => 'required|string',
            'gender' => 'required|string',
            'date_of_birth' =>'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country'=> 'required|string',
            'pincode' => 'required|integer',
            'ni_no' => 'required',

        ]);

        $people = $this->peopleService ->insertPeople($request);
        $status = $people['status'];
        $message = $people['message'];

        if($status === 409){
            if($people['emailExists']){
                return response()->json([
                    "status"=>$status,
                    "message"=>$message,
                    "emailExists" => true,
                ]);
            }
            else{
                return response()->json([
                    "status"=>$status,
                    "message"=>$message,
                ]);
            }  
        }

        return response()->json([
            "status"=>1,
            "message"=>$message,
        ]);
    }

    function updatePeople(Request $request){
        
        $id=$request['people_id'];

        $request->validate([
            "name"=>'required|string',
            'email'=>['required','regex:/^(.+)@(.+)\.(.+)$/i'],
            'job_type' => 'required|string',
            'gender' => 'required|string',
            'date_of_birth' =>'required|date',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country'=> 'required|string',
            'pincode' => 'required|integer',
            'ni_no' => 'required',
        ]);

        $people = $this -> peopleService -> updatePeople($request, $id);
        $status = $people['status'];
        $message = $people['message'];
        
        if($status === 409){
            if($people['emailExists']){
                return response()->json([
                    "status"=>$status,
                    "message"=>$message,
                    "emailExists" => true,
                ]);
            }
            else{
                return response()->json([
                    "status"=>$status,
                    "message"=>$message,
                ]);
            }  
        }
        
        if($status === 422){
            return response()->json([
                "status"=>$status,
                "error" => $message,
            ]);
        }

        return response()->json([
            "status"=> $status
        ]);
        
    }

    function deletePeople(Request $request){
        $id=$request["people_id"];

        $people = $this->peopleService->deletePeople($id);
        $status = $people['status'];
        $message = $people['message'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
                "error"=>$message,
            ]);
        }
 
        return response()->json([
            "status"=> $status,
            "message"=>$message,
        ]);
    }

    function getPeopleDetails(Request $request){

        $page = $request->input('page');
        $pageSize = $request->input('pageSize');
        $filter = $request->input('filter');
        
        $people = $this->peopleService->getPeopleDetails( $page, $pageSize, $filter);
        $status = $people['status'];
        $peopleData = $people['peopleData'];

        if($status === 422){
            return response()->json([
                "status"=> $status,
            ]);
        }

        return response()->json($peopleData);
    }
}
