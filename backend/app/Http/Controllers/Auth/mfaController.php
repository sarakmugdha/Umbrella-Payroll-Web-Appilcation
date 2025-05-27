<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class mfaController extends Controller
{
    protected $google2fa;

    public function __construct(Google2fa $google2fa){
        $this->google2fa = $google2fa;
    }
    public function generateQrCode(){

        $user = Auth::user();
        Log::channel('timesheet')->info($user);
        $is_mfa_enabled=$user->is_mfa_enabled;
        if($is_mfa_enabled){
            return response()->json(['status'=>0]);
        }
        $google2fa_secret = $this->google2fa->generateSecretKey();
        $user->mfa_secret = $google2fa_secret;
        $user->save();

        $qrCodeUrl=$this->google2fa->getQRCodeUrl(
            'Paycaddy',
            $user->email,
            $google2fa_secret
        );
        return response()->json(['qrUrl'=>$qrCodeUrl,'status'=>1]);

    }
    public function verifyMFA(Request $request){
        $user = Auth::user();
        Log::channel('timesheet')->info($user);

        $secret = $user->mfa_secret;
        Log::channel('timesheet')->info($request);
        $otp = (int) $request->otp;
        $is_mfa_enabled=$user->is_mfa_enabled;
        if ($this->google2fa->verifyKey($secret, $otp)&& $is_mfa_enabled==0) {

            $user->is_mfa_enabled=1;
            $user->save();

            return response()->json(['status'=>1]);

    }
        if($this->google2fa->verifyKey($secret, $otp)){
            return response()->json(['status'=>1,'role'=>$user->role_id]);
        }
    return response()->json(['status'=>0]);
}
}
