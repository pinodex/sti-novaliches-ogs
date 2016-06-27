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
 * Allows composition of MIME formatted messages
 */
class EmailComposer
{
    /**
     * @var array Array of email headers
     */
    private $envelope = [];

    /**
     * @var array Array of file attachments
     */
    private $attachments = [];

    /**
     * @var string Email body in text version
     */
    private $textBody;

    /**
     * @var string Email body in html version
     */
    private $htmlBody;

    /**
     * Set mail envelope From address
     * 
     * @param string $name Name
     * @param string $email Email
     */
    public function from($name, $email)
    {
        $this->envelope['From'] = sprintf('%s <%s>', $name, $email);
    }

    /**
     * Set mail envelope To address. For multiple recipients, just call this
     * method for each recipient
     * 
     * @param string $name Name
     * @param string $email Email
     */
    public function to($name, $email)
    {
        if (!isset($this->envelope['To'])) {
            $this->envelope['To'] = [];
        }

        $this->envelope['To'][] = sprintf('%s <%s>', $name, $email);
    }

    /**
     * Set mail envelope subject
     * 
     * @param string $subject Email Subject
     */
    public function subject($subject)
    {
        $this->envelope['Subject'] = $subject;
    }

    /**
     * Set body in plain text
     * 
     * @param string $string Email body text
     */
    public function textBody($string)
    {
        $this->textBody = $string;
    }

    /**
     * Set body in html format
     * 
     * @param string $string Email body html text
     */
    public function htmlBody($string)
    {
        $this->htmlBody = $string;
    }

    /**
     * Add file attachment
     * 
     * @param string $filePath Path to file
     * @param stirng $fileName Name of the file
     * @param string $mimeType MIME type of the file
     */
    public function attach($filePath, $fileName, $mimeType)
    {
        $this->attachments[] = [
            'filePath' => $filePath,
            'fileName' => $fileName,
            'mimeType' => $mimeType
        ];
    }

    /**
     * Get the constructed MIME message
     * 
     * @return string
     */
    public function getRaw()
    {
        $output = '';

        foreach ($this->envelope as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $output .= $key . ': ' . $value . "\r\n";
        }

        $mainBoundary = uniqid();
        $subBoundary = uniqid();

        $output .= 'Content-Type: multipart/mixed; boundary=' . $mainBoundary . "\r\n";
        $output .= "\r\n";

        $output .= '--' . $mainBoundary . "\r\n";
        $output .= 'Content-Type: multipart/alternative; boundary=' . $subBoundary . "\r\n";
        $output .= "\r\n";

        if ($this->textBody) {
            $output .= '--' . $subBoundary . "\r\n";
            $output .= 'Content-Type: text/plain;' . "\r\n";
            $output .= "\r\n";
            
            $output .= $this->textBody;
            $output .= "\r\n";
        }

        if ($this->htmlBody) {
            $output .= '--' . $subBoundary . "\r\n";
            $output .= 'Content-Type: text/html;' . "\r\n";
            $output .= "\r\n";
            
            $output .= $this->htmlBody;
            $output .= "\r\n";
        }

        $output .= '--' . $subBoundary . '--' . "\r\n";

        foreach ($this->attachments as $attachment) {
            $fileContents = file_get_contents($attachment['filePath']);
            $encoding = mb_detect_encoding($fileContents) ?: 'UTF-8';

            $output .= '--' . $mainBoundary . "\r\n";
            $output .= 'Content-Type: ' . $attachment['mimeType'] . '; charset=' . $encoding . '; name="' . $attachment['fileName'] . '"' . "\r\n";
            $output .= 'Content-Disposition: attachment; filename="' . $attachment['fileName'] . '"' . "\r\n";
            $output .= "\r\n";

            $output .= $fileContents . "\r\n";
        }

        $output .= '--' . $mainBoundary . '--' . "\r\n";

        return $output;
    }
}
