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

use Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Symfony\Component\Form\FormError;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Check if user's role
     * 
     * @param string $role
     * 
     * @return boolean
     */
    protected function isRole($role)
    {
        return $this->user->getRole() == $role;
    }

    /**
     * Return a pretty formatted JSON
     * 
     * @param \JsonSerializable $data Data to JSON encode
     * @param int $code HTTP status code
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function json($data, $code = 200)
    {
        return response()->json($data, $code, [], JSON_PRETTY_PRINT);
    }
}
