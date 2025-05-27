<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{

    public $authService;
    public function __construct(AuthService $authService){
            $this->authService=$authService;
    }
   

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'pwd' => 'required|string'
        ]);
        $result=$this->authService->login($request);

        return response()->json($result,$result['status']);

  

    }

    public function passwordSetup(Request $request, $id, $hash)
    {
        $request->validate([
            'password' => 'string|required',
            'cnfpassword' => "string|required|same:password"
        ]);

        $result=$this->authService->passwordSetup($request,$id,$hash);
       return response()->json($result,$result['status']);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => "required|string"
        ]);

        $result=$this->authService->forgotPassword($request);

        return response()->json($result,$result['status']);

    } 
    
    // public function register(Request $request)
    // {

    //     $request->validate(
    //         [
    //             'name' => 'required|string',
    //             'email' => 'required|string|email|unique:users,email',
    //             'username' => 'required|string|unique:users,username',
    //             'orgName' => 'required'
    //         ]
    //     );
    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'username' => $request->username,
    //     ]);
    //     $user->sendEmailVerificationNotification();


    //     return response()->json(
    //         [
    //             "status" => 200,
    //             "message" => "Password setup link shared to your email"
    //         ]
    //     );
    // }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->token()->revoke();

        return response()->json(['status' => 'success', 'message' => 'Logout successful'], 200);
    }

}