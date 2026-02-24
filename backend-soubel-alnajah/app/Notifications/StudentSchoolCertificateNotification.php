<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;


class StudentSchoolCertificateNotification extends Notification
{
    use Queueable;
    public $user;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$year,$namefr,$namear)
    {
        $this->user = $user;
        $this->year = $year;
        $this->namefr = $namefr;
        $this->namear = $namear;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

   
    public function toArray($notifiable)
    {
        return [
            'namefr'=> $this->namefr,
            'namear'=> $this->namear,
            'email'=> $this->user['email'],
            'year'=> $this->year
        ];
    }

}
