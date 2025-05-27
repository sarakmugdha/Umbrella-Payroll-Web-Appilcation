<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\UserService;


class UserController extends Controller
{
    public $UserService;
    public function __construct(UserService $UserService)
    {
        $this->UserService = $UserService;
    }
    public function getUserDetails(Request $request)
    {

        $userDetails = $this->UserService->getUserDetails($request);
        $userData = $userDetails["data"];
        $totalData = $userDetails["total"];
        return response()->json([
            "userData" => $userData
            ,
            "totalData" => $totalData
        ]);
    }
    public function addUser(Request $request)
    {
        Log::info($request);
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'username' => 'required'
        ]);


        $userData = $this->UserService->addUser($request);
        if ($userData['status'] === 409) {
            return response()->json([
                "status" => 409,
            ]);
        }
        if ($userData['status'] === 200) {
            return response()->json([
                "status" => 200,
            ]);
        }

    }



    public function deleteUser(Request $request)
    {
        $id = $request->input('id');

        $userData = $this->UserService->deleteUser($id);


        return response()->json($userData);
    }

    public function updateUserStatus(Request $request)
    {

        $userData = $this->UserService->updateUserStatus($request);

        return response()->json($userData);

    }




}