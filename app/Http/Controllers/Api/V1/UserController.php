<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers\Api\V1;

use Auth;
use App\Http\Controllers\Controller;
use App\Models\Student;

class UserController extends Controller
{
    /**
     * Show user record
     * 
     * @param string $id Student ID
     * 
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show()
    {
        if (!$this->user) {
            abort(401);
        }

        return $this->json([
            'type' => $this->user->getRole(),
            'data' => $this->user
        ]);
    }
}
