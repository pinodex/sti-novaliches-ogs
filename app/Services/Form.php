<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form as SymfonyForm;
use Symfony\Component\Form\FormError;

/**
 * Provides wrapper and factory for form service
 */
class Form extends Service
{
    /**
     * Create a new form builder
     *
     * @param string $identifier Form identifier
     *
     * @return Symfony\Component\Form\FormBuilder
     */
    public static function create($data = null, array $options = array())
    {
        $form = self::$app['form.factory']->createNamedBuilder(
            null, FormType::class, $data, $options
        );
        
        return $form;
    }

    /**
     * Add flash error message to form
     *
     * @param string $identifer Form identifier
     * @param string $message Error message 
     */
    public static function flashError($identifier, $message)
    {
        self::$app['flashbag']->add('form.' . $identifier . '.errors', $message);
    }

    /**
     * Apply flashed errors to form
     * 
     * @param SymfonyForm $form Form
     * @param string $identifer Form identifer
     */
    public static function handleFlashErrors($identifier, SymfonyForm $form)
    {
        $errors = self::$app['flashbag']->get('form.' . $identifier . '.errors');

        foreach ($errors as $error) {
            $form->addError(new FormError($error));
        }
    }
}
