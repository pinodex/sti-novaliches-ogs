<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Forms;

use Symfony\Component\Form\Extension\Core\Type;
use App\Models\User;

class LoginForm extends Form
{
    public function create()
    {
        $this->add('id', Type\TextType::class, [
            'attr'  => [
                'autofocus' => true
            ]
        ]);
        
        $this->add('password', Type\PasswordType::class);
    }
}
