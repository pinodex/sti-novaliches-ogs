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

class Token
{
    /**
     * @var string Client ID
     */
    protected $id;

    /**
     * @var string Client secret
     */
    protected $secret;

    /**
     * @var string Access token code
     */
    protected $code;

    /**
     * Constructs Token
     * 
     * @param string $id Client ID
     * @param string $secret Client secret
     * @param string $code Token value
     */
    public function __construct($id, $secret, $code)
    {
        $this->id = $id;

        $this->secret = $secret;

        $this->code = $code;
    }

    /**
     * Get authorization header value
     * 
     * @return string
     */
    public function getAuthorizationValue()
    {
        return sprintf('SSO-ACCESS-TOKEN id=%s, secret=%s, code=%s',
            $this->id, $this->secret, $this->code
        );
    }
}
