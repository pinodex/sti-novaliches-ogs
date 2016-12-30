<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Form as SymfonyForm;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Provides wrapper and factory for form service
 */
class Form
{
    /**
     * Create a new form builder
     *
     * @param string $identifier Form identifier
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    public static function create($data = null, array $options = array())
    {
        $options['allow_extra_fields'] = true;
        
        $form = app('form.factory')->createNamedBuilder(
            null, Type\FormType::class, $data, $options
        );
        
        return $form;
    }

    /**
     * Add flash error message to form
     *
     * @param string $identifier Form identifier
     * @param string $message Error message 
     */
    public static function flashError($identifier, $message)
    {
        session()->flash('form_message.' . $identifier, $message);
    }

    /**
     * Apply flashed errors to form
     * 
     * @param SymfonyForm $form Form
     * @param string $identifier Form identifier
     */
    public static function handleFlashErrors($identifier, SymfonyForm $form)
    {
        if ($message = session('form_message.' . $identifier)) {
            $form->addError(new FormError($message));
        }
    }
}
