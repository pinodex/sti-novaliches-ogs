<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extensions\Email;

use App\Extensions\Settings;

class GradeDelivery extends Composer
{
    /**
     * {@inheritDoc}
     * 
     * @throws Exception If email delivery is not configured.
     */
    public function __construct()
    {
        $settings = Settings::getAll();

        $isConfigured =
            isset($settings['email_delivery_recipient_email']) &&
            isset($settings['email_delivery_recipient_name']) &&
            isset($settings['email_delivery_subject']) &&
            isset($settings['email_delivery_body']);

        if (!$isConfigured) {
            throw new \Exception('Email delivery settings is not configured.');
        }

        $this->to($settings['email_delivery_recipient_name'], $settings['email_delivery_recipient_email']);
        $this->subject($settings['email_delivery_subject']);

        $this->textBody($settings['email_delivery_body']);
        $this->htmlBody('<p>' . $settings['email_delivery_body'] . '</p>');
    }
}
