<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class Notification extends BaseNotification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return new MailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'link'      => $this->getLink($notifiable),
            'image'     => $this->getImage($notifiable),
            'icon'      => $this->getIcon($notifiable),
            'content'   => $this->getContent($notifiable)
        ];
    }

    /**
     * Get notification link
     * 
     * @param  mixed  $notifiable
     * @return string
     */
    protected function getLink($notifiable)
    {
        return null;
    }

    /**
     * Get notification image
     * 
     * @param  mixed  $notifiable
     * @return string
     */
    protected function getImage($notifiable)
    {
        return asset('/assets/img/system-thumb.jpg');
    }

    /**
     * Get notification icon
     * 
     * @param  mixed  $notifiable
     * @return string
     */
    protected function getIcon($notifiable)
    {
        return 'circle';
    }

    /**
     * Get notification content
     * 
     * @param  mixed  $notifiable
     * @return string
     */
    protected function getContent($notifiable)
    {
        return 'This is the notification content.';
    }
}
