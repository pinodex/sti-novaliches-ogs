<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Help;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MainController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Index page
     * 
     * URL: /
     */
    public function index()
    {
        return view('help/index');
    }

    /**
     * Credits page
     * 
     * URL: /about
     */
    public function about()
    {
        return view('help/about');
    }
}
