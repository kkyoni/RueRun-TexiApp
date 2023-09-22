<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\PaymentReceipt as SendEmailTestMail;
use Mail;

class PaymentReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details_payment,$emailcontent_payment)
    {
        $this->details_payment = $details_payment;
        $this->emailcontent_payment = $emailcontent_payment;
        $this->text = $emailcontent_payment['text'];
        $this->title = $emailcontent_payment['title'];
        $this->userName = $emailcontent_payment['userName'];
        $this->pick_up_location = $emailcontent_payment['pick_up_location'];
        $this->drop_location = $emailcontent_payment['drop_location'];
        $this->booking_date = $emailcontent_payment['booking_date'];
        $this->ride_name = $emailcontent_payment['ride_name'];
        $this->trip_status = $emailcontent_payment['trip_status'];
        $this->base_fare = $emailcontent_payment['base_fare'];

        Mail::send( 'admin.emails.payment_receipt', $this->emailcontent_payment, function( $message ) 
        {
            $message->to($this->details_payment['email'],$this->details_payment['username'])->from( 'admin@admin.com', 'Admin' )->subject($this->details_payment['subject']);
        });
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
