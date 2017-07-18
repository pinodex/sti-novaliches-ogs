<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class Authenticate
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
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }

            if ($request->getPathInfo() == '/') {
                return redirect()->route('auth.login');    
            }
            
            return redirect()->route('auth.login', [
                'next' => $request->getRequestUri()
            ])->with('message', ['warning', 'You need to login to access this page']);
        }

        return $next($request);
    }
}
