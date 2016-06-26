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
    private $envelope = [];

    private $attachments = [];

    private $textBody;

    private $htmlBody;

    public function from($name, $email)
    {
        $this->envelope['From'] = sprintf('%s <%s>', $name, $email);
    }

    public function to($name, $email)
    {
        if (!isset($this->envelope['To'])) {
            $this->envelope['To'] = [];
        }

        $this->envelope['To'][] = sprintf('%s <%s>', $name, $email);
    }

    public function subject($subject)
    {
        $this->envelope['Subject'] = $subject;
    }

    public function textBody($string)
    {
        $this->textBody = $string;
    }

    public function htmlBody($string)
    {
        $this->htmlBody = $string;
    }

    public function attach($filePath, $fileName, $mimeType)
    {
        $this->attachments[] = [
            'filePath' => $filePath,
            'fileName' => $fileName,
            'mimeType' => $mimeType
        ];
    }

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
