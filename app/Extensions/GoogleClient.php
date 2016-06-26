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

/**
 * Wrapper for \Google_Client
 */
class GoogleClient extends \Google_Client
{
    /**
     * Get client instance with added scope
     * 
     * @param string $scope Scope
     * 
     * @return GoogleClient
     */
    public function withScope($scope)
    {
        $client = clone $this;
        $client->addScope($scope);

        return $client;
    }

    /**
     * Get client instance with added scopes
     * 
     * @param array $scopes Array of scopes
     * 
     * @return GoogleClient
     */
    public function withScopes(array $scopes)
    {
        $client = clone $this;

        foreach ($scopes as $scope) {
            $client->addScope($scope);
        }

        return $client;
    }

    /**
     * Get service instance with this Google client
     * 
     * @param string $className FQDN to a Google service class
     */
    public function getService($className)
    {
        return new $className($this);
    }
}
