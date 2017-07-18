<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Constraints;

use Symfony\Component\Validator\Constraint;

class PasswordMatch extends Constraint
{
    /**
     * @var string Model class for record check
     */
    public $hash;

    /**
     * @var string Error message
     */
    public $message = 'Password does not match';
}
