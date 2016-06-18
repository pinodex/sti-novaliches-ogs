<?php

namespace App\Extensions\User\Roles;

use Illuminate\Contracts\Auth\Authenticatable;

interface UserRoleInterface
{
    /**
     * Retrieve user by ID
     * 
     * @param mixed $identifer User identifier
     * 
     * @return Illuminate\Database\Eloquent\Model
     */
    public function retrieveById($identifier);

    /**
     * Retrieve Authenticatable user by supplied credentials
     * 
     * @param array $credentials User credentials
     * 
     * @throws \App\Exceptions\AuthException When there's an error with login
     * 
     * @return Authenticatable
     */
    public function retrieveByCredentials(array $credentials);

    /**
     * Validate credentials against a Authenticatable user
     * 
     * @param Authenticatable $user Authenticatable user
     * @param array $credentials User credentials
     * 
     * @throws \App\Exceptions\AuthException When there's an error with login
     * 
     * @return boolean
     */
    public function validateCredentials(Authenticatable $user, array $credentials);
}
