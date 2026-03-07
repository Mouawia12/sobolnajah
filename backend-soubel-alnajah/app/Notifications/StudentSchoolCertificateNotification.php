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
    public function __construct($user, array $requestDetails, $namefr, $namear)
    {
        $this->user = $user;
        $this->requestDetails = $requestDetails;
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
            'year'=> $this->requestDetails['year'] ?? null,
            'purpose' => $this->requestDetails['purpose'] ?? null,
            'copies' => $this->requestDetails['copies'] ?? null,
            'preferred_language' => $this->requestDetails['preferred_language'] ?? null,
            'delivery_method' => $this->requestDetails['delivery_method'] ?? null,
            'notes' => $this->requestDetails['notes'] ?? null,
            'requested_at' => $this->requestDetails['requested_at'] ?? now()->toDateTimeString(),
        ];
    }

}
