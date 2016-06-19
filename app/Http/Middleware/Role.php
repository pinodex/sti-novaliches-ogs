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

use Auth;
use Closure;

class Role
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
        $requiredRoles = array();

        for ($i = 2; $i < func_num_args(); $i++) { 
            $requiredRoles[] = func_get_arg($i);
        }

        if (in_array(Auth::user()->getRole(), $requiredRoles)) {
            return $next($request);
        }

        abort(403);
    }
}
