<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompletedForTrip extends Mailable
{
    use Queueable, SerializesModels;
    public $subject;
    public $send_to;
    public $body;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subject = $subject;
        $this->send_to = $send_to;
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('php1@aistechnolabs.co.uk')->subject($this->subject)->view('admin.emails.completed_trip');
        // return $this->from('mail@example.com', 'Mailtrap')
        //     ->subject('Test Queued Email')
        //     ->view('mails.email');
        // return $this->view('view.name');
    }
}
