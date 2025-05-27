<?php

namespace App\Http\Middleware;

use Closure;
use Http;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthMiddleware
{

    public function handle(Request $request, Closure $next): Response

    {
        Log::channel('timesheet')->info('inmiddleware', [auth()->guard('api')->check()]);
        if (!auth()->guard('api')->check()) {

            $refreshToken = $request->header('refresh_token') ?? '';
            Log::channel('timesheet')->info('inmiddleware', [$refreshToken]);

            $tokenResponse = Http::asForm()->post(env('PASSPORT_URL').'/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => env('PASSPORT_PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSPORT_PASSWORD_CLIENT_SECRET'),
                'refresh_token' => $refreshToken,
                'scope' => '',
            ]);

            if ($tokenResponse->failed()) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            Log::channel('timesheet')->info($tokenResponse->json());
            return response()->json($tokenResponse->json(), 401);
        }
        return $next($request);
    }
}
