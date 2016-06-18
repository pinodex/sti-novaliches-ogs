<?php

namespace App\Http\Middleware;

use Closure;

class GlobalHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        header_remove('X-Powered-By');

        $response->headers->add([
            'X-Frame-Options'   => 'SAMEORIGIN',
            'Pragma'            => 'no-cache',
            'Expires'           => 'Thu, 9 Sept 1999 09:00:00 GMT',
            'Cache-Control'     => 'no-cache, no-store, max-age=0, must-revalidate'
        ]);

        return $response;
    }
}
