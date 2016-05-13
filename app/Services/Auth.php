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

/**
 * Provides auth service
 */
class Auth extends Service
{
    /**
     * @var User Current logged in user. Used to cache user for current request.
     */
    private static $user;

    /**
     * Authenticate user by username and password
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return \App\Services\User
     */
    public static function attempt($username, $password)
    {
        foreach (self::$app['auth.providers'] as $provider) {
            $user = (new $provider)->attempt($username, $password);

            if ($user) {
                self::$user = $user;
                return $user;
            }
        }
    }

    /**
     * Get logged in user
     *
     * @return User
     */
    public static function user()
    {
        if (self::$user) {
            return self::$user;
        }

        if ($sessionUser = Session::get('user')) {
            try {
                self::$user = User::createFromSerializedData($sessionUser);
                return self::$user;
            } catch (\Exception $e) {
                Session::remove('user');
            }
        }
    }

    /**
     * Is user allowed at controller
     * 
     * @param string $controller Controller FQCN
     * 
     * @return boolean
     */
    public static function isAllowed($controller)
    {
        $controllerClass = $controller[0];
        $controllerAction = $controller[1];

        if (is_object($controllerClass)) {
            $controllerClass = get_class($controllerClass);
        }

        if ($user = self::user()) {
            $provider = $user->getProvider();
            $allowedControllers = array();

            foreach ($provider->getAllowedControllers() as $key => $value) {
                if (is_integer($key)) {
                    $allowedControllers[] = $value;

                    continue;
                }
                
                $allowedControllers[] = $key;
            }

            $disallowed = array_diff(self::$app['auth.protected_controllers'], $allowedControllers);

            if (in_array($controllerClass, $disallowed)) {
                return false;
            }

            if (array_key_exists($controllerClass, $provider->getAllowedControllers())) {
                $allowedActions = $provider->getAllowedControllers()[$controllerClass];

                if (!in_array($controllerAction, $allowedActions)) {
                    return false;
                }
            }

            return true;
        }

        if (in_array($controllerClass, self::$app['auth.protected_controllers'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Is user logged in or guest?
     *
     * @return boolean
     */
    public static function guest()
    {
        return self::user() === null;
    }

    /**
     * Login user
     *
     * @param App\Models\User $user User model
     */
    public static function login($user)
    {
        Session::set('user', $user->serialize());
    }

    /**
     * Remove session and logout user
     */
    public static function logout()
    {
        Session::remove('user');
    }
}
