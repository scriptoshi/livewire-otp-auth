<?php

namespace Scriptoshi\LivewireOtpAuth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class OTPNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $otp;
    protected $actionText;
    protected $actionUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($otp, $actionText = 'Verify OTP', $actionUrl = null)
    {
        $this->otp = $otp;
        $this->actionText = $actionText;
        $this->actionUrl = $actionUrl;
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
        $expirationTime = Config::get('livewire-otp-auth.expiration_time', 10);
        
        $mailMessage = (new MailMessage)
            ->subject('Your One-Time Password (OTP)')
            ->line('Your OTP is: ' . $this->otp)
            ->line("This OTP will expire in {$expirationTime} minutes.");

        if ($this->actionUrl) {
            $mailMessage->action($this->actionText, $this->actionUrl);
        }

        return $mailMessage->line('If you did not request this OTP, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'otp' => $this->otp,
        ];
    }
}
