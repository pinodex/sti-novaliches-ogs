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

use Symfony\Component\Security\Csrf\CsrfToken;

/**
 * Provides static class for CSRF from CSRF service
 */
class Csrf extends Service
{
    /**
     * Generate CSRF token
     *
     * @param string $identifier Token identifier
     *
     * @return Symfony\Component\Security\Csrf\CsrfToken
     */
    public static function generate($identifier, $refresh = false)
    {
        if ($refresh) {
            return self::$app['form.csrf_provider']->refreshToken($identifier);
        }

        return self::$app['form.csrf_provider']->getToken($identifier);
    }

    /**
     * Check if token is valid for the given identifier
     *
     * @param string $identifier Token identifier
     * @param string $token Token string
     *
     * @return boolean
     */
    public static function isValid($identifier, $token)
    {
        $token = new CsrfToken($identifier, $token);
        return self::$app['form.csrf_provider']->isTokenValid($token);
    }
}
