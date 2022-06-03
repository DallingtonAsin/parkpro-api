<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;

    /** * Create a new message instance.
     *  * * @return void */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /** * Build the message.
     *  * * @return $this */
    public function build()
    {
        return $this->markdown('mail.customer_suggestions')
        ->subject($this->data['subject'])
        ->with([
            'subject' => $this->data['subject'],
            'name' => $this->data['name'],
            'reaction' => $this->data['reaction'],
            'email' => $this->data['email'],
            'description' => $this->data['description'],
    ]);
    }
}
