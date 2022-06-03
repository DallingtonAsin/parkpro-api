<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Channels\CustomPaymentDbChannel;


class PaymentMadeNotification extends Notification
{
    use Queueable;
    private $paymentData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [CustomPaymentDbChannel::class]; // return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)                    
        ->name($this->paymentData['name'])
        ->line($this->paymentData['body'])
        ->action($this->paymentData['offerText'])
        ->line($this->paymentData['thanks']);
    }

    /**`
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->paymentData['id'],
            'type' => $this->paymentData['type'],
            'body' => $this->paymentData['body'],
        ];
    }

    /**
 * @return array
 */
        public function toCustomDb()
        {
            return [
                'id' => $this->paymentData['id'],
                'type' => $this->paymentData['type'],
                'body' => $this->paymentData['body'],
            ];
        }


}
