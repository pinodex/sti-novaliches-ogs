<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Extensions\EmailComposer;
use App\Extensions\Settings;
use App\Jobs\Job;

class DeliverGradesJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var string Path to file
     */
    protected $filePath;

    /**
     * @var array File metadata
     */
    protected $metadata;

    /**
     * @var array Array containing email recipient, subject and body
     */
    protected $envelope;

    /**
     * Create a new job instance.
     * 
     * @param string $filePath Path to file
     * @param array $metadata File metadata
     */
    public function __construct($filePath, array $metadata)
    {
        $this->filePath = $filePath;
        $this->metadata = $metadata;

        $recipientEmail = Settings::get('email_delivery_recipient_email');
        $recipientName = Settings::get('email_delivery_recipient_name');
        $subject = Settings::get('email_delivery_subject');
        $body = Settings::get('email_delivery_body');

        if ($recipientEmail && $recipientName && $subject && $body) {
            $this->envelope = [
                'recipient_email'   => $recipientEmail,
                'recipient_name'    => $recipientName,
                'subject'           => $subject,
                'body'              => $body
            ];
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->envelope || !file_exists($this->filePath)) {
            return;
        }

        $client = app('google');

        if (!$client->getAccessToken()) {
            return;
        }

        $user = $client->getService(\Google_Service_Oauth2::class);
        $gmail = $client->getService(\Google_Service_Gmail::class);

        try {
            $userInfo = $user->userinfo->get();
        } catch (\Exception $e) {
            return;
        }

        $message = new \Google_Service_Gmail_Message();
       
        $composer = new EmailComposer();

        $composer->from($userInfo->name, $userInfo->email);
        $composer->to($this->envelope['recipient_name'], $this->envelope['recipient_email']);
        $composer->subject($this->envelope['subject']);

        $composer->textBody($this->envelope['body']);
        $composer->htmlBody('<p>' . $this->envelope['body'] . '</p>');

        $composer->attach($this->filePath, $this->metadata['originalName'], $this->metadata['mimeType']);
        
        $message->setRaw(rtrim(strtr(base64_encode($composer->getRaw()), '+/', '-_'), '='));

        try {
            $gmail->users_messages->send('me', $message);
        } catch (\Exception $ignored) {}
    }
}
