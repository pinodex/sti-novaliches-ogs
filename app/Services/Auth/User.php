<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Generic user class
 * 
 * Represents a user entity.
 * Holds the user model and its provider.
 */
class User
{
    /**
     * User source model
     * 
     * @var Model
     */
    private $model;

    /**
     * User provider
     * 
     * @var object
     */
    private $provider;

    /**
     * User name
     * 
     * @var string
     */
    private $name;

    /**
     * Create User object from serialized data. Used for saved sessions
     * 
     * @param string $serialized Serialized object
     * 
     * @return User
     */
    public static function createFromSerializedData($serialized)
    {
        $data = unserialize($serialized);

        return new User(new $data['provider'],
            call_user_func(array($data['model'], 'find'), $data['userId'])
        );
    }

    /**
     * @param object $provider User provider
     * @param Model $model User source model
     */
    public function __construct($provider, Model $model)
    {
        if (!$model) {
            $exception = new ModelNotFoundException();
            $exception->setModel(get_class($model));

            throw $exception;
        }

        $this->provider = $provider;
        $this->model = $model;
    }

    /**
     * Get user model
     * 
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get user provider
     * 
     * @return object
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Get user name
     * 
     * @return string
     */
    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }

        $this->name = $this->provider->getName($this);
        return $this->name;
    }

    public function getRole()
    {
        return $this->provider->getRole();
    }

    public function serialize()
    {
        return serialize(array(
            'provider' => get_class($this->provider),
            'model' => get_class($this->model),
            'userId' => $this->model->id
        ));
    }
}
