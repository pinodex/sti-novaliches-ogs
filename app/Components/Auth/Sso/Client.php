<?php

/*
 * This file is part of the SIS for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Components\Auth\Sso;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;

class Client
{
    const ROUTE_IDENTITY = 'identity';

    /**
     * @var \GuzzleHttp\Client HTTP client
     */
    protected $client;

    /**
     * @var \App\Components\Auth\Sso\Token User token
     */
    protected $token;

    /**
     * Constructs SsoClient
     * 
     * @param App\Components\Auth\Sso\Token $token Token string
     * @param string $base Base URI
     */
    public function __construct(Token $token, $base)
    {
        $this->token = $token;

        $this->client = new HttpClient([
            'base_uri'  => $base,
            'headers' => [
                'Authorization' => $token->getAuthorizationValue()
            ]
        ]);
    }

    /**
     * Begin login
     */
    public function login()
    {
        try {
            $identity = $this->client->get(static::ROUTE_IDENTITY);
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() == '401') {
                $message = 'An error occurred';

                if ($body = $e->getResponse()->getBody()) {
                    try {
                        $response = json_decode($body);

                        if ($response->error) {
                            $message = $response->error->message;
                        }
                    } catch (Exception $ex) { }
                }

                throw new AuthException($message);
            }
        }

        return json_decode($identity->getBody(), true);
    }
}
