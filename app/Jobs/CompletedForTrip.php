<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\CompletedForTrip as SendEmailTestMail;
use Mail;

class CompletedForTrip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details,$emailcontent)
    {
        // dd($emailcontent);
        $this->details = $details;
        $this->emailcontent = $emailcontent;
        $this->text = $emailcontent['text'];
        $this->title = $emailcontent['title'];
        $this->userName = $emailcontent['userName'];

        Mail::send( 'admin.emails.completed_trip', $this->emailcontent, function( $message ) 
        {
            $message->to($this->details['email'],$this->details['username'])->from( 'admin@admin.com', 'Admin' )->subject($this->details['subject']);
        });
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Mail::send( 'admin.emails.completed_trip', $this->emailcontent, function( $message ) 
        // {
        //     $message->to($this->details['email'],$this->details['username'])->from( 'admin@admin.com', 'Admin' )->subject($this->details['subject']);
        // });
    }
}
