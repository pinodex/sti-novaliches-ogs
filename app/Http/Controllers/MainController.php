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

class MainController extends Controller
{
    /**
     * Index page
     * 
     * @return mixed
     */
    public function index()
    {
        if (Auth::guest()) {
            return redirect()->route('auth.login');
        }

        $route = Auth::user()->getRedirectRoute();

        return redirect()->route($route);
    }
}
