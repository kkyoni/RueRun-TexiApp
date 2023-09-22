<?php

namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\SendEmailTest as SendEmailTestMail;
use Illuminate\Mail\Mailable;

use Mail;

class sendInvitation extends Mailable
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $details;

    protected $emailcontent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details,$emailcontent)
    {
        $this->details = $details;
        $this->emailcontent = $emailcontent;
        $this->text = $emailcontent['text'];
        $this->title = $emailcontent['title'];
        $this->userName = $emailcontent['userName'];
        $this->code = $emailcontent['code'];
        $this->link = $emailcontent['link'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function handle()
    {
        Mail::send( 'emails.sendInvitation', $data=['title'=>$this->title, 'link'=>$this->link , 'code'=>$this->code, 'text'=>$this->text, 'userName'=>$this->details['email']], function( $message )
        {
            $message->to($this->details['email'],$this->details['username'])->from( $this->details['from_email'], 'Admin' )->subject($this->details['subject']);
        });
    }
}
