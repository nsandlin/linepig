<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackNotification extends Notification
{
    use Queueable;

    /**
     * The name of the command.
     *
     * @var string
     */
    protected $commandName;

    /**
     * Create a new notification instance.
     * 
     * @param string $commandName
     *   The name of the command we're running
     *
     * @return void
     */
    public function __construct(string $commandName)
    {
        $this->commandName = $commandName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\SlackMessage
     */
    public function toSlack($notifiable)
    {
        $cmd = $this->commandName;

        return (new SlackMessage)
                    ->from("LinEpig")
                    ->content("Artisan Command: $cmd, has started running.");
    }
}
