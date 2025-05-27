<?php



namespace App\Services;

use App\Mail\ForgotMail;
use App\Models\User;
use Exception;
use Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mail;
use Laravel\Passport\Http\Controllers\AccessTokenController;




class AuthService

{
    protected $super;
    public function __construct(AccessTokenController $super)
    {
        $this->super = $super;
    }

    public function login($request)
    {
        try {
            $credentials = $request->only('name', 'pwd');
            Log::channel('timesheet')->info($credentials);

            // $loginField = filter_var($credentials['name'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            // $credentials = [$loginField => $credentials['name'], 'password' => $credentials['pwd']];

            // $user = User::where($loginField, $request->name)->first();

            // if (!$user || !Hash::check($request->pwd, $user->password)) {

            //     return response(['message' => 'Invalid credentials', 'status' => 401]);
            // }


            // $token = $user->createToken('auth_token')->accessToken;
            Log::channel('timesheet')->info('hello',[env('PASSPORT_PASSWORD_CLIENT_ID'), env('PASSPORT_PASSWORD_CLIENT_SECRET')]);
            $token = Http::asForm()->post(env('PASSPORT_URL').'/oauth/token', [
                'grant_type' => 'password',
                'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
                'username' => $request->name,
                'password' => $request->pwd,
                'scope' => '',
            ]);

            Log::channel('timesheet')->info($token);

            return [
                'status' => 200,
                'expiry' => $token['expires_in'],
                'messsage' => "Login Successful",
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'],
                'token_type' => 'Bearer',
            ];


        } catch (Exception $e) {
            Log::channel('timesheet')->info($e);
            return [
                'status' => 400,
                'messsage' => "Bad Request",
            ];
        }
    }

    public function passwordSetup($request, $id, $hash)
    {
        try {
            $exp = $request->query("exp");


            if (now()->timestamp > $exp) {
                return response()->json(['status' => 'error', 'message' => 'Password Link Expired'], 403);
            }

            $user = User::findOrFail($id);
            if (!hash_equals($hash, sha1($user->email))) {
                return response()->json('Invalid Verification Link', 404);
            }



            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }


            $pwd = Hash::make($request->password);
            $user->password = $pwd;
            $user->save();

            return ['status' => 200, 'message' => 'Password Set Successful'];
        } catch (Exception $e) {
            Log::error($e);
            return ['message' => 'Bad Request', 'status' => 500];
        }

    }

    public function forgotPassword($request)
    {
        $User = User::where('email', $request->email)->first();
        $hash = sha1($User->email);
        if ($User->password == NULL) {
            return response()->json('Account password not yet set', 403);
        }
        Mail::to($User->email)->send(new ForgotMail(['hash' => $hash, 'id' => $User->id]));
        $User->is_mfa_enabled = 0;
        $User->save();

        return ['message' => 'Password Reset Link sent successfully', 'status' => 200];
    }
}