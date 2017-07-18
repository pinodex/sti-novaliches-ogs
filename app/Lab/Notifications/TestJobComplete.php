<?php

namespace App\Lab\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\Notification;

class TestJobComplete extends Notification
{
    use Queueable;

    public function via($notifiable)
    {
        return ['database'];
    }

    protected function getImage($notifiable)
    {
        return $notifiable->picture['thumb'];
    }

    protected function getIcon($notifiable)
    {
        return 'cog';
    }

    protected function getContent($notifiable)
    {
        return 'Test job completed';
    }
}
