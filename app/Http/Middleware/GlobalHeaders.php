<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            'Content-Security-Policy'   => "frame-ancestors 'self' http://stinovaliches.net;",
            'Pragma'                    => 'no-cache',
            'Expires'                   => 'Thu, 9 Sept 1999 09:00:00 GMT',
            'Cache-Control'             => 'no-cache, no-store, max-age=0, must-revalidate',
            'P3P'                       => 'CP="Top-kek"'
        ]);

        return $response;
    }
}
