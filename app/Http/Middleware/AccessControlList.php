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

class AccessControlList
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $role = Auth::user()->getRole();
        $acl = require app_path('acl.php');

        if (!array_key_exists($role, $acl)) {
            abort(403);
        }
        
        $action = explode('@', $request->route()->getActionName());
        $controllerName = $action[0];
        $controllerAction = $action[1];
        $allowedActions = $acl[$role];

        if (array_key_exists($controllerName, $allowedActions) && in_array($controllerAction, $allowedActions[$controllerName])) {
            return $next($request);
        }

        if (in_array($controllerName, $allowedActions)) {
            return $next($request);
        }

        abort(403);
    }
}
