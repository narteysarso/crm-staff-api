<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class VerifyStaffMemberBranchMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $staff = Auth::guard('staff')->user();
        
        // return response()->json(compact($staff));
        if(is_null($staff->branch)){
            $error = 'user must belong to a branch or station';
            return response()->json(compact($error));
        }

        return $next($request);
    }
}
