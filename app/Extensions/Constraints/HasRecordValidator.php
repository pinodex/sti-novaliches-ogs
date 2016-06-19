<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasRecordValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($constraint->exclude && $constraint->exclude == $value) {
            return;
        }

        $record = call_user_func([$constraint->model, 'where'],
            $constraint->row, $constraint->comparator, $value
        );

        if (!$record->exists()) {
            $this->context->addViolation($constraint->message);
        }
    }
}
