<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Symfony\Form;

use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

class FormValidatorExtension extends ValidatorExtension
{
    public function __construct()
    {
        $builder = Validation::createValidatorBuilder();
        
        $builder->setConstraintValidatorFactory(new ConstraintValidatorFactory());
        $builder->setMetadataFactory(new LazyLoadingMetadataFactory(new StaticMethodLoader()));

        parent::__construct($builder->getValidator());
    }
}
