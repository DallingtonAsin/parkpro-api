<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationMailSender extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($UserData)
    {
        $this->data = $UserData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.user_registration')
        ->subject($this->data['subject'])
        ->with([
            'name' => $this->data['name'],
            'username' => $this->data['username'],
            'password' => $this->data['password'],
            'user_position' => $this->data['user_position'],
            'registra' => $this->data['registra'],
            'registraPosition' => $this->data['registraPosition'],
            'registraEmail' => $this->data['registraEmail'],
            'email' => $this->data['email'],
            'subject' => $this->data['subject'],
            'created_at' => $this->data['created_at'],
            'details' => $this->data['details'],
            'activity' => $this->data['activity'],
    ]);

    }
}
