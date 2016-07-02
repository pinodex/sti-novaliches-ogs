<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;

class MainController extends Controller
{
    /**
     * Index page
     * 
     * URL: /
     */
    public function index()
    {
        return redirect()->route('auth.login');
    }

    /**
     * Credits page
     * 
     * URL: /credits
     */
    public function credits()
    {
        return view('credits');
    }
}
