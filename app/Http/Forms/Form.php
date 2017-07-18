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

use Symfony\Component\Form\Form as SymfonyForm;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Validator\Validation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Form
{
    /**
     * @var \Symfony\Component\Form\FormBuilder Symfony form builder
     */
    protected $builder;

    /**
     * @var \Illuminate\Database\Eloquent\Model Model instance
     */
    protected $model;

    /**
     * Create a new form instance
     *
     * @param array $data Default form data values
     * @param array $options Form options
     */
    public function __construct(Model $model = null, array $options = [])
    {
        $options['allow_extra_fields'] = true;
        $options['translation_domain'] = false;
        
        $this->builder = app('form.factory')->createNamedBuilder(
            null, Type\FormType::class, null, $options
        );

        if ($model) {
            $this->setModel($model);
        }

        $this->create();
    }

    /**
     * Set model for form
     *
     * @param \Illuminate\Database\Eloquent\Model $model Model instance
     *
     * @return \App\Http\Forms\Form
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        $this->setData($model->toArray());

        return $this;
    }

    /**
     * Set initial data for form
     *
     * @param mixed $data Form data
     *
     * @return \App\Http\Forms\Form
     */
    public function setData($data)
    {
        $this->builder->setData($data);

        return $this;
    }

    /**
     * Add form elements to form. Calls Form->add internally
     *
     * @return \App\Http\Forms\Form
     */
    public function add()
    {
        return call_user_func_array([$this->builder, 'add'], func_get_args());
    }

    /**
     * Get form from form builder. Calls Form->getForm() internally
     *
     * @return \App\Http\Forms\Form
     */
    public function getForm()
    {
        return call_user_func_array([$this->builder, 'getForm'], func_get_args());
    }

    /**
     * Create form. This is called on Form instantiation.
     */
    public function create() {}

    /**
     * Validate form
     * 
     * @param array $data Form data
     * 
     * @return array
     */
    public function validate(array $data)
    {
        $validator = Validation::createValidator();
        $fields = $this->builder->all();
        
        $formErrors = [];

        foreach ($fields as $name => $form) {
            $options = $form->getOptions();
            $value = null;

            if (!array_key_exists('constraints', $options)) {
                continue;
            }

            if (array_key_exists($name, $data)) {
                $value = $data[$name];
            }

            $violations = $validator->validate($value, $options['constraints']);

            if ($violations->count() == 0) {
                continue;
            }

            $formErrors[$name] = $violations;
        }

        foreach ($formErrors as $key => $errors) {
            $formErrors[$key] = [];

            foreach ($errors as $error) {
                $formErrors[$key][] = $error->getMessage();
            }
        }

        return $formErrors;
    }

    /**
     * Generate form select field choices from model collection
     * 
     * @param \Illuminate\Support\Collection $data Model collection
     * @param bool $unassigned Add unassigned option
     * 
     * @return array
     */
    protected function toChoices(Collection $data, $unassigned = false)
    {
        $choices = [];

        if ($unassigned) {
            $choices['Unassigned'] = 0;
        }

        $data->each(function ($value) use (&$choices) {
            $choices[$value->name] = $value->id;
        });

        return $choices;
    }
}
