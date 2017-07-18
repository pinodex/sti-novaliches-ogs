<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs;

    /**
     * Add the permission middleware
     */
    public function can($permission)
    {
        return $this->middleware('can:' . $permission);
    }

    /**
     * Log action to current logged in user
     * 
     * @param string $action Action code
     * @param array $params Action parameters
     * @param \Illuminate\Http\Request $request Request object
     */
    public function logAction($action, array $params = [], Request $request = null)
    {
        if (Auth::check()) {
            Auth::user()->log($action, $params, $request);
        }
    }
}
