<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Middleware;

use Auth;
use Closure;
use App\Components\Acl;

class AccessControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $args)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $user = Auth::user();
        $isAllowed = call_user_func_array([Acl::for($user), 'can'], explode(',', $args));

        if ($isAllowed) {
            return $next($request);
        }

        abort(403);
    }
}
