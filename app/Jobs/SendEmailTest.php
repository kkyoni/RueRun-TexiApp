<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\SendEmailTest as SendEmailTestMail;
use Mail;


class SendEmailTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $details;

     protected $emailcontent;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details,$emailcontent)
    {
        $this->details = $details;
        $this->emailcontent = $emailcontent;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::send( 'emails.adminNotifiction', $this->emailcontent, function( $message ) 
        {
            $message->to($this->details['email'],$this->details['username'])->from( 'admin@admin.com', 'Admin' )->subject($this->details['subject']);
        });
    }
}
