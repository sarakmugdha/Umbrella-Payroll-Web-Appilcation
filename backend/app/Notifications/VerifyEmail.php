<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
class VerifyEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl=$this->VerifyURL($notifiable);
        return (new MailMessage)
        ->subject('Password Set-up Link')
            ->line('please Click the button below to set-up your password')
            ->action('Password Setup', $verificationUrl)
            ->line('Thank you for using our application!')
            ->line('@2025 PayCaddy PVT. LTD.');
    }
    protected function VerifyURL(object $notifiable){
        \Log::error($notifiable->getEmailForVerification());
        $id=$notifiable->getkey();
        $hash=sha1($notifiable->getEmailForVerification());
        $expiry=Carbon::now()->addMinutes(60)->timestamp;
        return "http://localhost:4200/password-setup/$id/$hash?exp=$expiry";
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
