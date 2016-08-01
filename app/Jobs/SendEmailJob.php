<?php

/*
 * This file is part of the online grades system for STI College Novaliches
 *
 * (c) Raphael Marco <raphaelmarco@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Jobs;

use App\Jobs\Job;
use App\Extensions\Email\Composer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var Composer Email composer instance
     */
    protected $composer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $client = app('google');

            if ($client->isAccessTokenExpired()) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $gmail = $client->getService(\Google_Service_Gmail::class);
        $user = $client->getService(\Google_Service_Oauth2::class);

        $userInfo = $user->userinfo->get();
        $this->composer->from($userInfo->name, $userInfo->email);
        
        $message = new \Google_Service_Gmail_Message();
        $message->setRaw(rtrim(strtr(base64_encode($this->composer->getRaw()), '+/', '-_'), '='));

        $gmail->users_messages->send('me', $message);
    }
}
