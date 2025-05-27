<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
       
        if (!Auth::check() || !$this->hasRole($role)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }

    /**
     * Check if the authenticated user has the given role
     *
     * @param string $role
     * @return bool
     */
    private function hasRole($role)
    {   
        return Auth::user()->role_id == $role;
    }
}
?>