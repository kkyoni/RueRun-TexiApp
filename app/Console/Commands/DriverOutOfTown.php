<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\sendNotification;
use App\Models\LinerideBooking;
use App\Models\LinerideUserBooking;
use App\Models\ParcelDetail;
use App\Models\TransactionDetail;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mail;
use Event;
use Illuminate\Support\Arr;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Stripe\Charge;
use Stripe\Customer;
use App\Models\User, App\Models\Setting;
use App\Models\Preferences;
use App\Models\Notifications;
use App\Models\EmergencyDetails;
Use App\Models\Conversions;
Use App\Models\RatingReviews;
use App\Models\UserDetails;
use App\Models\Promocodes;
use App\Models\CardDetails;
use App\Models\DriverDetails;
Use App\Models\Support;
Use App\Models\SupportCategory;
use App\Models\WalletHistory;
use App\Models\Vehicle;
use App\Models\DriverDocuments, App\Models\Wallet;
use App\Models\DriverVehicleDocument;
use App\Models\State;
use App\Models\City;
use App\Models\UserNotification;
use App\Models\Cms;
use App\Models\RideSetting;
use App\Models\ParcelType;
use App\Models\Service;
use App\Models\CompanyDetail;
use App\Models\Booking;
use App\Models\OutTwonrideBooking;
use App\Models\EmergencyType;
use App\Models\InviteContactList;
use App\Jobs\sendInvitation;
use App\Models\PopupNotification, App\Models\UserReport, App\Models\UserBehavior;
use Response;
class DriverOutOfTown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver:outtown';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Driver Out Of Town';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        $booking_driver = Booking::where('trip_status','accepted')->where('booking_date','=',date('Y-m-d'))->where('booking_start_time','=',date('H:i A'))->where('ride_setting_id','2')->get();
        if(!empty($booking_driver)){
            foreach ($booking_driver as $key => $value) {
                $driver_booking = Booking::where('id',$value['id'])->first();
                $driver_data['message']       = 'Remand Trip';
                $driver_data['type']          = 'accepted';
                $driver_data['booking_id']    = $value->id;
                $driver_data['driver_id']     = $value->driver_id;
                $driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($driver_booking,$driver_data));
            }
        }
        // $a = "jaymin@aistechnolabs.biz";
        // Mail::raw("Get Driver Out Of Town", function($message) use ($a){
        //     $message->from('php2@aistechnolabs.co.uk');
        //     $message->to($a)->subject('Hourly Update');
        // });
    }
}
