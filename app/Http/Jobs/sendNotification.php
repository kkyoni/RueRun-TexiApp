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

class sendNotification extends Mailable
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
        // dd($details,$emailcontent);
        $this->details = $details;
        $this->emailcontent = $emailcontent;
        $this->text = $emailcontent['text'];
        $this->title = $emailcontent['title'];
        $this->userName = $emailcontent['userName'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function handle()
    {
        Mail::send( 'admin.emails.verify', $data=['title'=>$this->title, 'text'=>$this->text, 'userName'=>$this->userName], function( $message )
        {
            $message->to($this->details['email'],$this->details['username'])->from( 'admin@admin.com', 'Admin' )->subject($this->details['subject']);
        });
    }
}
