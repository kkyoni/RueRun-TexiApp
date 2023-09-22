<?php
namespace App\Http\Controllers\Api;
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
use App\Models\DriverDocuments, App\Models\Wallet,App\Models\BookingNotifications;
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
use App\Models\PopupNotification, App\Models\UserReport, App\Models\UserBehavior, App\Models\CancleBooking;
use Response;
use App\Models\BankDetail;
use Illuminate\Support\Facades\Log;
use App\Models\VehicleBody;
use DateTime;
use DateTimeZone;
class CommonController extends Controller{

    public function __construct()
    {
        // set_time_limit(8000000);
    }

    public function getAuthenticatedUser(){
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }

    /*
    |--------------------------------------------------------------------------
    | All CMS
    |--------------------------------------------------------------------------
    */
    public function CmsPage(Request $request){
        try{
            $allcms = json_decode(strip_tags(Cms::where('status','active')->get()),true);
            return response()->json(['status' => 'success','message' =>'All CMS','data' => $allcms]);
        }catch(Exception $e){
            return response()->json(['status' => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Invite Cms Page
    |--------------------------------------------------------------------------
    */
    public function InviteCmsPage(Request $request){
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }

            $allcms = json_decode(strip_tags(Cms::where('id','7')->where('status','active')->first()),true);
            $allcms['uuid'] = $user->uuid;
            return response()->json(['status' => 'success','message' =>'Invite Contact CMS','data' => $allcms]);
        }catch(Exception $e){
            return response()->json(['status' => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Faq Page
    |--------------------------------------------------------------------------
    */
    public function faqPage(){
        try{
            $faq = Setting::where('code','faq')->first();
            $que = array_keys((array)json_decode(Setting::where('code','faq')->first()->value),true);
            $ans = array_values((array)json_decode(Setting::where('code','faq')->first()->value,true));
            return response()->json(['status' => 'success','message' => $faq->label,'data'      => ['FaqQue' => $que,'FaqAne' => $ans,]]);
        }catch(Exception $e){
            return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Update Token
    |--------------------------------------------------------------------------
    |
    */
    public function updateToken(Request $request){
        $token = $request->header('Authorization');
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $userId = $user->id;
            $token = JWTAuth::refresh(str_replace('Bearer ',"",$token));
            User::where('id',$userId)->update([
                'login_token' => $token,
                ]);
        } catch (Exception $e) {
            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => "error",'code'=>500,'message' => 'Token is Invalid']);
            }else if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                $token = JWTAuth::refresh(str_replace('Bearer ',"",$token));
                return response()->json(['status' => "error",'code'=>$e->getCode(),'message' => 'Token is Expired','token'=>$token]);
            }
        } catch(\Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
            return response()->json(['status' => "error",'code'=>500,'message' => 'Token is Invalid']);
        } catch(\Tymon\JWTAuth\Exceptions\TokenExpiredException $e){
            $token = JWTAuth::refresh(str_replace('Bearer ',"",$token));
            return response()->json(['status' => "error",'code'=>$e->getCode(),'message' => 'Token is Expired','token'=>$token]);
        } catch(JWTAuthException $e){
            return response()->json(['status' => "error",'code'=>$e->getCode(),'message' => $e->getMessage()]);
        }
        return response()->json(['status' => "success",'message' =>"Token Success",'token'=>str_replace('Bearer ',"",$token)]);
    }

    /*
    |--------------------------------------------------------------------------
    | Forgot Password
    |--------------------------------------------------------------------------
    |
    */
    public function forgotPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'type' => 'required',
            ]);
        if ($validator->fails()){
            return response()->json(['status'   => 'error','message'  => $validator->messages()->first()]);
        }
        try{
            $user = User::where(['email'=>$request->email,'user_type'=>$request->type])->first();
            if(!$user){
                return response()->json(['status'    => 'error','message'   => "Invalid e-mail address."]);
            } else {
                $password = Str::random(8);
                $mailData['mail_to']   = $user->email;
                $mailData['to_name']   = $user->first_name;
                $mailData['mail_from']   = 'admin@admin.com';
                $mailData['from_title']  = 'Reset Password';
                $mailData['subject']     = 'Reset Password';
                $data = [
                'data' => $mailData,
                'username'=>$user->first_name,
                'password'=>$password
                ];
                Mail::send('emails.verify', $data, function($message) use($mailData) {
                    $message->subject($mailData['subject']);
                    $message->from($mailData['mail_from'],$mailData['from_title']);
                    $message->to($mailData['mail_to'],$mailData['to_name']);
                });
                if(Mail::failures()) {
                    return response()->json(['status'=>'error','message'=>'Mail failed']);
                }
                $user->password = \Hash::make($password);
                $user->link_code = \Hash::make($password);
                $user->save();
                return response()->json(['status'    => 'success','message'   => 'New password is sent to your mail.',]);
            }
        }catch(Exception $e){
            return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    |
    */
    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email'  => 'required|string|email',
            'password' => 'required|string|min:8',
            ]);
        if($validator->fails()) {
            return response()->json(['status'   => 'error','message'  => $validator->messages()->first()]);
        }
        try{
            $user = User::where('email', $request->email)->where(function ($query) {
                $query->where('user_type','user');
                $query->orwhere('user_type','driver');
                $query->orwhere('user_type','company');
                $query->where('status','active');
            })->first();
            if(!$user){
                return response()->json(['status'    => 'error','message'   => "Invalid e-mail address."]);
            }else{
                $today = Carbon::now();
                $linkEx_time = Carbon::parse($user->link_expire);
                if($today >= $linkEx_time){
                    return response(['status'    => 'error','message'   =>  'Your link is expired.please try again & generate new link.']);
                }else{
                    if(request('password') == $user->link_code){
                        $user->password = bcrypt(request('password'));
                        $user->save();
                        return response()->json(['status'    => 'success','message'   => 'Your Password Changed Successfully.',]);
                    }else{
                        return response()->json(['status'    => 'error','message'   => 'Password you have entered which does not match.',]);
                    }
                }
            }
        }catch(Exception $e){
            return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get State list
    |--------------------------------------------------------------------------
    |
    */
    public function getState(){
        try{
            $state = State::all();
            if(count($state)>0){
                return response()->json(['status'  => 'success','message' => 'You are getting state list successfully','data'    => $state]);
            }else{
                return response()->json(['status'  => 'error','message' => 'State Not Found']);
            }
        }catch(Exception $e){
            return response()->json(['status'  => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get City State Wise
    |--------------------------------------------------------------------------
    |
    */
    public function StatewiseCity(Request $request){
        $validator = Validator::make($request->all(), [
            'state_id'   => 'required',
            ]);
        if($validator->fails()) {
            return response()->json(['status'   => 'error','message'  => $validator->messages()->first()]);
        }
        try{
            $city = City::where('state_id',$request->state_id)->get();
            if(count($city)>0){
                return response()->json(['status'  => 'success','message' => 'You are getting city list successfully','data'    => $city]);
            }else{
                return response()->json(['status'  => 'error','message' => 'City Not Found']);
            }
        }catch(Exception $e){
            return response()->json(['status'  => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Service Type
    |--------------------------------------------------------------------------
    |
    */
    public function getServiceType(){
        try{
            $service_type = Service::where('status','active')->get();
            if(count($service_type)>0){
                return response()->json(['status'  => 'success','message' => 'You are getting service type list successfully','data'    => $service_type,]);
            }else{
                return response()->json(['status'  => 'error','message' => 'Service Type Not Found']);
            }
        }catch(Exception $e){
            return response()->json(['status'  => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Parcel Type
    |--------------------------------------------------------------------------
    |
    */
    public function getParcelType(){
        try{
            $parcel_type = ParcelType::where('status','active')->get();
            if(count($parcel_type)>0){
                return response()->json(['status'  => 'success','message' => 'You are getting parcel type list successfully','data'    => $parcel_type,
                    ]);
            }else{
                return response()->json(['status'  => 'error','message' => 'Parcel Type Not Found']);
            }
        }catch(Exception $e){
            return response()->json(['status'  => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Trip History Detail
    |--------------------------------------------------------------------------
    |
    */
    // public function getTripHistoryDetail(Request $request){
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }

    //         if($request->booking_id){
    //             $trip_id = $request->booking_id;
    //             $tripshistory = Booking::with(['user','driver'])->where('id', $trip_id)->first();
    //             $driver_id = $tripshistory ['driver']->id;
    //             $total_review = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->count();
    //             $avgrating = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->avg('rating');
    //             if(empty($avgrating)){
    //                 $avgrating = 0;
    //             }
    //             if($tripshistory->payment_type == "wallet"){
    //                 $tripshistory['user_card_type'] = "";
    //                 $tripshistory['user_card_number'] = "";
    //                 $tripshistory['user_card_holder_name'] = "";
    //                 $tripshistory['user_card_expiry_month'] = "";
    //                 $tripshistory['user_card_expiry_year'] = "";
    //                 $tripshistory['user_billing_address'] = "";
    //                 $tripshistory['user_bank_name'] = "";
    //                 $tripshistory['user_card_name'] = "";
    //                 $tripshistory['user_cvv'] = "";
    //             } elseif($tripshistory->payment_type == "card") {
    //                 $user_card = CardDetails::where('id',$tripshistory->card_id)->first();
    //                 $tripshistory['user_card_type'] = $user_card->card_type;
    //                 $tripshistory['user_card_number'] = $user_card->card_number;
    //                 $tripshistory['user_card_holder_name'] = $user_card->card_holder_name;
    //                 $tripshistory['user_card_expiry_month'] = $user_card->card_expiry_month;
    //                 $tripshistory['user_card_expiry_year'] = $user_card->card_expiry_year;
    //                 $tripshistory['user_billing_address'] = $user_card->billing_address;
    //                 $tripshistory['user_bank_name'] = $user_card->bank_name;
    //                 $tripshistory['user_card_name'] = $user_card->card_name;
    //                 $tripshistory['user_cvv'] = $user_card->cvv;
    //             } else {
    //                 $tripshistory['user_card_type'] = "";
    //                 $tripshistory['user_card_number'] = "";
    //                 $tripshistory['user_card_holder_name'] = "";
    //                 $tripshistory['user_card_expiry_month'] = "";
    //                 $tripshistory['user_card_expiry_year'] = "";
    //                 $tripshistory['user_billing_address'] = "";
    //                 $tripshistory['user_bank_name'] = "";
    //                 $tripshistory['user_card_name'] = "";
    //                 $tripshistory['user_cvv'] = "";
    //             }
    //             $origin_addresses = $tripshistory->pick_up_location;
    //             $destination_addresses = $tripshistory->drop_location;
    //             $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
    //             $distance_arr = json_decode($distance_data);
    //             $elements = $distance_arr->rows[0]->elements;
    //             $duration = $elements[0]->duration->text;
    //             $distance = $elements[0]->distance->text;

    //             if($tripshistory->trip_status == "cancelled"){
    //                 $discount = 0;
    //                 $total_amount['vehicle_base_fare'] = 0;
    //                 $total_amount['total_fare'] = 0;
    //             } else {
    //                 if(!empty($tripshistory->promo_id)){
    //                     $total_amount = $this->gettotalfare($tripshistory->vehicle_id, $tripshistory->total_km, $tripshistory->promo_id);
    //                     $discount = $total_amount['vehicle_base_fare'] - $total_amount['total_fare'];
    //                 } else {
    //                     $discount = 0;
    //                     $total_amount['vehicle_base_fare'] = $tripshistory->base_fare;
    //                     $total_amount['total_fare'] = (float)$tripshistory->base_fare;
    //                 }
    //             }

    //             $tripshistory['total_fare'] = $total_amount['total_fare'];
    //             $tripshistory['discount'] = $discount;
    //             $tripshistory['payment_amount'] = $total_amount['vehicle_base_fare'];
    //             $tripshistory['driver_total_review'] = $total_review;
    //             $tripshistory['driver_total_review_avg'] = $avgrating;
    //             $tripshistory['duration'] = $duration;
    //             $tripshistory['distance'] = $distance;

    //             return response()->json(['status' => 'success','message' => 'Booking History Details', 'data'=>$tripshistory]);
    //         }else if($request->parcel_id){
    //             $trip_id = $request->parcel_id;
    //             $tripshistory = ParcelDetail::with(['user','driver'])->where('id', $trip_id)->first();

    //             if(!empty($tripshistory ['driver'])){
    //                 $driver_id = $tripshistory ['driver']->id;
    //             }else{
    //                 $driver_id = 0;
    //             }

    //             $total_review = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->count();
    //             $avgrating = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->avg('rating');
    //             if(empty($avgrating)){
    //                 $avgrating = 0;
    //             }
    //             if($tripshistory->payment_type == "wallet"){
    //                 $tripshistory['user_card_type'] = "";
    //                 $tripshistory['user_card_number'] = "";
    //                 $tripshistory['user_card_holder_name'] = "";
    //                 $tripshistory['user_card_expiry_month'] = "";
    //                 $tripshistory['user_card_expiry_year'] = "";
    //                 $tripshistory['user_billing_address'] = "";
    //                 $tripshistory['user_bank_name'] = "";
    //                 $tripshistory['user_card_name'] = "";
    //                 $tripshistory['user_cvv'] = "";
    //             } elseif($tripshistory->payment_type == "card") {
    //                 $user_card = CardDetails::where('id',$tripshistory->card_id)->first();
    //                 $tripshistory['user_card_type'] = $user_card->card_type;
    //                 $tripshistory['user_card_number'] = $user_card->card_number;
    //                 $tripshistory['user_card_holder_name'] = $user_card->card_holder_name;
    //                 $tripshistory['user_card_expiry_month'] = $user_card->card_expiry_month;
    //                 $tripshistory['user_card_expiry_year'] = $user_card->card_expiry_year;
    //                 $tripshistory['user_billing_address'] = $user_card->billing_address;
    //                 $tripshistory['user_bank_name'] = $user_card->bank_name;
    //                 $tripshistory['user_card_name'] = $user_card->card_name;
    //                 $tripshistory['user_cvv'] = $user_card->cvv;
    //             } else {
    //                 $tripshistory['user_card_type'] = "";
    //                 $tripshistory['user_card_number'] = "";
    //                 $tripshistory['user_card_holder_name'] = "";
    //                 $tripshistory['user_card_expiry_month'] = "";
    //                 $tripshistory['user_card_expiry_year'] = "";
    //                 $tripshistory['user_billing_address'] = "";
    //                 $tripshistory['user_bank_name'] = "";
    //                 $tripshistory['user_card_name'] = "";
    //                 $tripshistory['user_cvv'] = "";
    //             }
    //             $origin_addresses = $tripshistory->pick_up_location;
    //             $destination_addresses = $tripshistory->drop_location;
    //             $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
    //             $distance_arr = json_decode($distance_data);
    //             $elements = $distance_arr->rows[0]->elements;
    //             $duration = $elements[0]->duration->text;
    //             $distance = $elements[0]->distance->text;

    //             if($tripshistory->trip_status == "cancelled"){
    //                 $discount = 0;
    //                 $total_amount['vehicle_base_fare'] = 0;
    //                 $total_amount['total_fare'] = 0;
    //             } else {
    //                 if(!empty($tripshistory->promo_id)){
    //                     $total_amount = $this->gettotalfare($tripshistory->vehicle_id, $tripshistory->total_km, $tripshistory->promo_id);
    //                     $discount = $total_amount['vehicle_base_fare'] - $total_amount['total_fare'];
    //                 } else {
    //                     $discount = 0;
    //                     $total_amount['vehicle_base_fare'] = $tripshistory->base_fare;
    //                     $total_amount['total_fare'] = (float)$tripshistory->base_fare;
    //                 }
    //             }

    //             $tripshistory['total_fare'] = $total_amount['total_fare'];
    //             $tripshistory['discount'] = $discount;
    //             $tripshistory['payment_amount'] = $total_amount['vehicle_base_fare'];
    //             $tripshistory['driver_total_review'] = $total_review;
    //             $tripshistory['driver_total_review_avg'] = $avgrating;
    //             $tripshistory['duration'] = $duration;
    //             $tripshistory['distance'] = $distance;
    //             $tripshistory['trip_status'] = $tripshistory->parcel_status;

    //             return response()->json(['status' => 'success','message' => 'Parcel Booking History Details', 'data'=>$tripshistory]);
    //         }else if($request->shuttle_id){
    //             $trip_id = $request->shuttle_id;

    //             if($user->user_type === 'user'){
    //                 $tripshistory = LinerideUserBooking::with(['user','driver','driver_shuttle'])
    //                     ->where('id', $trip_id)
    //                     //->orWhere('shuttle_driver_id', $trip_id)
    //                     ->first();
    //             }else{
    //                 $tripshistory = LinerideUserBooking::with(['user','driver','driver_shuttle'])
    //                     ->where('shuttle_driver_id', $trip_id)
    //                     ->where('id', $request->shuttle_user_id)
    //                     ->first();
    //             }

    //             if(!empty($tripshistory ['driver'])){
    //                 $driver_id = $tripshistory ['driver']->id;
    //             }else{
    //                 $driver_id = 0;
    //             }

    //             $total_review = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->count();
    //             $avgrating = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->avg('rating');
    //             if(empty($avgrating)){
    //                 $avgrating = 0;
    //             }
    //             if($tripshistory->payment_type == "wallet"){
    //                 $tripshistory['user_card_type'] = "";
    //                 $tripshistory['user_card_number'] = "";
    //                 $tripshistory['user_card_holder_name'] = "";
    //                 $tripshistory['user_card_expiry_month'] = "";
    //                 $tripshistory['user_card_expiry_year'] = "";
    //                 $tripshistory['user_billing_address'] = "";
    //                 $tripshistory['user_bank_name'] = "";
    //                 $tripshistory['user_card_name'] = "";
    //                 $tripshistory['user_cvv'] = "";
    //             } elseif($tripshistory->payment_type == "card") {
    //                 $user_card = CardDetails::where('id',$tripshistory->card_id)->first();
    //                 $tripshistory['user_card_type'] = $user_card->card_type;
    //                 $tripshistory['user_card_number'] = $user_card->card_number;
    //                 $tripshistory['user_card_holder_name'] = $user_card->card_holder_name;
    //                 $tripshistory['user_card_expiry_month'] = $user_card->card_expiry_month;
    //                 $tripshistory['user_card_expiry_year'] = $user_card->card_expiry_year;
    //                 $tripshistory['user_billing_address'] = $user_card->billing_address;
    //                 $tripshistory['user_bank_name'] = $user_card->bank_name;
    //                 $tripshistory['user_card_name'] = $user_card->card_name;
    //                 $tripshistory['user_cvv'] = $user_card->cvv;
    //             } else {
    //                 $tripshistory['user_card_type'] = "";
    //                 $tripshistory['user_card_number'] = "";
    //                 $tripshistory['user_card_holder_name'] = "";
    //                 $tripshistory['user_card_expiry_month'] = "";
    //                 $tripshistory['user_card_expiry_year'] = "";
    //                 $tripshistory['user_billing_address'] = "";
    //                 $tripshistory['user_bank_name'] = "";
    //                 $tripshistory['user_card_name'] = "";
    //                 $tripshistory['user_cvv'] = "";
    //             }
    //             if($user->user_type === 'driver'){
    //                 $driver_ride = LinerideBooking::find($request->shuttle_id);
    //                 $origin_addresses = $driver_ride->pick_up_location;
    //                 $destination_addresses = $driver_ride->drop_location;
    //             }else{
    //                 $origin_addresses = $tripshistory->pick_up_location;
    //                 $destination_addresses = $tripshistory->drop_location;
    //             }

    //             $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
    //             $distance_arr = json_decode($distance_data);
    //             $elements = $distance_arr->rows[0]->elements;
    //             $duration = $elements[0]->duration->text;
    //             $distance = $elements[0]->distance->text;

    //             if($tripshistory->trip_status == "cancelled"){
    //                 $discount = 0;
    //                 $total_amount['vehicle_base_fare'] = 0;
    //                 $total_amount['total_fare'] = 0;
    //             } else {
    //                 if(!empty($tripshistory->promo_id)){
    //                     $total_amount = $this->gettotalfare($tripshistory->driver->vehicle_id, $tripshistory->total_distance, $tripshistory->promo_id);
    //                     $discount = $total_amount['vehicle_base_fare'] - $total_amount['total_fare'];
    //                 } else {
    //                     if($user->user_type === 'user'){
    //                         $total_amount['vehicle_base_fare'] = $tripshistory->total_amount;
    //                         $total_amount['total_fare'] = (float)$tripshistory->total_amount;
    //                     }else if($tripshistory->driver->driver_model){
    //                         $total_amount['vehicle_base_fare'] = $tripshistory->driver->driver_model->base_fare;
    //                         $total_amount['total_fare'] = (float)$tripshistory->driver->driver_model->base_fare;
    //                     }else{
    //                         $total_amount['vehicle_base_fare'] = $tripshistory->base_fare;
    //                         $total_amount['total_fare'] = (float)$tripshistory->base_fare;
    //                     }
    //                     $discount = 0;
    //                 }
    //             }

    //             $tripshistory['total_fare'] = $total_amount['total_fare'];
    //             $tripshistory['discount'] = $discount;
    //             $tripshistory['payment_amount'] = $total_amount['vehicle_base_fare'];
    //             $tripshistory['driver_total_review'] = $total_review;
    //             $tripshistory['driver_total_review_avg'] = $avgrating;
    //             $tripshistory['duration'] = $duration;
    //             $tripshistory['distance'] = $distance;
    //             $tripshistory['trip_status'] = $tripshistory->trip_status;

    //             return response()->json(['status' => 'success','message' => 'Shuttle Booking History Details', 'data'=>$tripshistory]);
    //         }

    //     } catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    //     }
    // }

    public function getTripHistoryDetail(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }

            if($request->booking_id){
                $trip_id = $request->booking_id;
                $tripshistory = Booking::with(['user','driver'])->where('id', $trip_id)->first();
                if(empty($tripshistory->driver_id)){
                    return response()->json(['status'=>'error','message' => 'Driver is not assigned'],200);
                }
                $driver_id = $tripshistory['driver']->id;
                $total_review = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->count();
                $avgrating = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->avg('rating');
                if(empty($avgrating)){
                    $avgrating = 0;
                }
                if($tripshistory->payment_type == "wallet"){
                    $tripshistory['user_card_type'] = "";
                    $tripshistory['user_card_number'] = "";
                    $tripshistory['user_card_holder_name'] = "";
                    $tripshistory['user_card_expiry_month'] = "";
                    $tripshistory['user_card_expiry_year'] = "";
                    $tripshistory['user_billing_address'] = "";
                    $tripshistory['user_bank_name'] = "";
                    $tripshistory['user_card_name'] = "";
                    $tripshistory['user_cvv'] = "";
                } elseif($tripshistory->payment_type == "card") {
                    $user_card = CardDetails::where('id',$tripshistory->card_id)->first();
                    $tripshistory['user_card_type'] = @$user_card->card_type;
                    $tripshistory['user_card_number'] = @$user_card->card_number;
                    $tripshistory['user_card_holder_name'] = @$user_card->card_holder_name;
                    $tripshistory['user_card_expiry_month'] = @$user_card->card_expiry_month;
                    $tripshistory['user_card_expiry_year'] = @$user_card->card_expiry_year;
                    $tripshistory['user_billing_address'] = @$user_card->billing_address;
                    $tripshistory['user_bank_name'] = @$user_card->bank_name;
                    $tripshistory['user_card_name'] = @$user_card->card_name;
                    $tripshistory['user_cvv'] = @$user_card->cvv;
                } else {
                    $tripshistory['user_card_type'] = "";
                    $tripshistory['user_card_number'] = "";
                    $tripshistory['user_card_holder_name'] = "";
                    $tripshistory['user_card_expiry_month'] = "";
                    $tripshistory['user_card_expiry_year'] = "";
                    $tripshistory['user_billing_address'] = "";
                    $tripshistory['user_bank_name'] = "";
                    $tripshistory['user_card_name'] = "";
                    $tripshistory['user_cvv'] = "";
                }

                $lat= $tripshistory->driver->latitude; //latitude
                $lng= $tripshistory->driver->longitude; //longitude
                $driver_address_trip = $this->getaddress($lat,$lng);
                // dd($driver_address_trip);
                if($driver_address_trip){
                    $origin_addresses = $driver_address_trip;
                }else{
                    echo "Not found";
                }

                // $origin_addresses = $tripshistory->pick_up_location;
                $destination_addresses = $tripshistory->drop_location;
                $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                $distance_arr = json_decode($distance_data);
                $elements = $distance_arr->rows[0]->elements;
                if($elements[0]->status == "ZERO_RESULTS"){
                    $duration = "0";
                    $distance = "0";
                }else{
                    $duration = $elements[0]->duration->text;
                    $distance = $elements[0]->distance->text;
                }

                if($tripshistory->trip_status == "cancelled"){
                    $discount = 0;
                    $total_amount['vehicle_base_fare'] = 0;
                    $total_amount['total_fare'] = 0;
                } else {
                    if(!empty($tripshistory->promo_id)){
                        $total_amount = $this->gettotalfare($tripshistory->vehicle_id, $tripshistory->total_km, $tripshistory->promo_id);
                        $discount = $total_amount['vehicle_base_fare'] - $total_amount['total_fare'];
                    } else {
                        $discount = 0;
                        $total_amount['vehicle_base_fare'] = $tripshistory->base_fare;
                        $total_amount['total_fare'] = (float)$tripshistory->base_fare;
                    }
                }

                $tripshistory['total_fare'] = $total_amount['total_fare'];
                $tripshistory['discount'] = $discount;
                $tripshistory['payment_amount'] = $total_amount['vehicle_base_fare'];
                $tripshistory['driver_total_review'] = $total_review;
                $tripshistory['driver_total_review_avg'] = $avgrating;
                $tripshistory['duration'] = $duration;
                $tripshistory['distance'] = $distance;
                $tripshistory['booking_date'] = date("m-d-Y", strtotime($tripshistory['booking_date']));
                return response()->json(['status' => 'success','message' => 'Booking History Details', 'data'=>$tripshistory]);
            }else if($request->parcel_id){
                $trip_id = $request->parcel_id;
                $tripshistory = ParcelDetail::with(['user','driver'])->where('id', $trip_id)->first();

                if(!empty($tripshistory ['driver'])){
                    $driver_id = $tripshistory ['driver']->id;
                }else{
                    $driver_id = 0;
                }

                $total_review = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->count();
                $avgrating = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->avg('rating');
                if(empty($avgrating)){
                    $avgrating = 0;
                }
                if($tripshistory->payment_type == "wallet"){
                    $tripshistory['user_card_type'] = "";
                    $tripshistory['user_card_number'] = "";
                    $tripshistory['user_card_holder_name'] = "";
                    $tripshistory['user_card_expiry_month'] = "";
                    $tripshistory['user_card_expiry_year'] = "";
                    $tripshistory['user_billing_address'] = "";
                    $tripshistory['user_bank_name'] = "";
                    $tripshistory['user_card_name'] = "";
                    $tripshistory['user_cvv'] = "";
                } elseif($tripshistory->payment_type == "card") {
                    $user_card = CardDetails::where('id',$tripshistory->card_id)->first();
                    $tripshistory['user_card_type'] = $user_card->card_type;
                    $tripshistory['user_card_number'] = $user_card->card_number;
                    $tripshistory['user_card_holder_name'] = $user_card->card_holder_name;
                    $tripshistory['user_card_expiry_month'] = $user_card->card_expiry_month;
                    $tripshistory['user_card_expiry_year'] = $user_card->card_expiry_year;
                    $tripshistory['user_billing_address'] = $user_card->billing_address;
                    $tripshistory['user_bank_name'] = $user_card->bank_name;
                    $tripshistory['user_card_name'] = $user_card->card_name;
                    $tripshistory['user_cvv'] = $user_card->cvv;
                } else {
                    $tripshistory['user_card_type'] = "";
                    $tripshistory['user_card_number'] = "";
                    $tripshistory['user_card_holder_name'] = "";
                    $tripshistory['user_card_expiry_month'] = "";
                    $tripshistory['user_card_expiry_year'] = "";
                    $tripshistory['user_billing_address'] = "";
                    $tripshistory['user_bank_name'] = "";
                    $tripshistory['user_card_name'] = "";
                    $tripshistory['user_cvv'] = "";
                }
                $lat= $tripshistory->driver->latitude; //latitude
                $lng= $tripshistory->driver->longitude; //longitude
                // dd($lng);

                $driver_address_trip = $this->getaddress($lat,$lng);
                // dd($driver_address_trip);
                if($driver_address_trip){
                    $origin_addresses = $driver_address_trip;
                }else{
                    echo "Not found";
                }
                // $origin_addresses = $tripshistory->pick_up_location;
                $destination_addresses = $tripshistory->drop_location;
                $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                $distance_arr = json_decode($distance_data);
                $elements = $distance_arr->rows[0]->elements;
                $duration = $elements[0]->duration->text;
                $distance = $elements[0]->distance->text;

                if($tripshistory->trip_status == "cancelled"){
                    $discount = 0;
                    $total_amount['vehicle_base_fare'] = 0;
                    $total_amount['total_fare'] = 0;
                } else {
                    if(!empty($tripshistory->promo_id)){
                        $total_amount = $this->gettotalfare($tripshistory->vehicle_id, $tripshistory->total_km, $tripshistory->promo_id);
                        $discount = $total_amount['vehicle_base_fare'] - $total_amount['total_fare'];
                    } else {
                        $discount = 0;
                        $total_amount['vehicle_base_fare'] = $tripshistory->base_fare;
                        $total_amount['total_fare'] = (float)$tripshistory->base_fare;
                    }
                }

                $tripshistory['total_fare'] = $total_amount['total_fare'];
                $tripshistory['discount'] = $discount;
                $tripshistory['payment_amount'] = $total_amount['vehicle_base_fare'];
                $tripshistory['driver_total_review'] = $total_review;
                $tripshistory['driver_total_review_avg'] = $avgrating;
                $tripshistory['duration'] = $duration;
                $tripshistory['distance'] = $distance;
                $tripshistory['trip_status'] = $tripshistory->parcel_status;
                $tripshistory['booking_date'] = date("m-d-Y", strtotime($tripshistory['booking_date']));
                return response()->json(['status' => 'success','message' => 'Parcel Booking History Details', 'data'=>$tripshistory]);
            }else if($request->shuttle_id){
                $trip_id = $request->shuttle_id;
                // dd($trip_id);

                if($user->user_type === 'user'){
                    $tripshistory = LinerideUserBooking::with(['user','driver','driver_shuttle'])
                    ->where('id', $trip_id)
                        //->orWhere('shuttle_driver_id', $trip_id)
                    ->first();
                }else{
                    $tripshistory = LinerideUserBooking::with(['user','driver','driver_shuttle'])
                    ->where('id', $trip_id)
                    ->where('shuttle_driver_id', $request->shuttle_user_id)
                    ->first();
                }
                // dd($tripshistory);
                if(!empty($tripshistory ['driver'])){
                    $driver_id = $tripshistory ['driver']->id;
                }else{
                    $driver_id = 0;
                }

                $total_review = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->count();
                $avgrating = RatingReviews::where('from_user_id',$driver_id)->orWhere('to_user_id',$driver_id)->avg('rating');
                if(empty($avgrating)){
                    $avgrating = 0;
                }
                if($tripshistory->payment_type == "wallet"){
                    $tripshistory['user_card_type'] = "";
                    $tripshistory['user_card_number'] = "";
                    $tripshistory['user_card_holder_name'] = "";
                    $tripshistory['user_card_expiry_month'] = "";
                    $tripshistory['user_card_expiry_year'] = "";
                    $tripshistory['user_billing_address'] = "";
                    $tripshistory['user_bank_name'] = "";
                    $tripshistory['user_card_name'] = "";
                    $tripshistory['user_cvv'] = "";
                } elseif($tripshistory->payment_type == "card") {
                    $user_card = CardDetails::where('id',$tripshistory->card_id)->first();
                    $tripshistory['user_card_type'] = $user_card->card_type;
                    $tripshistory['user_card_number'] = $user_card->card_number;
                    $tripshistory['user_card_holder_name'] = $user_card->card_holder_name;
                    $tripshistory['user_card_expiry_month'] = $user_card->card_expiry_month;
                    $tripshistory['user_card_expiry_year'] = $user_card->card_expiry_year;
                    $tripshistory['user_billing_address'] = $user_card->billing_address;
                    $tripshistory['user_bank_name'] = $user_card->bank_name;
                    $tripshistory['user_card_name'] = $user_card->card_name;
                    $tripshistory['user_cvv'] = $user_card->cvv;
                } else {
                    $tripshistory['user_card_type'] = "";
                    $tripshistory['user_card_number'] = "";
                    $tripshistory['user_card_holder_name'] = "";
                    $tripshistory['user_card_expiry_month'] = "";
                    $tripshistory['user_card_expiry_year'] = "";
                    $tripshistory['user_billing_address'] = "";
                    $tripshistory['user_bank_name'] = "";
                    $tripshistory['user_card_name'] = "";
                    $tripshistory['user_cvv'] = "";
                }
                if($user->user_type === 'driver'){
                    $driver_ride = LinerideBooking::find($request->shuttle_user_id);
                    $origin_addresses = $driver_ride->pick_up_location;
                    $destination_addresses = $driver_ride->drop_location;
                }else{
                    $origin_addresses = $tripshistory->pick_up_location;
                    $destination_addresses = $tripshistory->drop_location;
                }
                $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                // $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                $distance_arr = json_decode($distance_data);
                $elements = $distance_arr->rows[0]->elements;
                $duration = $elements[0]->duration->text;
                $distance = $elements[0]->distance->text;

                if($tripshistory->trip_status == "cancelled"){
                    $discount = 0;
                    $total_amount['vehicle_base_fare'] = 0;
                    $total_amount['total_fare'] = 0;
                } else {
                    if(!empty($tripshistory->promo_id)){
                        $total_amount = $this->gettotalfare($tripshistory->driver->vehicle_id, $tripshistory->total_distance, $tripshistory->promo_id);
                        $discount = $total_amount['vehicle_base_fare'] - $total_amount['total_fare'];
                    } else {
                        if($user->user_type === 'user'){
                            $total_amount['vehicle_base_fare'] = $tripshistory->total_amount;
                            $total_amount['total_fare'] = (float)$tripshistory->total_amount;
                        }else if($tripshistory->driver->driver_model){
                            $total_amount['vehicle_base_fare'] = $tripshistory->driver->driver_model->base_fare;
                            $total_amount['total_fare'] = (float)$tripshistory->driver->driver_model->base_fare;
                        }else{
                            $total_amount['vehicle_base_fare'] = $tripshistory->base_fare;
                            $total_amount['total_fare'] = (float)$tripshistory->base_fare;
                        }
                        $discount = 0;
                    }
                }

                $tripshistory['total_fare'] = $total_amount['total_fare'];
                $tripshistory['discount'] = $discount;
                $tripshistory['payment_amount'] = $total_amount['vehicle_base_fare'];
                $tripshistory['driver_total_review'] = $total_review;
                $tripshistory['driver_total_review_avg'] = $avgrating;
                $tripshistory['duration'] = $duration;
                $tripshistory['distance'] = $distance;
                $tripshistory['trip_status'] = $tripshistory->trip_status;
                $tripshistory['booking_date'] = date("m-d-Y", strtotime($tripshistory['booking_date']));
                return response()->json(['status' => 'success','message' => 'Shuttle Booking History Details', 'data'=>$tripshistory]);
            }

        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Distance
    |--------------------------------------------------------------------------
    |
    */
    public function distance($lat1, $lon1, $lat2, $lon2) {
        $unit = Setting::where('code','distance_in')->first();
        $latitudeFrom = $lat1;
        $longitudeFrom = $lon1;
        $latitudeTo = $lat2;
        $longitudeTo = $lon2;
        //Calculate distance from latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $miles = round($miles, 2);
        // $miles = number_format($miles, 2);
        $unit = strtoupper($unit->value);
        if ($unit === "KM") {
            $miles = $miles * 1.609344;
            return round($miles);
        }else {
            return round($miles);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Total Fare
    |--------------------------------------------------------------------------
    |
    */
    public function gettotalfare($vehicle_id='', $distance='', $promo_id=''){
        try{
            $commission_rate = Setting::where('code','comission_rate')->first();
            $date = date('m/d/Y');
            $promo_code = Promocodes::where('id', $promo_id)->first();
            $vehicle = Vehicle::where('id', $vehicle_id)->first();
            if(!empty($vehicle)){
                $vehicle_base_fare = (int)$vehicle->price_per_km * (int)$distance;
            }else{
                $vehicle_base_fare = 0;
            }
            // add admin commission
            $fare = (float)$vehicle_base_fare + ((float)$vehicle_base_fare * (float)$commission_rate->value) / 100;
            $admin_commision = ((float)$vehicle_base_fare * (float)$commission_rate->value) / 100;
            // calculate promo code offer
            if(!empty($promo_code)){
                $fare = (float)$fare - (((float)($fare)*(float)$promo_code->amount)/100);
            }else{
                $fare = (float)$fare;
            }
            $data['vehicle_base_fare'] =  $vehicle_base_fare;
            $data['admin_commision'] =  $admin_commision;
            $data['total_fare'] =  round($fare);
            return $data;
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Preferences
    |--------------------------------------------------------------------------
    |
    */
    public function getAllPreferences(){
        try {
            $preferences = Preferences::get();
            if(count($preferences) != 0){
                return response()->json(['status' => 'success','message' => 'All Preferences', 'data'=>$preferences]);
            }else{
                return response()->json(['status' => 'error','message' => 'No Preferences not found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Trip Cancelled
    |--------------------------------------------------------------------------
    |
    */
    // public function tripCancelled(Request $request){
    //     $validation_array =[
    //         'booking_id'       => 'nullable'
    //     ];
    //     $validation = Validator::make($request->all(),$validation_array);
    //     if($validation->fails()){
    //         return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
    //     }
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         if($request->booking_id){
    //             $trip_id = $request->booking_id;
    //             $tripdetail = Booking::where('id', $trip_id)->first();

    //             if($tripdetail->trip_status == "on_going"){
    //                 return response()->json(['status' => 'error','message' => "Ongoing trip can't be cancelled"]);
    //             }

    //             if(!empty($tripdetail)){
    //                 if($request->extra_notes){
    //                     $extra_notes = $request->extra_notes;
    //                     Booking::where('id', $tripdetail->id)->update(['trip_status'=>'cancelled', 'extra_notes' => $extra_notes]);
    //                 }else{
    //                     Booking::where('id', $tripdetail->id)->update(['trip_status'=>'cancelled']);
    //                 }

    //                 $user_deatil = User::where('id',$tripdetail->user_id)->first();
    //                 $driver_deatil = User::where('id',$tripdetail->driver_id)->first();
    //                 // notification for user
    //                 $user_data['message'] =  'Booking Cancelled Successfully';
    //                 $user_data['type'] = 'trip_cancelled';
    //                 $user_data['user_id'] = $tripdetail->user_id;
    //                 $user_data['title']        =  'RueRun';
    //                 $user_data['sound']        = 'default';
    //                 $user_data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                 // notification for driver
    //                 $driver_data['message'] =  'Trip Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
    //                 $driver_data['type'] = 'trip_cancelled';
    //                 $driver_data['driver_id'] = $tripdetail->driver_id;
    //                 $driver_data['title']     =  'RueRun';
    //                 $driver_data['sound']     = 'default';
    //                 $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
    //                 if($user->id == $tripdetail->user_id){
    //                     $touserid = $tripdetail->driver_id;
    //                 }else{
    //                     $touserid = $tripdetail->user_id;
    //                 }
    //                 UserNotification::where('booking_id', $trip_id)->update([
    //                     'sent_from_user' =>$tripdetail->user_id,
    //                     'sent_to_user' => $tripdetail->driver_id,
    //                     'notification_for' => "cancel",
    //                     'title' => "Booking cancelled",
    //                     'description' => "Your Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);
    //                 // UserNotification::create([
    //                 //     'sent_from_user' => $user->id,
    //                 //     'sent_to_user' => $touserid,
    //                 //     'booking_id' => $trip_id,
    //                 //     'notification_for' => "cancel",
    //                 //     'title' => "Booking cancelled",
    //                 //     'description' => "Your Booking cancelled Successfully",
    //                 //     'admin_flag' => "0",
    //                 // ]);

    //                 PopupNotification::create([
    //                     'from_user_id' => $user->id,
    //                     'to_user_id' => $touserid,
    //                     'title' => 'Booking Cancelled',
    //                     'description' => 'Booking has been cancelled Successfully',
    //                     'date' => Carbon::now()->format('d-m-Y'),
    //                     'time' => Carbon::now()->format('H:i A'),
    //                     'booking_id' => $tripdetail->id,
    //                 ]);
    //                 return response()->json(['status' => 'success','message' => 'Booking has been cancelled Successfully', 'data'=>Booking::find($trip_id)]);
    //             }else{
    //                 return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    //             }
    //         }else if($request->parcel_id){

    //             $trip_id = $request->parcel_id;
    //             $tripdetail = ParcelDetail::where('id', $trip_id)->first();

    //             if($tripdetail->parcel_status == "on_going"){
    //                 return response()->json(['status' => 'error','message' => "Ongoing trip can't be cancelled"]);
    //             }

    //             if(!empty($tripdetail)){
    //                 if($request->extra_notes){
    //                     $extra_notes = $request->extra_notes;
    //                     ParcelDetail::where('id', $tripdetail->id)->update(['parcel_status'=>'cancelled', 'extra_notes' => $extra_notes]);
    //                 }else{
    //                     ParcelDetail::where('id', $tripdetail->id)->update(['parcel_status'=>'cancelled']);
    //                 }

    //                 $user_deatil = User::where('id',$tripdetail->user_id)->first();
    //                 $driver_deatil = User::where('id',$tripdetail->driver_id)->first();
    //                 // notification for user
    //                 $user_data['message'] =  'Parcel Detail Booking Cancelled Successfully';
    //                 $user_data['type'] = 'trip_cancelled';
    //                 $user_data['user_id'] = $tripdetail->user_id;
    //                 $user_data['title']        =  'RueRun';
    //                 $user_data['sound']        = 'default';
    //                 $user_data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                 // notification for driver
    //                 $driver_data['message'] =  'Trip Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
    //                 $driver_data['type'] = 'trip_cancelled';
    //                 $driver_data['driver_id'] = $tripdetail->driver_id;
    //                 $driver_data['title']     =  'RueRun';
    //                 $driver_data['sound']     = 'default';
    //                 $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
    //                 if($user->id == $tripdetail->user_id){
    //                     $touserid = $tripdetail->driver_id;
    //                 }else{
    //                     $touserid = $tripdetail->user_id;
    //                 }
    //                 UserNotification::where('parcel_id', $trip_id)->update([
    //                     'sent_from_user' => $tripdetail->user_id,
    //                     'sent_to_user' => $tripdetail->driver_id,
    //                     'notification_for' => "cancel",
    //                     'title' => "Parcel Detail Booking cancelled",
    //                     'description' => "Your Parcel Detail Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);
    //                 // UserNotification::create([
    //                 //     'sent_from_user' => $user->id,
    //                 //     'sent_to_user' => $touserid,
    //                 //     'booking_id' => $trip_id,
    //                 //     'notification_for' => "cancel",
    //                 //     'title' => "Parcel Detail Booking cancelled",
    //                 //     'description' => "Your Parcel Detail Booking cancelled Successfully",
    //                 //     'admin_flag' => "0",
    //                 // ]);

    //                 PopupNotification::create([
    //                     'from_user_id' => $user->id,
    //                     'to_user_id' => $touserid,
    //                     'title' => 'Parcel Detail Booking Cancelled',
    //                     'description' => 'Parcel Detail Booking has been cancelled Successfully',
    //                     'date' => Carbon::now()->format('d-m-Y'),
    //                     'time' => Carbon::now()->format('H:i A'),
    //                     'booking_id' => $tripdetail->id,
    //                 ]);
    //                 return response()->json(['status' => 'success','message' => 'Parcel Detail Booking has been cancelled Successfully', 'data'=>ParcelDetail::find($trip_id)]);
    //             }else{
    //                 return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    //             }

    //         }else if($request->shuttle_id){
    //             $tripdetail = LinerideUserBooking::with(['user','driver'])->where('id', $request->shuttle_id)->first();
    //             if(empty($tripdetail)){
    //                 $tripdetail = LinerideBooking::with(['driver'])->where('id', $request->shuttle_id)->first();
    //             }

    //             if($request->extra_notes){
    //                 $extra_notes = $request->extra_notes;
    //             }else{
    //                 $extra_notes = '';
    //             }

    //             if($user->user_type == 'user'){
    //                 LinerideUserBooking::where('user_id', $user->id)->where('id', $request->shuttle_id)
    //                     ->update([
    //                         'trip_status'   => 'cancelled',
    //                         'end_time'      =>  Carbon::now()->format('H:i A'),
    //                         'extra_notes'   =>  $extra_notes
    //                     ]);

    //                 $user_deatil = $tripdetail->user;
    //                 $driver_deatil = $tripdetail->driver;
    //                 // notification for user
    //                 $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
    //                 $user_data['type']          =   'trip_cancelled';
    //                 $user_data['user_id']       =   $tripdetail->user_id;
    //                 $user_data['title']         =   'RueRun';
    //                 $user_data['sound']         =   'default';
    //                 $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                 // notification for driver
    //                 $driver_data['message']         =  'Shuttle Booking Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
    //                 $driver_data['type']            =   'trip_cancelled';
    //                 $driver_data['driver_id']       =   $tripdetail->driver_id;
    //                 $driver_data['title']           =   'RueRun';
    //                 $driver_data['sound']           =   'default';
    //                 $driver_data['notification']    =   Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
    //                 if($user->id == $tripdetail->user_id){
    //                     $touserid = $tripdetail->driver_id;
    //                 }else{
    //                     $touserid = $tripdetail->user_id;
    //                 }

    //                 UserNotification::where('shuttle_id', $tripdetail->id)->update([
    //                     'sent_from_user' => $tripdetail->user_id,
    //                     'sent_to_user' => $tripdetail->driver_id,
    //                     'notification_for' => "cancel",
    //                     'title' => "Shuttle Booking cancelled",
    //                     'description' => "Your Shuttle Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);
    //                 // UserNotification::create([
    //                 //     'sent_from_user'    => $user->id,
    //                 //     'sent_to_user'      => $touserid,
    //                 //     'shuttle_id'        => $tripdetail->id,
    //                 //     'notification_for'  => "cancel",
    //                 //     'title'             => "Shuttle Booking cancelled",
    //                 //     'description'       => "Your Shuttle Booking cancelled Successfully",
    //                 //     'admin_flag'        => "0",
    //                 // ]);

    //                 PopupNotification::create([
    //                     'from_user_id'      => $user->id,
    //                     'to_user_id'        => $touserid,
    //                     'title'             => 'Shuttle Booking cancelled',
    //                     'description'       => 'Shuttle Booking has been cancelled Successfully',
    //                     'date'              => Carbon::now()->format('Y-m-d'),
    //                     'time'              => Carbon::now()->format('H:i A'),
    //                     'shuttle_id'        => $tripdetail->id,
    //                 ]);
    //                 return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>LinerideUserBooking::find($tripdetail->id)]);
    //             }else{
    //                 LinerideUserBooking::where('driver_id', $user->id)
    //                     ->where('shuttle_driver_id', $request->shuttle_id)
    //                     ->where('id', $request->shuttle_user_id)
    //                     ->whereNotIn('trip_status', ['completed','cancelled'])
    //                     ->update([
    //                         'trip_status'   => 'cancelled',
    //                         'end_time'      =>  Carbon::now()->format('H:i A'),
    //                         'extra_notes'   =>  $extra_notes
    //                     ]);
    //                 $shuttle_detail = LinerideUserBooking::with(['user','driver'])->where('id', $request->shuttle_user_id)->first();
    //                 if($user->id == $shuttle_detail->driver_id){
    //                     $touserid = $shuttle_detail->user_id;
    //                 }else{
    //                     $touserid = $shuttle_detail->driver_id;
    //                 }

    //                 $user_deatil = $shuttle_detail->user;
    //                 $driver_deatil = $shuttle_detail->driver;
    //                 // notification for user
    //                 $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
    //                 $user_data['type']          =   'trip_cancelled';
    //                 $user_data['user_id']       =   $touserid;
    //                 $user_data['title']         =   'RueRun';
    //                 $user_data['sound']         =   'default';
    //                 $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                 UserNotification::where('shuttle_id', $tripdetail->id)->update([
    //                     'sent_from_user' => $shuttle_detail->user,
    //                     'sent_to_user' => $shuttle_detail->driver,
    //                     'notification_for' => "cancel",
    //                     'title' => "Shuttle Booking cancelled",
    //                     'description' => "Your Shuttle Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);

    //                 // UserNotification::create([
    //                 //     'sent_from_user'    => $user->id,
    //                 //     'sent_to_user'      => $touserid,
    //                 //     'shuttle_id'        => $tripdetail->id,
    //                 //     'notification_for'  => "cancel",
    //                 //     'title'             => "Shuttle Booking cancelled",
    //                 //     'description'       => "Your Shuttle Booking cancelled Successfully",
    //                 //     'admin_flag'        => "0",
    //                 // ]);

    //                 PopupNotification::create([
    //                     'from_user_id'      => $user->id,
    //                     'to_user_id'        => $touserid,
    //                     'title'             => 'Shuttle Booking cancelled',
    //                     'description'       => 'Shuttle Booking has been cancelled Successfully',
    //                     'date'              => Carbon::now()->format('Y-m-d'),
    //                     'time'              => Carbon::now()->format('H:i A'),
    //                     'shuttle_id'        => $tripdetail->id,
    //                 ]);
    //                 return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>$shuttle_detail]);
    //             }
    //         }else if($request->shuttle_driver_id){
    //             $tripdetail = LinerideBooking::with(['driver'])->where('id', $request->shuttle_driver_id)->first();

    //             if($request->extra_notes){
    //                 $extra_notes = $request->extra_notes;
    //             }else{
    //                 $extra_notes = '';
    //             }
    //             LinerideBooking::where('driver_id', $user->id)->where('id', $request->shuttle_driver_id)
    //                 ->update([
    //                     'trip_status'   => 'cancelled',
    //                     'end_time'      =>  Carbon::now()->format('H:i A'),
    //                     'extra_notes'   =>  $extra_notes
    //                 ]);
    //             LinerideUserBooking::where('driver_id', $user->id)
    //                 ->where('shuttle_driver_id', $request->shuttle_driver_id)
    //                 ->whereNotIn('trip_status', ['completed','cancelled'])
    //                 ->update([
    //                     'trip_status'   => 'cancelled',
    //                     'end_time'      =>  Carbon::now()->format('H:i A'),
    //                     'extra_notes'   =>  $extra_notes
    //                 ]);

    //             $getallshuttleuser = LinerideUserBooking::with(['user','driver'])->where('driver_id', $user->id)
    //                 ->where('shuttle_driver_id', $request->shuttle_driver_id)
    //                 ->where('trip_status' , 'cancelled')->get();

    //             if(sizeof($getallshuttleuser) > 0){
    //                 foreach($getallshuttleuser as $shuttle){
    //                     $user_deatil = $shuttle->user;
    //                     $driver_deatil = $shuttle->driver;
    //                     // notification for user
    //                     $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
    //                     $user_data['type']          =   'trip_cancelled';
    //                     $user_data['user_id']       =   $shuttle->user_id;
    //                     $user_data['title']         =   'RueRun';
    //                     $user_data['sound']         =   'default';
    //                     $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                     // notification for driver
    //                     $driver_data['message']         =  'Shuttle Booking Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
    //                     $driver_data['type']            =   'trip_cancelled';
    //                     $driver_data['driver_id']       =   $shuttle->driver_id;
    //                     $driver_data['title']           =   'RueRun';
    //                     $driver_data['sound']           =   'default';
    //                     $driver_data['notification']    =   Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
    //                     if($user->id == $tripdetail->user_id){
    //                         $touserid = $tripdetail->driver_id;
    //                     }else{
    //                         $touserid = $tripdetail->user_id;
    //                     }

    //                     UserNotification::where('shuttle_id', $tripdetail->id)->update([
    //                     'sent_from_user' => $tripdetail->driver_id,
    //                     'sent_to_user' => $tripdetail->user_id,
    //                     'notification_for' => "cancel",
    //                     'title' => "Shuttle Booking cancelled",
    //                     'description' => "Your Shuttle Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);
    //                     // UserNotification::create([
    //                     //     'sent_from_user'    => $user->id,
    //                     //     'sent_to_user'      => $touserid,
    //                     //     'shuttle_id'        => $tripdetail->id,
    //                     //     'notification_for'  => "cancel",
    //                     //     'title'             => "Shuttle Booking cancelled",
    //                     //     'description'       => "Your Shuttle Booking cancelled Successfully",
    //                     //     'admin_flag'        => "0",
    //                     // ]);

    //                     PopupNotification::create([
    //                         'from_user_id'      => $user->id,
    //                         'to_user_id'        => $touserid,
    //                         'title'             => 'Shuttle Booking cancelled',
    //                         'description'       => 'Shuttle Booking has been cancelled Successfully',
    //                         'date'              => Carbon::now()->format('Y-m-d'),
    //                         'time'              => Carbon::now()->format('H:i A'),
    //                         'shuttle_id'        => $tripdetail->id,
    //                     ]);
    //                 }
    //             }
    //             return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>LinerideBooking::find($request->shuttle_driver_id)]);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    //     }
    // }
    public function tripCancelled(Request $request){
        $validation_array =[
        'booking_id'       => 'nullable'
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            if($request->booking_id){
                $trip_id = $request->booking_id;
                $tripdetail = Booking::with(['car_details'])->where('id', $trip_id)->first();
                if($tripdetail->trip_status == "on_going"){
                    return response()->json(['status' => 'error','message' => "Ongoing trip can't be cancelled",'data' =>(object)$tripdetail]);
                }
                if(!empty($tripdetail->out_town_id)){
                    if($tripdetail->seats == "vip"){
                        $outTownBooking = OutTwonrideBooking::where('id',$tripdetail->out_town_id)->first();
                        $total_seat_available = $outTownBooking->seat_available + $outTownBooking->seat_booked;
                        $total_seat_booked = "0";
                        OutTwonrideBooking::where('id', $tripdetail->out_town_id)->update(['seat_available'=>$total_seat_available, 'seat_booked' => $total_seat_booked]);

                    } else {
                        $outTownBooking = OutTwonrideBooking::where('id',$tripdetail->out_town_id)->first();
                        $total_seat_available = $outTownBooking->seat_available + $tripdetail->seats;
                        $total_seat_booked = $outTownBooking->seat_booked + $tripdetail->seats;
                        OutTwonrideBooking::where('id', $tripdetail->out_town_id)->update(['seat_available'=>$total_seat_available, 'seat_booked' => $total_seat_booked]);
                    }
                }

                if(!empty($tripdetail)){
                    if($user->user_type == "user"){
                        $tripdetail = Booking::with('card_details')->where('id', $tripdetail->id)->first();
                        if($tripdetail->trip_status == "accepted" && $tripdetail->payment_status == "pending") {
                            /*$date1 = $request->current_date;
                            $time1 = $request->current_time;
                            $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date1.$time1);
                            $to_time1 = $new_time->format('Y-m-d H:i:s');
                            $to_time = strtotime($to_time1);

                            $date = $tripdetail->booking_date;
                            $time = $tripdetail->booking_start_time;
                            $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date.$time);
                            $from_time1 = $new_time->format('Y-m-d H:i:s');
                            $from_time = strtotime($from_time1);
                            $diff = round(abs($to_time - $from_time) / 60,6);*/
                            $date1 = $request->current_date;
                            $time1 = $request->current_time;
                            $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date1.$time1);
                            $to_time = $new_time->format('Y-m-d H:i:s');

                            $date = $tripdetail->booking_date;
                            $time = $tripdetail->booking_start_time;
                            $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date.$time);
                            $from_time = $new_time->format('Y-m-d H:i:s');
                            $start_time = Carbon::parse($from_time);
                            $end_time   = Carbon::parse($to_time);
                            $mins = $end_time->diffInMinutes($start_time, true);
                            // $diff = $mins/60;
                            $diff = $mins;
                            if($diff >= 3){
                                $booking_fee = Setting::where('code','booking_fee')->first();
                                $bas_fee = Setting::where('code','bas_fee')->first();
                                $teamp = $booking_fee->value + $bas_fee->value;
                                $tripdetail['booking_fee'] = $booking_fee->value;
                                $tripdetail['bas_fee'] = $bas_fee->value;
                                $tripdetail['booking_total_fee'] = (string)(5 + ((10/100)*$teamp));
                                return response()->json(['status' => 'error','message' => 'You need to pay cancellation charge.', 'data'=>(object)$tripdetail]);
                            }
                        } 
                    }
                    if($request->extra_notes){
                        $extra_notes = $request->extra_notes;
                        Booking::where('id', $tripdetail->id)->update(['trip_status'=>'cancelled', 'payment_status'=>'cancelled','admin_comm_status'=>'cancelled','tip_amount_status'=>'cancelled','extra_notes' => $extra_notes]);
                    }else{
                        Booking::where('id', $tripdetail->id)->update(['trip_status'=>'cancelled','payment_status'=>'cancelled','admin_comm_status'=>'cancelled','tip_amount_status'=>'cancelled']);
                    }

                    $user_deatil = User::where('id',$tripdetail->user_id)->first();
                    $driver_deatil = User::where('id',$tripdetail->driver_id)->first();
                    // notification for user
                    /*$user_data['message'] =  'Booking Cancelled Successfully';
                    $user_data['type'] = 'trip_cancelled';
                    $user_data['user_id'] = $tripdetail->user_id;
                    $user_data['title']        =  'RueRun';
                    $user_data['sound']        = 'default';
                    $user_data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));*/

                    // notification for driver
                    $driver_data['message'] =  'Trip Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
                    $driver_data['type'] = 'trip_cancelled';
                    $driver_data['driver_id'] = $tripdetail->driver_id;
                    $driver_data['title']     =  'RueRun';
                    $driver_data['sound']     = 'default';
                    $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
                    if($user->id == $tripdetail->user_id){
                        $touserid = $tripdetail->driver_id;
                    }else{
                        $touserid = $tripdetail->user_id;
                    }
                    if($user->user_type == "user"){
                        UserNotification::where('booking_id', $trip_id)->update([
                            // 'sent_from_user' =>$tripdetail->user_id,
                            // 'sent_to_user' => $tripdetail->driver_id,
                            'notification_for' => "cancel",
                            'title' => "Booking cancelled",
                            'description' => "Your Booking cancelled Successfully",
                            'admin_flag' => "0",
                            ]);
                    } else {
                        $UserNotification_checking = UserNotification::where('booking_id', $trip_id)->where('sent_to_user',$tripdetail->driver_id)->first();
                        UserNotification::where('id', $UserNotification_checking->id)->update([
                            'sent_from_user' =>$tripdetail->user_id,
                            'sent_to_user' => $tripdetail->driver_id,
                            'notification_for' => "cancel",
                            'title' => "Booking cancelled",
                            'description' => "Your Booking cancelled Successfully",
                            'admin_flag' => "0",
                            ]);
                    }
                    // UserNotification::where('booking_id', $trip_id)->where('sent_to_user',$tripdetail->driver_id)->update([
                    //     'sent_from_user' =>$tripdetail->user_id,
                    //     'sent_to_user' => $tripdetail->driver_id,
                    //     'notification_for' => "cancel",
                    //     'title' => "Booking cancelled",
                    //     'description' => "Your Booking cancelled Successfully",
                    //     'admin_flag' => "0",
                    // ]);
                    // UserNotification::create([
                    //     'sent_from_user' => $user->id,
                    //     'sent_to_user' => $touserid,
                    //     'booking_id' => $trip_id,
                    //     'notification_for' => "cancel",
                    //     'title' => "Booking cancelled",
                    //     'description' => "Your Booking cancelled Successfully",
                    //     'admin_flag' => "0",
                    // ]);

                    PopupNotification::create([
                        'from_user_id' => $user->id,
                        'to_user_id' => $touserid,
                        'title' => 'Booking Cancelled',
                        'description' => 'Booking has been cancelled Successfully',
                        'date' => Carbon::now()->format('d-m-Y'),
                        'time' => Carbon::now()->format('H:i A'),
                        'booking_id' => $tripdetail->id,
                        ]);
                    return response()->json(['status' => 'success','message' => 'Booking has been cancelled Successfully', 'data'=>Booking::find($trip_id)]);
                }else{
                    return response()->json(['status' => 'error','message' => 'Something went Wrong']);
                }
            }else if($request->parcel_id){

                $trip_id = $request->parcel_id;
                $tripdetail = ParcelDetail::where('id', $trip_id)->first();

                if($tripdetail->parcel_status == "on_going"){
                    return response()->json(['status' => 'error','message' => "Ongoing trip can't be cancelled",]);
                }

                if(!empty($tripdetail)){
                    if($request->extra_notes){
                        $extra_notes = $request->extra_notes;
                        ParcelDetail::where('id', $tripdetail->id)->update(['parcel_status'=>'cancelled', 'extra_notes' => $extra_notes]);
                    }else{
                        ParcelDetail::where('id', $tripdetail->id)->update(['parcel_status'=>'cancelled']);
                    }

                    $user_deatil = User::where('id',$tripdetail->user_id)->first();
                    $driver_deatil = User::where('id',$tripdetail->driver_id)->first();
                    // notification for user
                    $user_data['message'] =  'Parcel Detail Booking Cancelled Successfully';
                    $user_data['type'] = 'trip_cancelled';
                    $user_data['user_id'] = $tripdetail->user_id;
                    $user_data['title']        =  'RueRun';
                    $user_data['sound']        = 'default';
                    $user_data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

                    // notification for driver
                    $driver_data['message'] =  'Trip Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
                    $driver_data['type'] = 'trip_cancelled';
                    $driver_data['driver_id'] = $tripdetail->driver_id;
                    $driver_data['title']     =  'RueRun';
                    $driver_data['sound']     = 'default';
                    $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
                    if($user->id == $tripdetail->user_id){
                        $touserid = $tripdetail->driver_id;
                    }else{
                        $touserid = $tripdetail->user_id;
                    }
                    UserNotification::where('parcel_id', $trip_id)->where('sent_to_user',$tripdetail->driver_id)->update([
                        'sent_from_user' => $tripdetail->user_id,
                        'sent_to_user' => $tripdetail->driver_id,
                        'notification_for' => "cancel",
                        'title' => "Parcel Detail Booking cancelled",
                        'description' => "Your Parcel Detail Booking cancelled Successfully",
                        'admin_flag' => "0",
                        ]);
                    // UserNotification::create([
                    //     'sent_from_user' => $user->id,
                    //     'sent_to_user' => $touserid,
                    //     'booking_id' => $trip_id,
                    //     'notification_for' => "cancel",
                    //     'title' => "Parcel Detail Booking cancelled",
                    //     'description' => "Your Parcel Detail Booking cancelled Successfully",
                    //     'admin_flag' => "0",
                    // ]);

                    PopupNotification::create([
                        'from_user_id' => $user->id,
                        'to_user_id' => $touserid,
                        'title' => 'Parcel Detail Booking Cancelled',
                        'description' => 'Parcel Detail Booking has been cancelled Successfully',
                        'date' => Carbon::now()->format('d-m-Y'),
                        'time' => Carbon::now()->format('H:i A'),
                        'booking_id' => $tripdetail->id,
                        ]);
                    return response()->json(['status' => 'success','message' => 'Parcel Detail Booking has been cancelled Successfully', 'data'=>ParcelDetail::find($trip_id)]);
                }else{
                    return response()->json(['status' => 'error','message' => 'Something went Wrong']);
                }

            }else if($request->shuttle_id){
                $tripdetail = LinerideUserBooking::with(['user','driver'])->where('id', $request->shuttle_id)->first();
                if(empty($tripdetail)){
                    $tripdetail = LinerideBooking::with(['driver'])->where('id', $request->shuttle_id)->first();
                }

                if($request->extra_notes){
                    $extra_notes = $request->extra_notes;
                }else{
                    $extra_notes = '';
                }

                if($user->user_type == 'user'){
                    LinerideUserBooking::where('user_id', $user->id)->where('id', $request->shuttle_id)
                    ->update([
                        'trip_status'   => 'cancelled',
                        'end_time'      =>  Carbon::now()->format('H:i A'),
                        'extra_notes'   =>  $extra_notes
                        ]);

                    $user_deatil = $tripdetail->user;
                    $driver_deatil = $tripdetail->driver;
                    // notification for user
                    $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
                    $user_data['type']          =   'trip_cancelled';
                    $user_data['user_id']       =   $tripdetail->user_id;
                    $user_data['title']         =   'RueRun';
                    $user_data['sound']         =   'default';
                    $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

                    // notification for driver
                    $driver_data['message']         =  'Shuttle Booking Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
                    $driver_data['type']            =   'trip_cancelled';
                    $driver_data['driver_id']       =   $tripdetail->driver_id;
                    $driver_data['title']           =   'RueRun';
                    $driver_data['sound']           =   'default';
                    $driver_data['notification']    =   Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
                    if($user->id == $tripdetail->user_id){
                        $touserid = $tripdetail->driver_id;
                    }else{
                        $touserid = $tripdetail->user_id;
                    }

                    UserNotification::where('shuttle_id', $tripdetail->id)->where('sent_to_user',$tripdetail->driver_id)->update([
                        'sent_from_user' => $tripdetail->user_id,
                        'sent_to_user' => $tripdetail->driver_id,
                        'notification_for' => "cancel",
                        'title' => "Shuttle Booking cancelled",
                        'description' => "Your Shuttle Booking cancelled Successfully",
                        'admin_flag' => "0",
                        ]);
                    // UserNotification::create([
                    //     'sent_from_user'    => $user->id,
                    //     'sent_to_user'      => $touserid,
                    //     'shuttle_id'        => $tripdetail->id,
                    //     'notification_for'  => "cancel",
                    //     'title'             => "Shuttle Booking cancelled",
                    //     'description'       => "Your Shuttle Booking cancelled Successfully",
                    //     'admin_flag'        => "0",
                    // ]);

                    PopupNotification::create([
                        'from_user_id'      => $user->id,
                        'to_user_id'        => $touserid,
                        'title'             => 'Shuttle Booking cancelled',
                        'description'       => 'Shuttle Booking has been cancelled Successfully',
                        'date'              => Carbon::now()->format('Y-m-d'),
                        'time'              => Carbon::now()->format('H:i A'),
                        'shuttle_id'        => $tripdetail->id,
                        ]);
                    return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>LinerideUserBooking::find($tripdetail->id)]);
                }else{
                    $shuttle_user_id = LinerideUserBooking::where('driver_id', $user->id)->where('id', $request->shuttle_id)->first();
                    LinerideUserBooking::where('driver_id', $user->id)
                    ->where('shuttle_driver_id', $shuttle_user_id)
                    ->where('id', $request->shuttle_id)
                    ->whereNotIn('trip_status', ['completed','cancelled'])
                    ->update([
                        'trip_status'   => 'cancelled',
                        'end_time'      =>  Carbon::now()->format('H:i A'),
                        'extra_notes'   =>  $extra_notes
                        ]);
                        // dd($shuttle_user_id);
                    $shuttle_detail = LinerideUserBooking::with(['user','driver'])->where('id', $request->shuttle_id)->first();

                    // dd($shuttle_detail);
                    if($user->id == $shuttle_detail->driver_id){
                        $touserid = $shuttle_detail->user_id;
                    }else{
                        $touserid = $shuttle_detail->driver_id;
                    }

                    $user_deatil = $shuttle_detail->user;
                    $driver_deatil = $shuttle_detail->driver;
                    // notification for user
                    $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
                    $user_data['type']          =   'trip_cancelled';
                    $user_data['user_id']       =   $touserid;
                    $user_data['title']         =   'RueRun';
                    $user_data['sound']         =   'default';
                    $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

                    UserNotification::where('shuttle_id', $tripdetail->id)->where('sent_to_user',$tripdetail->driver_id)->update([
                        'sent_from_user' => $shuttle_detail->user,
                        'sent_to_user' => $shuttle_detail->driver,
                        'notification_for' => "cancel",
                        'title' => "Shuttle Booking cancelled",
                        'description' => "Your Shuttle Booking cancelled Successfully",
                        'admin_flag' => "0",
                        ]);

                    // UserNotification::create([
                    //     'sent_from_user'    => $user->id,
                    //     'sent_to_user'      => $touserid,
                    //     'shuttle_id'        => $tripdetail->id,
                    //     'notification_for'  => "cancel",
                    //     'title'             => "Shuttle Booking cancelled",
                    //     'description'       => "Your Shuttle Booking cancelled Successfully",
                    //     'admin_flag'        => "0",
                    // ]);

                    PopupNotification::create([
                        'from_user_id'      => $user->id,
                        'to_user_id'        => $touserid,
                        'title'             => 'Shuttle Booking cancelled',
                        'description'       => 'Shuttle Booking has been cancelled Successfully',
                        'date'              => Carbon::now()->format('Y-m-d'),
                        'time'              => Carbon::now()->format('H:i A'),
                        'shuttle_id'        => $tripdetail->id,
                        ]);
                    return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>$shuttle_detail]);
                }
            }else if($request->shuttle_driver_id){
                $tripdetail = LinerideBooking::with(['driver'])->where('id', $request->shuttle_driver_id)->first();

                if($request->extra_notes){
                    $extra_notes = $request->extra_notes;
                }else{
                    $extra_notes = '';
                }
                LinerideBooking::where('driver_id', $user->id)->where('id', $request->shuttle_driver_id)
                ->update([
                    'trip_status'   => 'cancelled',
                    'end_time'      =>  Carbon::now()->format('H:i A'),
                    'extra_notes'   =>  $extra_notes
                    ]);
                LinerideUserBooking::where('driver_id', $user->id)
                ->where('shuttle_driver_id', $request->shuttle_driver_id)
                ->whereNotIn('trip_status', ['completed','cancelled'])
                ->update([
                    'trip_status'   => 'cancelled',
                    'end_time'      =>  Carbon::now()->format('H:i A'),
                    'extra_notes'   =>  $extra_notes
                    ]);

                $getallshuttleuser = LinerideUserBooking::with(['user','driver'])->where('driver_id', $user->id)
                ->where('shuttle_driver_id', $request->shuttle_driver_id)
                ->where('trip_status' , 'cancelled')->get();

                if(sizeof($getallshuttleuser) > 0){
                    foreach($getallshuttleuser as $shuttle){
                        $user_deatil = $shuttle->user;
                        $driver_deatil = $shuttle->driver;
                        // notification for user
                        $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
                        $user_data['type']          =   'trip_cancelled';
                        $user_data['user_id']       =   $shuttle->user_id;
                        $user_data['title']         =   'RueRun';
                        $user_data['sound']         =   'default';
                        $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

                        // notification for driver
                        $driver_data['message']         =  'Shuttle Booking Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
                        $driver_data['type']            =   'trip_cancelled';
                        $driver_data['driver_id']       =   $shuttle->driver_id;
                        $driver_data['title']           =   'RueRun';
                        $driver_data['sound']           =   'default';
                        $driver_data['notification']    =   Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
                        if($user->id == $tripdetail->user_id){
                            $touserid = $tripdetail->driver_id;
                        }else{
                            $touserid = $tripdetail->user_id;
                        }

                        UserNotification::where('shuttle_id', $tripdetail->id)->where('sent_to_user',$tripdetail->driver_id)->update([
                            'sent_from_user' => $tripdetail->user_id,
                            'sent_to_user' => $tripdetail->driver_id,
                            'notification_for' => "cancel",
                            'title' => "Shuttle Booking cancelled",
                            'description' => "Your Shuttle Booking cancelled Successfully",
                            'admin_flag' => "0",
                            ]);
                        // UserNotification::create([
                        //     'sent_from_user'    => $user->id,
                        //     'sent_to_user'      => $touserid,
                        //     'shuttle_id'        => $tripdetail->id,
                        //     'notification_for'  => "cancel",
                        //     'title'             => "Shuttle Booking cancelled",
                        //     'description'       => "Your Shuttle Booking cancelled Successfully",
                        //     'admin_flag'        => "0",
                        // ]);

                        PopupNotification::create([
                            'from_user_id'      => $user->id,
                            'to_user_id'        => $touserid,
                            'title'             => 'Shuttle Booking cancelled',
                            'description'       => 'Shuttle Booking has been cancelled Successfully',
                            'date'              => Carbon::now()->format('Y-m-d'),
                            'time'              => Carbon::now()->format('H:i A'),
                            'shuttle_id'        => $tripdetail->id,
                            ]);
                    }
                }
                return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>LinerideBooking::find($request->shuttle_driver_id)]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }
    // public function tripCancelled(Request $request){
    //     $validation_array =[
    //         'booking_id'       => 'nullable'
    //     ];
    //     $validation = Validator::make($request->all(),$validation_array);
    //     if($validation->fails()){
    //         return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
    //     }
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         if($request->booking_id){
    //             $trip_id = $request->booking_id;
    //             $tripdetail = Booking::where('id', $trip_id)->first();

    //             if($tripdetail->trip_status == "on_going"){
    //                 return response()->json(['status' => 'error','message' => "Ongoing trip can't be cancelled"]);
    //             }
    //             if(!empty($tripdetail->out_town_id)){
    //                 if($tripdetail->seats == "vip"){
    //                     $outTownBooking = OutTwonrideBooking::where('id',$tripdetail->out_town_id)->first();
    //                     $total_seat_available = $outTownBooking->seat_available + $outTownBooking->seat_booked;
    //                     $total_seat_booked = "0";
    //                     OutTwonrideBooking::where('id', $tripdetail->out_town_id)->update(['seat_available'=>$total_seat_available, 'seat_booked' => $total_seat_booked]);

    //                 } else {
    //                     $outTownBooking = OutTwonrideBooking::where('id',$tripdetail->out_town_id)->first();
    //                     $total_seat_available = $outTownBooking->seat_available + $tripdetail->seats;
    //                     $total_seat_booked = $outTownBooking->seat_booked + $tripdetail->seats;
    //                     OutTwonrideBooking::where('id', $tripdetail->out_town_id)->update(['seat_available'=>$total_seat_available, 'seat_booked' => $total_seat_booked]);
    //                 }
    //             }

    
    //             if(!empty($tripdetail)){
    //                 if($tripdetail->payment_status == "pending"){
    //                 $tripdetail = Booking::with('card_details')->where('id', $tripdetail->id)->first();
    //                 if(!empty($tripdetail->driver_id)){
    //                 $to_time =  strtotime("now");
    //                 $from_time = strtotime($tripdetail->created_at);
    //                 $diff = round(abs($to_time - $from_time) / 60,3);
    //                 if($diff >= 3){
    //                     $booking_fee = Setting::where('code','booking_fee')->first();
    //                     $bas_fee = Setting::where('code','bas_fee')->first();
    //                     $teamp = $booking_fee->value + $bas_fee->value;
    //                     $tripdetail['booking_fee'] = $booking_fee->value;
    //                     $tripdetail['bas_fee'] = $bas_fee->value;
    //                     $tripdetail['booking_total_fee'] = (string)$teamp;
    //                     return response()->json(['status' => 'error','message' => 'Booking has been pending Successfully', 'data'=>$tripdetail]);
    //                 }
    //                 }
    
    //             }
    //                 if($request->extra_notes){
    //                     $extra_notes = $request->extra_notes;
    //                     Booking::where('id', $tripdetail->id)->update(['trip_status'=>'cancelled', 'payment_status'=>'cancelled','admin_comm_status'=>'cancelled','tip_amount_status'=>'cancelled','extra_notes' => $extra_notes]);
    //                 }else{
    //                     Booking::where('id', $tripdetail->id)->update(['trip_status'=>'cancelled','payment_status'=>'cancelled','admin_comm_status'=>'cancelled','tip_amount_status'=>'cancelled']);
    //                 }

    //                 $user_deatil = User::where('id',$tripdetail->user_id)->first();
    //                 $driver_deatil = User::where('id',$tripdetail->driver_id)->first();
    //                 // notification for user
    //                 $user_data['message'] =  'Booking Cancelled Successfully';
    //                 $user_data['type'] = 'trip_cancelled';
    //                 $user_data['user_id'] = $tripdetail->user_id;
    //                 $user_data['title']        =  'RueRun';
    //                 $user_data['sound']        = 'default';
    //                 $user_data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                 // notification for driver
    //                 $driver_data['message'] =  'Trip Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
    //                 $driver_data['type'] = 'trip_cancelled';
    //                 $driver_data['driver_id'] = $tripdetail->driver_id;
    //                 $driver_data['title']     =  'RueRun';
    //                 $driver_data['sound']     = 'default';
    //                 $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
    //                 if($user->id == $tripdetail->user_id){
    //                     $touserid = $tripdetail->driver_id;
    //                 }else{
    //                     $touserid = $tripdetail->user_id;
    //                 }
    //                 UserNotification::where('booking_id', $trip_id)->where('sent_to_user',$tripdetail->driver_id)->update([
    //                     'sent_from_user' =>$tripdetail->user_id,
    //                     'sent_to_user' => $tripdetail->driver_id,
    //                     'notification_for' => "cancel",
    //                     'title' => "Booking cancelled",
    //                     'description' => "Your Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);
    //                 // UserNotification::create([
    //                 //     'sent_from_user' => $user->id,
    //                 //     'sent_to_user' => $touserid,
    //                 //     'booking_id' => $trip_id,
    //                 //     'notification_for' => "cancel",
    //                 //     'title' => "Booking cancelled",
    //                 //     'description' => "Your Booking cancelled Successfully",
    //                 //     'admin_flag' => "0",
    //                 // ]);

    //                 PopupNotification::create([
    //                     'from_user_id' => $user->id,
    //                     'to_user_id' => $touserid,
    //                     'title' => 'Booking Cancelled',
    //                     'description' => 'Booking has been cancelled Successfully',
    //                     'date' => Carbon::now()->format('d-m-Y'),
    //                     'time' => Carbon::now()->format('H:i A'),
    //                     'booking_id' => $tripdetail->id,
    //                 ]);
    //                 return response()->json(['status' => 'success','message' => 'Booking has been cancelled Successfully', 'data'=>Booking::find($trip_id)]);
    //             }else{
    //                 return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    //             }
    //         }else if($request->parcel_id){

    //             $trip_id = $request->parcel_id;
    //             $tripdetail = ParcelDetail::where('id', $trip_id)->first();

    //             if($tripdetail->parcel_status == "on_going"){
    //                 return response()->json(['status' => 'error','message' => "Ongoing trip can't be cancelled"]);
    //             }

    //             if(!empty($tripdetail)){
    //                 if($request->extra_notes){
    //                     $extra_notes = $request->extra_notes;
    //                     ParcelDetail::where('id', $tripdetail->id)->update(['parcel_status'=>'cancelled', 'extra_notes' => $extra_notes]);
    //                 }else{
    //                     ParcelDetail::where('id', $tripdetail->id)->update(['parcel_status'=>'cancelled']);
    //                 }

    //                 $user_deatil = User::where('id',$tripdetail->user_id)->first();
    //                 $driver_deatil = User::where('id',$tripdetail->driver_id)->first();
    //                 // notification for user
    //                 $user_data['message'] =  'Parcel Detail Booking Cancelled Successfully';
    //                 $user_data['type'] = 'trip_cancelled';
    //                 $user_data['user_id'] = $tripdetail->user_id;
    //                 $user_data['title']        =  'RueRun';
    //                 $user_data['sound']        = 'default';
    //                 $user_data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                 // notification for driver
    //                 $driver_data['message'] =  'Trip Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
    //                 $driver_data['type'] = 'trip_cancelled';
    //                 $driver_data['driver_id'] = $tripdetail->driver_id;
    //                 $driver_data['title']     =  'RueRun';
    //                 $driver_data['sound']     = 'default';
    //                 $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
    //                 if($user->id == $tripdetail->user_id){
    //                     $touserid = $tripdetail->driver_id;
    //                 }else{
    //                     $touserid = $tripdetail->user_id;
    //                 }
    //                 UserNotification::where('parcel_id', $trip_id)->where('sent_to_user',$tripdetail->driver_id)->update([
    //                     'sent_from_user' => $tripdetail->user_id,
    //                     'sent_to_user' => $tripdetail->driver_id,
    //                     'notification_for' => "cancel",
    //                     'title' => "Parcel Detail Booking cancelled",
    //                     'description' => "Your Parcel Detail Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);
    //                 // UserNotification::create([
    //                 //     'sent_from_user' => $user->id,
    //                 //     'sent_to_user' => $touserid,
    //                 //     'booking_id' => $trip_id,
    //                 //     'notification_for' => "cancel",
    //                 //     'title' => "Parcel Detail Booking cancelled",
    //                 //     'description' => "Your Parcel Detail Booking cancelled Successfully",
    //                 //     'admin_flag' => "0",
    //                 // ]);

    //                 PopupNotification::create([
    //                     'from_user_id' => $user->id,
    //                     'to_user_id' => $touserid,
    //                     'title' => 'Parcel Detail Booking Cancelled',
    //                     'description' => 'Parcel Detail Booking has been cancelled Successfully',
    //                     'date' => Carbon::now()->format('d-m-Y'),
    //                     'time' => Carbon::now()->format('H:i A'),
    //                     'booking_id' => $tripdetail->id,
    //                 ]);
    //                 return response()->json(['status' => 'success','message' => 'Parcel Detail Booking has been cancelled Successfully', 'data'=>ParcelDetail::find($trip_id)]);
    //             }else{
    //                 return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    //             }

    //         }else if($request->shuttle_id){
    //             $tripdetail = LinerideUserBooking::with(['user','driver'])->where('id', $request->shuttle_id)->first();
    //             if(empty($tripdetail)){
    //                 $tripdetail = LinerideBooking::with(['driver'])->where('id', $request->shuttle_id)->first();
    //             }

    //             if($request->extra_notes){
    //                 $extra_notes = $request->extra_notes;
    //             }else{
    //                 $extra_notes = '';
    //             }

    //             if($user->user_type == 'user'){
    //                 LinerideUserBooking::where('user_id', $user->id)->where('id', $request->shuttle_id)
    //                     ->update([
    //                         'trip_status'   => 'cancelled',
    //                         'end_time'      =>  Carbon::now()->format('H:i A'),
    //                         'extra_notes'   =>  $extra_notes
    //                     ]);

    //                 $user_deatil = $tripdetail->user;
    //                 $driver_deatil = $tripdetail->driver;
    //                 // notification for user
    //                 $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
    //                 $user_data['type']          =   'trip_cancelled';
    //                 $user_data['user_id']       =   $tripdetail->user_id;
    //                 $user_data['title']         =   'RueRun';
    //                 $user_data['sound']         =   'default';
    //                 $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                 // notification for driver
    //                 $driver_data['message']         =  'Shuttle Booking Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
    //                 $driver_data['type']            =   'trip_cancelled';
    //                 $driver_data['driver_id']       =   $tripdetail->driver_id;
    //                 $driver_data['title']           =   'RueRun';
    //                 $driver_data['sound']           =   'default';
    //                 $driver_data['notification']    =   Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
    //                 if($user->id == $tripdetail->user_id){
    //                     $touserid = $tripdetail->driver_id;
    //                 }else{
    //                     $touserid = $tripdetail->user_id;
    //                 }

    //                 UserNotification::where('shuttle_id', $tripdetail->id)->where('sent_to_user',$tripdetail->driver_id)->update([
    //                     'sent_from_user' => $tripdetail->user_id,
    //                     'sent_to_user' => $tripdetail->driver_id,
    //                     'notification_for' => "cancel",
    //                     'title' => "Shuttle Booking cancelled",
    //                     'description' => "Your Shuttle Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);
    //                 // UserNotification::create([
    //                 //     'sent_from_user'    => $user->id,
    //                 //     'sent_to_user'      => $touserid,
    //                 //     'shuttle_id'        => $tripdetail->id,
    //                 //     'notification_for'  => "cancel",
    //                 //     'title'             => "Shuttle Booking cancelled",
    //                 //     'description'       => "Your Shuttle Booking cancelled Successfully",
    //                 //     'admin_flag'        => "0",
    //                 // ]);

    //                 PopupNotification::create([
    //                     'from_user_id'      => $user->id,
    //                     'to_user_id'        => $touserid,
    //                     'title'             => 'Shuttle Booking cancelled',
    //                     'description'       => 'Shuttle Booking has been cancelled Successfully',
    //                     'date'              => Carbon::now()->format('Y-m-d'),
    //                     'time'              => Carbon::now()->format('H:i A'),
    //                     'shuttle_id'        => $tripdetail->id,
    //                 ]);
    //                 return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>LinerideUserBooking::find($tripdetail->id)]);
    //             }else{
    //                 $shuttle_user_id = LinerideUserBooking::where('driver_id', $user->id)->where('id', $request->shuttle_id)->first();
    //                 LinerideUserBooking::where('driver_id', $user->id)
    //                     ->where('shuttle_driver_id', $shuttle_user_id)
    //                     ->where('id', $request->shuttle_id)
    //                     ->whereNotIn('trip_status', ['completed','cancelled'])
    //                     ->update([
    //                         'trip_status'   => 'cancelled',
    //                         'end_time'      =>  Carbon::now()->format('H:i A'),
    //                         'extra_notes'   =>  $extra_notes
    //                     ]);
    //                     // dd($shuttle_user_id);
    //                 $shuttle_detail = LinerideUserBooking::with(['user','driver'])->where('id', $request->shuttle_id)->first();

    //                 // dd($shuttle_detail);
    //                 if($user->id == $shuttle_detail->driver_id){
    //                     $touserid = $shuttle_detail->user_id;
    //                 }else{
    //                     $touserid = $shuttle_detail->driver_id;
    //                 }

    //                 $user_deatil = $shuttle_detail->user;
    //                 $driver_deatil = $shuttle_detail->driver;
    //                 // notification for user
    //                 $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
    //                 $user_data['type']          =   'trip_cancelled';
    //                 $user_data['user_id']       =   $touserid;
    //                 $user_data['title']         =   'RueRun';
    //                 $user_data['sound']         =   'default';
    //                 $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                 UserNotification::where('shuttle_id', $tripdetail->id)->where('sent_to_user',$tripdetail->driver_id)->update([
    //                     'sent_from_user' => $shuttle_detail->user,
    //                     'sent_to_user' => $shuttle_detail->driver,
    //                     'notification_for' => "cancel",
    //                     'title' => "Shuttle Booking cancelled",
    //                     'description' => "Your Shuttle Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);

    //                 // UserNotification::create([
    //                 //     'sent_from_user'    => $user->id,
    //                 //     'sent_to_user'      => $touserid,
    //                 //     'shuttle_id'        => $tripdetail->id,
    //                 //     'notification_for'  => "cancel",
    //                 //     'title'             => "Shuttle Booking cancelled",
    //                 //     'description'       => "Your Shuttle Booking cancelled Successfully",
    //                 //     'admin_flag'        => "0",
    //                 // ]);

    //                 PopupNotification::create([
    //                     'from_user_id'      => $user->id,
    //                     'to_user_id'        => $touserid,
    //                     'title'             => 'Shuttle Booking cancelled',
    //                     'description'       => 'Shuttle Booking has been cancelled Successfully',
    //                     'date'              => Carbon::now()->format('Y-m-d'),
    //                     'time'              => Carbon::now()->format('H:i A'),
    //                     'shuttle_id'        => $tripdetail->id,
    //                 ]);
    //                 return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>$shuttle_detail]);
    //             }
    //         }else if($request->shuttle_driver_id){
    //             $tripdetail = LinerideBooking::with(['driver'])->where('id', $request->shuttle_driver_id)->first();

    //             if($request->extra_notes){
    //                 $extra_notes = $request->extra_notes;
    //             }else{
    //                 $extra_notes = '';
    //             }
    //             LinerideBooking::where('driver_id', $user->id)->where('id', $request->shuttle_driver_id)
    //                 ->update([
    //                     'trip_status'   => 'cancelled',
    //                     'end_time'      =>  Carbon::now()->format('H:i A'),
    //                     'extra_notes'   =>  $extra_notes
    //                 ]);
    //             LinerideUserBooking::where('driver_id', $user->id)
    //                 ->where('shuttle_driver_id', $request->shuttle_driver_id)
    //                 ->whereNotIn('trip_status', ['completed','cancelled'])
    //                 ->update([
    //                     'trip_status'   => 'cancelled',
    //                     'end_time'      =>  Carbon::now()->format('H:i A'),
    //                     'extra_notes'   =>  $extra_notes
    //                 ]);

    //             $getallshuttleuser = LinerideUserBooking::with(['user','driver'])->where('driver_id', $user->id)
    //                 ->where('shuttle_driver_id', $request->shuttle_driver_id)
    //                 ->where('trip_status' , 'cancelled')->get();

    //             if(sizeof($getallshuttleuser) > 0){
    //                 foreach($getallshuttleuser as $shuttle){
    //                     $user_deatil = $shuttle->user;
    //                     $driver_deatil = $shuttle->driver;
    //                     // notification for user
    //                     $user_data['message']       =   'Shuttle Ride Booking Cancelled Successfully';
    //                     $user_data['type']          =   'trip_cancelled';
    //                     $user_data['user_id']       =   $shuttle->user_id;
    //                     $user_data['title']         =   'RueRun';
    //                     $user_data['sound']         =   'default';
    //                     $user_data['notification']  =   Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

    //                     // notification for driver
    //                     $driver_data['message']         =  'Shuttle Booking Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
    //                     $driver_data['type']            =   'trip_cancelled';
    //                     $driver_data['driver_id']       =   $shuttle->driver_id;
    //                     $driver_data['title']           =   'RueRun';
    //                     $driver_data['sound']           =   'default';
    //                     $driver_data['notification']    =   Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));
    //                     if($user->id == $tripdetail->user_id){
    //                         $touserid = $tripdetail->driver_id;
    //                     }else{
    //                         $touserid = $tripdetail->user_id;
    //                     }

    //                     UserNotification::where('shuttle_id', $tripdetail->id)->where('sent_to_user',$tripdetail->driver_id)->update([
    //                     'sent_from_user' => $tripdetail->user_id,
    //                     'sent_to_user' => $tripdetail->driver_id,
    //                     'notification_for' => "cancel",
    //                     'title' => "Shuttle Booking cancelled",
    //                     'description' => "Your Shuttle Booking cancelled Successfully",
    //                     'admin_flag' => "0",
    //                 ]);
    //                     // UserNotification::create([
    //                     //     'sent_from_user'    => $user->id,
    //                     //     'sent_to_user'      => $touserid,
    //                     //     'shuttle_id'        => $tripdetail->id,
    //                     //     'notification_for'  => "cancel",
    //                     //     'title'             => "Shuttle Booking cancelled",
    //                     //     'description'       => "Your Shuttle Booking cancelled Successfully",
    //                     //     'admin_flag'        => "0",
    //                     // ]);

    //                     PopupNotification::create([
    //                         'from_user_id'      => $user->id,
    //                         'to_user_id'        => $touserid,
    //                         'title'             => 'Shuttle Booking cancelled',
    //                         'description'       => 'Shuttle Booking has been cancelled Successfully',
    //                         'date'              => Carbon::now()->format('Y-m-d'),
    //                         'time'              => Carbon::now()->format('H:i A'),
    //                         'shuttle_id'        => $tripdetail->id,
    //                     ]);
    //                 }
    //             }
    //             return response()->json(['status' => 'success','message' => 'Shuttle Ride Booking has been cancelled Successfully', 'data'=>LinerideBooking::find($request->shuttle_driver_id)]);
    //         }
    //     } catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    //     }
    // }




    /*
    |--------------------------------------------------------------------------
    | Admin Cancle Booking Charge
    |--------------------------------------------------------------------------
    |
    */

    public function AdminChargetripCancelled(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            if($request->booking_id){
                $trip_id = $request->booking_id;
                $tripdetail = Booking::where('id', $trip_id)->first();
                $booking_fee = Setting::where('code','booking_fee')->first();
                $bas_fee = Setting::where('code','bas_fee')->first();
                $teamp = 5 + ((10/100)*($booking_fee->value + $bas_fee->value));
                if($teamp == $request->booking_total_fee){

                    $data['booking_id']=$request->booking_id;
                    $data['amount']=$request->booking_total_fee;
                    $data['user_id']=$user->id;
                    $data['status']="complete";
                    $service_type='booking';

                    CancleBooking::Create($data);
                    Booking::where('id', $tripdetail->id)->update(['trip_status'=>'cancelled','payment_status'=>'completed','total_amount'=>$request->booking_total_fee]);
                    if($tripdetail->payment_type === 'wallet'){
                        $result = $this->setWalletPayment($tripdetail, $service_type);
                    }else{
                        $result = $this->setCardPayment($tripdetail, $request->transaction_id, $service_type);
                    }

                    $user_deatil = User::where('id',$tripdetail->user_id)->first();
                    $driver_deatil = User::where('id',$tripdetail->driver_id)->first();

            // notification for user
                    $user_data['message'] =  'Booking Cancelled Successfully';
                    $user_data['type'] = 'trip_cancelled';
                    $user_data['user_id'] = $tripdetail->user_id;
                    $user_data['title']        =  'RueRun';
                    $user_data['sound']        = 'default';
                    $user_data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_deatil,$user_data));

            // notification for driver
                    $driver_data['message'] =  'Trip Cancelled Successfully '.@$user_deatil->last_name.' '.@$user_deatil->first_name;
                    $driver_data['type'] = 'trip_cancelled';
                    $driver_data['driver_id'] = $tripdetail->driver_id;
                    $driver_data['title']     =  'RueRun';
                    $driver_data['sound']     = 'default';
                    $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_deatil,$driver_data));

                    UserNotification::where('booking_id', $trip_id)->where('sent_to_user',$tripdetail->driver_id)->update([
                        'sent_from_user' =>$tripdetail->user_id,
                        'sent_to_user' => $tripdetail->driver_id,
                        'notification_for' => "cancel",
                        'title' => "Booking cancelled",
                        'description' => "Your Booking cancelled Successfully",
                        'admin_flag' => "0",
                        ]);
                    if($user->id == $tripdetail->user_id){
                        $touserid = $tripdetail->driver_id;
                    }else{
                        $touserid = $tripdetail->user_id;
                    }
                    PopupNotification::create([
                        'from_user_id' => $user->id,
                        'to_user_id' => $touserid,
                        'title' => 'Booking Cancelled',
                        'description' => 'Booking has been cancelled Successfully',
                        'date' => Carbon::now()->format('d-m-Y'),
                        'time' => Carbon::now()->format('H:i A'),
                        'booking_id' => $tripdetail->id,
                        ]);
                    return response()->json(['status' => 'success','message' => 'Booking has been cancelled Successfully', 'data'=>Booking::find($trip_id)]);
                } else {
                    return response()->json(['status' => 'error','message' => 'Something went Wrong']);
                }   

            }else if($request->parcel_id){

            }else if($request->shuttle_id){

            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }


/*
|--------------------------------------------------------------------------
| Payment via wallet (used in completePayment())
|--------------------------------------------------------------------------
|
*/
public function setWalletPayment($tripdetail=array(), $service_type=''){
    if(!empty($tripdetail)){
        if($tripdetail->user_id){
            $user_wallet = Wallet::where('user_id', $tripdetail->user_id)->first();
            $driver_wallet = Wallet::where('user_id', $tripdetail->driver_id)->first();
            $total_amount = $tripdetail->total_amount;
            if($tripdetail->tip_amount){
                $total_amount = (int)$tripdetail->total_amount + (int)$tripdetail->tip_amount;
            }if($tripdetail->hold_time_amount){
                $total_amount = (int)$total_amount + (int)$tripdetail->hold_time_amount;
            }
            $total_amount = (int)$total_amount + (int)$tripdetail->toll_amount + (int)$tripdetail->airport_charge;
            if(!empty($user_wallet)){
                if((int)$total_amount <= (int)$user_wallet->amount){
            // Update User Wallet Amount
                    $deducted_amt = (int)$user_wallet->amount - (int)$total_amount;
                    Wallet::where('user_id', $tripdetail->user_id)->update([
                        'amount' => $deducted_amt
                        ]);
                    // Update Driver Wallet Amount
                    if(!empty($driver_wallet)){
                        $driver_amt = (int)$driver_wallet->amount + (int)$total_amount;
                        Wallet::where('user_id', $tripdetail->driver_id)->update([
                            'amount' => $driver_amt
                            ]);
                    }else{
                        $driver_amt = (int)$total_amount;
                        Wallet::create([
                            'amount' => $driver_amt,
                            'user_id'=>$tripdetail->driver_id
                            ]);
                    }
                    WalletHistory::create([
                        'user_id'       => $tripdetail->user_id,
                        'amount'        => (int)$total_amount,
                        'description'   => '$'.(int)$total_amount.' deducted from Your Wallet for Ride Booking',
                        'refer_user_id' => $tripdetail->driver_id,
                        ]);

                    if($service_type == 'booking'){
                        TransactionDetail::create([
                            'amount'            => (int)$total_amount ,
                            'user_id'           => (int)$tripdetail->user_id,
                            'booking_id'        => (int)$tripdetail->id,
                            'promo_id'          => (int)$tripdetail->promo_id,
                            'status'            => 'complete',
                            ]);
                    }else if($service_type == 'parcel_booking'){
                        TransactionDetail::create([
                            'amount'            => (int)$total_amount ,
                            'user_id'           => (int)$tripdetail->user_id,
                            'parcel_id'         => (int)$tripdetail->id,
                            'promo_id'          => (int)$tripdetail->promo_id,
                            'status'            => 'complete',
                            ]);
                    }else if($service_type == 'shuttle'){
                        TransactionDetail::create([
                            'amount'            => (int)$total_amount ,
                            'user_id'           => (int)$tripdetail->user_id,
                            'shuttle_id'        => (int)$tripdetail->id,
                            'promo_id'          => (int)$tripdetail->promo_id,
                            'status'            => 'complete',
                            ]);
                    }
                    $data['message'] = 'Amount is Deducted from User Wallet';
                    $data['type'] = 'success';
                }else{
                    $data['type'] = 'error';
                    $data['message'] = 'Wallet amount is not Sufficient';
                }
            }else{
                $data['type'] = 'error';
                $data['message'] = 'User has no Wallet';
            }
        }
        return $data;
    }
}


/*
|--------------------------------------------------------------------------
| Payment via card (used in completePayment())
|--------------------------------------------------------------------------
|
*/
public function setCardPayment($tripdetail=array(), $transaction_id='', $service_type=''){
    if(!empty($tripdetail) && $transaction_id && $service_type){
        $checktransaction = TransactionDetail::with(['tripDetail','parcelDetail'])->where('user_id',$tripdetail->user_id)->where('booking_id',$tripdetail->id)->orWhere('parcel_id',$tripdetail->id)->first();
        if(!empty($checktransaction)){
            $data['type'] = 'error';
            $data['message'] = 'Your Transaction is Already Done';
            return $data;
        }
        $total_amount = $tripdetail->total_amount;
        if($tripdetail->tip_amount){
            $total_amount = (int)$tripdetail->total_amount + (int)$tripdetail->tip_amount;
        }
        if($tripdetail->hold_time_amount){
            $total_amount = (int)$total_amount + (int)$tripdetail->hold_time_amount;
        }
        $total_amount = (int)$total_amount + (int)$tripdetail->toll_amount + (int)$tripdetail->airport_charge;

        if($service_type == 'booking'){
            TransactionDetail::create([
                'amount'            => (int)$total_amount,
                'user_id'           => (int)$tripdetail->user_id,
                'transaction_id'    => $transaction_id,
                'booking_id'        => (int)$tripdetail->id,
                'promo_id'          => (int)$tripdetail->promo_id,
                'status'            => 'complete',
                ]);
        }else if($service_type == 'parcel_booking'){
            TransactionDetail::create([
                'amount'            => (int)$total_amount ,
                'user_id'           => (int)$tripdetail->user_id,
                'transaction_id'    => $transaction_id,
                'parcel_id'         => (int)$tripdetail->id,
                'promo_id'          => (int)$tripdetail->promo_id,
                'status'            => 'complete',
                ]);
        }else if($service_type == 'shuttle'){
            TransactionDetail::create([
                'amount'            => (int)$total_amount ,
                'user_id'           => (int)$tripdetail->user_id,
                'shuttle_id'        => (int)$tripdetail->id,
                'promo_id'          => (int)$tripdetail->promo_id,
                'status'            => 'complete',
                'transaction_id'    => $transaction_id,
                ]);
        }


        $data['type'] = 'success';
        $data['message'] = 'Payment Successfully Done';
        return $data;
    }
}

    /*
    |--------------------------------------------------------------------------
    | Emergency contact list
    |--------------------------------------------------------------------------
    |
    */
    public function getAllEmergencyContacts(){
        try {
            $contact_list = EmergencyDetails::get();
            if(count($contact_list) != 0){
                return response()->json(['status' => 'success','message' => 'All Emergency contact List', 'data'=>$contact_list]);
            }else{
                return response()->json(['status' => 'error','message' => 'No Emergency contact List found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DeActivate Account
    |--------------------------------------------------------------------------
    |
    */
    public function DeActivate(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $user_data = User::where('id',$user->id)->first();
            if($user_data){
                $user_data->status = "inactive";
                $user_data->save();
                return response()->json(['status' => 'success','message' => 'DeActivate Account Successfully','data' => $user_data]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Conversions
    |--------------------------------------------------------------------------
    |
    */
    public function getconversions(Request $request){
        $validator = Validator::make($request->all(),[
            'sent_to'       => 'required',
            ]);
        if($validator->fails()){
            return response()->json(['status'    => 'error','message'   => $validator->messages()->first()]);
        }
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $getUserSentMessages = Conversions::where('sent_from',$user->id)->Where('sent_to',$request->get('sent_to'))->get()->toArray();
            $getUserRecivedMessages = Conversions::where('sent_from',$request->get('sent_to'))->Where('sent_to',$user->id)->get()->toArray();
            $data=array_merge($getUserSentMessages,$getUserRecivedMessages);
            array_multisort( array_column($data, "id"), SORT_ASC, $data );
            return response()->json(['status'    => 'success','message'   => 'Conversasions Record List','data'      => $data,]);
        }catch (\Exception $exception){
            return response()->json(['status'    => 'error','message'   => $exception->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Rating Reviews
    |--------------------------------------------------------------------------
    |
    */
    public function getRatingReviews(Request $request){
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $ratingreviews = RatingReviews::where('to_user_id',$user->id)->where('status','approved')->get()->toArray();
            $avgrating = RatingReviews::where('to_user_id',$user->id)->where('status','approved')->get()->avg('rating');
            if(count($ratingreviews) != 0){
                return response()->json(['status'=>'success','message'=> 'You are successfully User Rating Reviews!','data'=> $ratingreviews, 'average_rating'=>number_format($avgrating,1,'.',',')]);
            }else{
                return response()->json(['status'=> 'error','message'=>'User Rating Reviews Record List Not Found']);
            }
        }catch (\Exception $exception){
            return response()->json(['status'=> 'error','message'=> $exception->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Driver Rating Reviews
    |--------------------------------------------------------------------------
    |
    */
    public function getDriverRatingReviews(Request $request){
        $validator = Validator::make($request->all(), [
            'driver_id'   => 'required',
            ]);
        if($validator->fails()) {
            return response()->json(['status'   => 'error','message'  => $validator->messages()->first()]);
        }
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            // $ratingreviews = RatingReviews::with('to_user')->where('from_user_id',$request->driver_id)->where('status','approved')->get()->toArray();
            $ratingreviews = RatingReviews::with('to_user')->where('to_user_id',$request->driver_id)->orderBy('id', 'DESC')->get()->toArray();
            $rating = RatingReviews::where('to_user_id', $request->driver_id)->where('status','approved')->get();
            $avg_star_rating = number_format((float)$rating->avg('rating'), 1, '.', '');

            $five_rating    = RatingReviews::where('to_user_id', $request->driver_id)->whereIn('rating',['5.0'])->where('status','approved')->get()->count();
            $four_rating    = RatingReviews::where('to_user_id', $request->driver_id)->whereIn('rating',['4.0','4.5'])->where('status','approved')->get()->count();
            $three_rating   = RatingReviews::where('to_user_id', $request->driver_id)->whereIn('rating',['3.0','3.5'])->where('status','approved')->get()->count();
            $two_rating     = RatingReviews::where('to_user_id', $request->driver_id)->whereIn('rating',['2.0','2.5'])->where('status','approved')->get()->count();
            $one_rating     = RatingReviews::where('to_user_id',$request->driver_id)->whereIn('rating',['1.0','1.5'])->where('status','approved')->get()->count();

            $all_rating = [];
            $all_rating['five_rating']      = $five_rating;
            $all_rating['four_rating']      = $four_rating;
            $all_rating['three_rating']     = $three_rating;
            $all_rating['two_rating']       = $two_rating;
            $all_rating['one_rating']       = $one_rating;

            if(count($ratingreviews)>0){
                return response()->json(['status'=>'success','message'=> 'You are successfully User Rating Reviews!','data'=> $ratingreviews, 'avg_star_rating'=>$avg_star_rating, 'all_rating' => $all_rating ]);
            }else{
                return response()->json(['status'=> 'error','message'=>'User Rating Reviews Record List Not Found']);
            }
        }catch(Exception $e){
            return response()->json(['status'  => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Conversions
    |--------------------------------------------------------------------------
    |
    */
    public function CreateConversions(Request $request){
        $validation_array =[
        'message'       => 'required',
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status'    => 'error','message'   => $validation->errors()->first(),'data' => (object)[]]);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            if(!empty($request->trip_id)){
                $trip_id = $request->trip_id;
            } else {
                $trip_id = "";
            }
            $created_at = Carbon::now()->format('Y-m-d H:i:s');

            $data['sent_from']=$user->id;
            $data['sent_to']=request('sent_to');
            $data['message']=request('message');
            $data['is_read']="unread";
            $data['trip_id']=$trip_id;
            $data['created_at']=$created_at;
            $data['updated_at']="";
            $data['deleted_at']="";

            $userdata = Conversions::Create($data);
            $data['id']=$userdata->id;
            if($user->user_type == 'driver'){
                $user_data = User::where('id',$request->sent_to)->first();
                $user_notification_data['id']  =       $user->id;
                $user_notification_data['booking_id'] = $request->trip_id;
                $user_notification_data['message']    = $request->message;
                $user_notification_data['type']       = 'new_conversions';
                $user_notification_data['title']      =  $user->first_name.' '.$user->last_name;
                $user_notification_data['sound']      = 'default';
                $user_notification_data['notification']  = Event::dispatch('send-notification-assigned-user',array($user_data,$user_notification_data));
            } else {
                $driver_data = User::where('id',$request->sent_to)->first();
                $driver_notification_data['id'] = $user->id;
                $driver_notification_data['booking_id'] = $request->trip_id;
                $driver_notification_data['message'] =  $request->message;
                $driver_notification_data['type'] = 'new_conversions';
                $driver_notification_data['title']        =  $user->first_name.' '.$user->last_name;
                $driver_notification_data['sound']        = 'default';
                $driver_notification_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_data,$driver_notification_data));
            }
            return response()->json(['status' => 'success','message' => 'You are successfully Conversions!','data' => $data]);
        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Invite Contact Users And Driver
    |--------------------------------------------------------------------------
    |
    */
    // public function createemailinvitecontact(Request $request){
    //     $validation_array =[
    //     'email' => 'required',
    //     'description' => 'required'
    //     ];
    //     $validation = Validator::make($request->all(),$validation_array);
    //     if($validation->fails()){
    //         return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
    //     }
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         if($request->get('email')){
    //             $UserEmails = $request->get('email');
    //         }else{
    //             $UserEmails = '';
    //         }
    //         if($user->user_type == 'driver'){
    //             $link = "https://play.google.com/store/apps/details?id=com.ruerun.taxidriverapp";
    //         } elseif($user->user_type == 'user'){
    //             $link = "https://play.google.com/store/apps/details?id=com.ruerun.user";
    //         }else{
    //             $link = "http://rueruntest.aistechnolabs.co";
    //         }

    //         foreach (@$UserEmails as $email) {
    //             InviteContactList::create(['email' => $email,'user_id' => $user->id,'link'=>$link,'description'=>$request->get('description') ]);
    //             $emailcontent = array (
    //                 'text' => $request->get('description'),
    //                 'title' => "Invitation",
    //                 'link' => $link,
    //                 'userName' => $user->first_name
    //                 );
    //             $details['from_email'] = $user->email;
    //             $details['email'] = $email;
    //             $details['username'] = $user->first_names;
    //             $details['subject'] = 'Ruerun Invitation ';
    //             dispatch(new sendInvitation($details,$emailcontent));
    //         }
    //         return response()->json(['status'=>'success','message'=> 'Invitation Sent Successfully!']);
    //     }catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
    //     }
    // }
    // public function createemailinvitecontact(Request $request){
    //     $validation_array =[
    //     'email' => 'required',
    //     'description' => 'required'
    //     ];
    //     $validation = Validator::make($request->all(),$validation_array);
    //     if($validation->fails()){
    //         return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
    //     }
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         $current_date = date("Y-m-d");
    //         $check_InviteContactList = InviteContactList::where('user_id',$user->id)->where('email',$request->email)->where('created_at','>=',$current_date)->orderBy('id','DESC')->first();
    //         // dd($check_InviteContactList);
    //         if(empty($check_InviteContactList)){
    //             if($request->get('email')){
    //             $UserEmails = $request->get('email');
    //         }else{
    //             $UserEmails = '';
    //         }
    //         if($user->user_type == 'driver'){
    //             $link = "https://play.google.com/store/apps/details?id=com.ruerun.taxidriverapp";
    //         } elseif($user->user_type == 'user'){
    //             $link = "https://play.google.com/store/apps/details?id=com.ruerun.user";
    //         }else{
    //             $link = "http://rueruntest.aistechnolabs.co";
    //         }

    //         foreach (@$UserEmails as $email) {
    //             InviteContactList::create(['email' => $email,'user_id' => $user->id,'link'=>$link,'description'=>$request->get('description') ]);
    //             $emailcontent = array (
    //                 'text' => $request->get('description'),
    //                 'title' => "Invitation",
    //                 'link' => $link,
    //                 'userName' => $user->first_name
    //                 );
    //             $details['from_email'] = $user->email;
    //             $details['email'] = $email;
    //             $details['username'] = $user->first_names;
    //             $details['subject'] = 'Ruerun Invitation ';
    //             dispatch(new sendInvitation($details,$emailcontent));
    //         }
    //         return response()->json(['status'=>'success','message'=> 'Invitation Sent Successfully!']);
    //         } else {
    //             return response()->json(['status'=>'error','message'=> 'You can invite only 1 contact in 24 hours.']);
    //         }
    //     }catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
    //     }
    // }

    public function createemailinvitecontact(Request $request){
        $validation_array =[
        'email' => 'required',
        'description' => 'required'
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $current_date = date("Y-m-d");
            $updatedUserEmail = explode(',', $request->email);

            $android_driver = Setting::where('code','android_driver')->first();
            $android_user = Setting::where('code','android_user')->first();
            $ios_driver = Setting::where('code','ios_driver')->first();
            $ios_user = Setting::where('code','ios_user')->first();

            if($user->device_type == 'android'){
                if($user->user_type == 'driver'){
                    $link = $android_driver->value;
                } elseif($user->user_type == 'user'){
                    $link = $android_user->value;
                }else{
                    $link = "http://rueruntest.aistechnolabs.co";
                }
            }else{
                if($user->user_type == 'driver'){
                    $link = $ios_driver->value;
                } elseif($user->user_type == 'user'){
                    $link = $ios_user->value;
                }else{
                    $link = "http://rueruntest.aistechnolabs.co";
                }
            }

            foreach ($updatedUserEmail  as $value) {
                $check_InviteContactList = InviteContactList::where('user_id',$user->id)->where('email',$value)->where('created_at','>=',$current_date)->orderBy('id','DESC')->first();
                if(empty($check_InviteContactList)){
                    InviteContactList::create(['email' => $value,'user_id' => $user->id,'link'=>$link,'description'=>$request->get('description') ]);
                    $emailcontent = array (
                        'text' => $request->get('description'),
                        'title' => "Invitation",
                        'link' => $link,
                        'code' => $user->uuid,
                        'userName' => $user->first_name
                        );
                    $details['from_email'] = $user->email;
                    $details['email'] = $value;
                    $details['username'] = $user->first_names;
                    $details['subject'] = 'Ruerun Invitation ';
                    dispatch(new sendInvitation($details,$emailcontent));
                }
            }
            return response()->json(['status'=>'success','message'=> "Invitation Sent Successfully!"]);
        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }


    // public function createinvitecontact(Request $request){
    //     $validation_array =[
    //     'contact' => 'required',
    //     'description' => 'required'
    //     ];
    //     $validation = Validator::make($request->all(),$validation_array);
    //     if($validation->fails()){
    //         return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
    //     }
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         $current_date = date("Y-m-d");
    //         // dd($current_date);
    //         $check_InviteContactList = InviteContactList::where('user_id',$user->id)->where('contact_number',$request->contact)->where('created_at','>=',$current_date)->orderBy('id','DESC')->first();
    //         // dd($check_InviteContactList);
    //         if(empty($check_InviteContactList)){
    //         if($request->get('contact')){
    //             $UserContact = $request->get('contact');
    //         }else{
    //             $UserContact = '';
    //         }
    //         if($user->user_type == 'driver'){
    //             $link = "https://play.google.com/store/apps/details?id=com.ruerun.taxidriverapp";
    //         } elseif($user->user_type == 'user'){
    //             $link = "https://play.google.com/store/apps/details?id=com.ruerun.user";
    //         }else{
    //             $link = "http://rueruntest.aistechnolabs.co";
    //         }
    //         // dd($UserContact,$user->id,$link,$request->description);

    //         foreach (@$UserContact as $contact) {
    //             InviteContactList::create([
    //                 'contact_number' => $contact,
    //                 'user_id' => $user->id,
    //                 'link'=>$link,
    //                 'description'=>$request->get('description') 
    //             ]);
    //             // $emailcontent = array (
    //             //     'text' => $request->get('description'),
    //             //     'title' => "Invitation",
    //             //     'link' => $link,
    //             //     'userName' => $user->first_name
    //             //     );
    //             // $details['from_email'] = $user->email;
    //             // $details['email'] = $email;
    //             // $details['username'] = $user->first_names;
    //             // $details['subject'] = 'Ruerun Invitation ';
    //             // dispatch(new sendInvitation($details,$emailcontent));
    //         }
    //         return response()->json(['status'=>'success','message'=> 'Invitation Sent Successfully!']);
    //         } else {
    //             return response()->json(['status'=>'error','message'=> 'You can invite only 1 contact in 24 hours.']);
    //         }
    //     }catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
    //     }
    // }
    public function createinvitecontact(Request $request){
        $validation_array =[
        'contact' => 'required',
        'description' => 'required'
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $current_date = date("Y-m-d");
            $updatedUserEmail = explode(',', $request->contact);

            $android_driver = Setting::where('code','android_driver')->first();
            $android_user = Setting::where('code','android_user')->first();
            $ios_driver = Setting::where('code','ios_driver')->first();
            $ios_user = Setting::where('code','ios_user')->first();

            if($user->device_type == 'android'){
                if($user->user_type == 'driver'){
                    $link = $android_driver->value;
                } elseif($user->user_type == 'user'){
                    $link = $android_user->value;
                }else{
                    $link = "http://rueruntest.aistechnolabs.co";
                }
            }else{
                if($user->user_type == 'driver'){
                    $link = $ios_driver->value;
                } elseif($user->user_type == 'user'){
                    $link = $ios_user->value;
                }else{
                    $link = "http://rueruntest.aistechnolabs.co";
                }
            }
            foreach ($updatedUserEmail  as $value) {
                $check_InviteContactList = InviteContactList::where('user_id',$user->id)->where('contact_number',$value)->where('created_at','>=',$current_date)->orderBy('id','DESC')->first();
                if(empty($check_InviteContactList)){
                    InviteContactList::create(['contact_number' => $value,'user_id' => $user->id,'link'=>$link,'description'=>$request->get('description') ]);
                }
            }
            return response()->json(['status'=>'success','message'=> "Invitation Sent Successfully!"]);
        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Invite Contact Users And Driver
    |--------------------------------------------------------------------------
    |
    */
    public function invitecontact(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $user_code = $user->uuid;
            $data_ref = User::where('ref_id',$user_code)->get();
            if(count($data_ref) != 0){
                return response()->json(['status'=>'success','message'=> 'You are successfully Invite Contact!','data'=> $data_ref]);
            }else{
                return response()->json(['status'=> 'error','message'=>'Invite Contact Not Found']);
            }
        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Old Invite Contact List
    |--------------------------------------------------------------------------
    |
    */
    public function old_invitecontactlist(Request $request){
        try {
            $validation_array =[
            'contact' => 'required',
            ];
            $validation = Validator::make($request->all(),$validation_array);
            if($validation->fails()){
                return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
            }
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $contact_list = $request->contact;
            $register_contact = User::pluck('contact_number')->toArray();
            $unregister_contact = array_diff($contact_list,$register_contact);
            $register_contact = array_intersect($register_contact,$contact_list);
            $register_contact = array_values($register_contact);
            $unregister_contact = array_values($unregister_contact);
            return Response::json(['status'=> 'success','register_contact'=> $register_contact,'unregister_contact'=>$unregister_contact]);
        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Invite Contact List
    |--------------------------------------------------------------------------
    |
    */

    public function invitecontactlistIOS(Request $request){
        try {

            $rules =[
            'users' => 'array|min:1',
            ];

            $updatedUserData = explode(',', $request->name);

            $updatedContactData = explode(',', $request->contact_number);
            $updatedImageData = explode(',', $request->image);
            $usernameData = [];
            foreach ($updatedUserData  as $key=>$value) {
                $usernameData['name'][$key]['name'] = $value;
            }
            $userDetailsusername[] = $usernameData['name'];
            foreach ($updatedContactData  as $key=>$value) {
                $usernameData['contact'][$key]['number'] = $value;
            }
            $userDetailsusername[] = $usernameData['contact'];
            foreach ($updatedImageData  as $key=>$value) {
                $usernameData['image'][$key]['image'] = $value;
            }
            $userDetailsusername[] = $usernameData['image'];

            $name = Arr::pluck($userDetailsusername[0], 'name');
            $number = Arr::pluck($userDetailsusername[1], 'number');
            $image = Arr::pluck($userDetailsusername[2], 'image');
            $register_contact = User::pluck('contact_number');

            $regiserUser = [];
            $nonRegiserUser = [];
            for ($i=0; $i <count($name) ; $i++) {
                if(in_array($number[$i],$register_contact->toArray())){
                    $regiserUser[] = [
                    'contact_number'  => $number[$i],
                    'image'   => $image[$i],
                    'name'    => $name[$i],
                    ];
                }else{
                    $nonRegiserUser[] = [
                    'contact_number'  => $number[$i],
                    'image'   => $image[$i],
                    'name'    => $name[$i],
                    ];
                }
            }

            return Response::json(['status'=> 'success','register_contact'=> $regiserUser,'unregister_contact'=>$nonRegiserUser]);

        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }


    public function invitecontactlistAndroid(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            // $rules =[
            // 'users' => 'array|min:1',
            // ];

            // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Request.log';
            // //file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "All_Request: ".json_encode($request->all)."\n", FILE_APPEND);

            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForName: ".$request->invite_username."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForNumber: ".$request->contact_number."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForImage: ".$request->image."\n", FILE_APPEND);


            // dd($request->invite_username,$request->image);
            $updatedUserData = $request->invite_username;
            $updatedContactData = $request->contact_number;
            $updatedImageData = $request->image;
            // $updatedUserData = explode(',', $request->invite_username);
            // $updatedContactData = explode(',', $request->contact_number);
            // $updatedImageData = explode(',', $request->image);
            $usernameData = [];
            foreach ($updatedUserData  as $key=>$value) {
                $usernameData['name'][$key]['name'] = $value;
            }
            $userDetailsusername[] = $usernameData['name'];
            foreach ($updatedContactData  as $key=>$value) {
                $usernameData['contact'][$key]['number'] = $value;
            }
            $userDetailsusername[] = $usernameData['contact'];
            foreach ($updatedImageData  as $key=>$value) {
                $usernameData['image'][$key]['image'] = $value;
            }
            $userDetailsusername[] = $usernameData['image'];

            $name = Arr::pluck($userDetailsusername[0], 'name');
            $number = Arr::pluck($userDetailsusername[1], 'number');
            $image = Arr::pluck($userDetailsusername[2], 'image');
            $register_contact = User::pluck('contact_number');
            // echo "<pre>";
            // print_r($userDetailsusername);
            // die();
            $current_date = date("Y-m-d");
            $regiserUser = [];
            $nonRegiserUser = [];
            for ($i=0; $i <count($name) ; $i++) {
                if(in_array(@$number[$i],@$register_contact->toArray())){
                    $regiserUser[] = [
                    'contact_number'  => @$number[$i],
                    'image'   => @$image[$i],
                    'name'    => @$name[$i],
                    ];
                }else{
                    if(!empty($number[$i])){

                        $check = InviteContactList::where('user_id',$user->id)->where('contact_number',@$number[$i])->where('created_at','>=',$current_date)->first();
                        if(!empty($check)){
                            $nonRegiserUser[] = [
                            'contact_number'  => @$number[$i],
                            'image'   => @$image[$i],
                            'name'    => @$name[$i],
                            'flage'   => "invite",
                            ];
                        } else {


                            $nonRegiserUser[] = [
                            'contact_number'  => @$number[$i],
                            'image'   => @$image[$i],
                            'name'    => @$name[$i],
                            'flage'   => "Notinvite",
                            ];
                        }
                    }
                }
            }

            // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Response.log';
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_Register: ".json_encode($regiserUser)."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_Unregister: ".json_encode($nonRegiserUser)."\n", FILE_APPEND);
            // $user_code = $user->uuid;
            // $data_ref = User::where('ref_id',$user_code)->get();
            // if(count($data_ref) != 0){
            //     $data_ref = $data_ref;
            // }else{
            //     $data_ref = [];
            //     // return response()->json(['status'=> 'error','message'=>'Invite Contact Not Found']);
            // }

            // return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser,'data'=>$data_ref]);
            return Response::json(['status'=> 'success','register_contact'=> $regiserUser,'unregister_contact'=>$nonRegiserUser]);

        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }

    // public function invitecontactlist(Request $request){
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         // $rules =[
    //         // 'users' => 'array|min:1',
    //         // ];

    //         // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Request.log';
    //         // //file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "All_Request: ".json_encode($request->all)."\n", FILE_APPEND);

    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForName: ".$request->invite_username."\n", FILE_APPEND);
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForNumber: ".$request->contact_number."\n", FILE_APPEND);
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForImage: ".$request->image."\n", FILE_APPEND);


    //         // dd($request->invite_username,$request->image);
    //         // $updatedUserData = $request->invite_username;
    //         // $updatedContactData = $request->contact_number;
    //         // $updatedImageData = $request->image;
    //         $updatedUserData = explode(',', $request->invite_username);
    //         $updatedContactData1 = explode(',',$request->contact_number);
    //         $name1 = str_replace(' ', '', $updatedContactData1);
    //         $updatedContactData = str_replace('-', '', $name1);
    //         $updatedImageData = explode(',', $request->image);
    //         $usernameData = [];
    //         Log::info(json_encode($request->all()));
    //         foreach ($updatedUserData  as $key=>$value) {
    //             $usernameData['name'][$key]['name'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['name'];
    //         foreach ($updatedContactData  as $key=>$value) {
    //             $usernameData['contact'][$key]['number'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['contact'];
    //         foreach ($updatedImageData  as $key=>$value) {
    //             $usernameData['image'][$key]['image'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['image'];

    //         $name = Arr::pluck($userDetailsusername[0], 'name');
    //         $number = Arr::pluck($userDetailsusername[1], 'number');
    //         $image = Arr::pluck($userDetailsusername[2], 'image');
    //         // dd($number);
    //         $register_contact = User::where('ref_id',$user->uuid)->pluck('contact_number');
    //         // echo "<pre>";
    //         // print_r($register_contact);
    //         // die();
    //         $current_date = date("Y-m-d");
    //         $regiserUser = [];
    //         $nonRegiserUser = [];
    //         for ($i=0; $i <count($name) ; $i++) {
    //             if(in_array(@$number[$i],@$register_contact->toArray())){
    //                 $regiserUser[] = [
    //                     'contact_number'  => @$number[$i],
    //                     'image'   => @$image[$i],
    //                     'name'    => @$name[$i],
    //                 ];
    //             }else{
    //                 if(!empty($number[$i])){
    //                     $check = InviteContactList::where('user_id',$user->id)->where('contact_number',@$number[$i])->where('created_at','>=',$current_date)->first();
    //                     if(!empty($check)){
    //                     $nonRegiserUser[] = [
    //                         'contact_number'  => @$number[$i],
    //                         'image'   => @$image[$i],
    //                         'name'    => @$name[$i],
    //                         'flage'   => "invite",
    //                     ];
    //                     } else {


    //                     $nonRegiserUser[] = [
    //                         'contact_number'  => @$number[$i],
    //                         'image'   => @$image[$i],
    //                         'name'    => @$name[$i],
    //                         'flage'   => "Notinvite",
    //                     ];
    //                     }
    //                 }
    //             }
    //         }

    //         // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Response.log';
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_Register: ".json_encode($regiserUser)."\n", FILE_APPEND);
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_Unregister: ".json_encode($nonRegiserUser)."\n", FILE_APPEND);
    //         // $user_code = $user->uuid;
    //         // $data_ref = User::where('ref_id',$user_code)->get();
    //         // if(count($data_ref) != 0){
    //         //     $data_ref = $data_ref;
    //         // }else{
    //         //     $data_ref = [];
    //         //     // return response()->json(['status'=> 'error','message'=>'Invite Contact Not Found']);
    //         // }

    //         // return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser,'data'=>$data_ref]);
    //         return Response::json(['status'=> 'success','register_contact'=> $regiserUser,'unregister_contact'=>$nonRegiserUser]);

    //     }catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
    //     }
    // }

    public function invitecontactlist(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }


            // $rules =[
            // 'users' => 'array|min:1',
            // ];

            // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Request.log';
            // //file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "All_Request: ".json_encode($request->all)."\n", FILE_APPEND);

            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForName: ".$request->invite_username."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForNumber: ".$request->contact_number."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_RequestForImage: ".$request->image."\n", FILE_APPEND);


            // dd($request->invite_username,$request->image);
            // $updatedUserData = $request->invite_username;
            // $updatedContactData = $request->contact_number;
            // $updatedImageData = $request->image;
            $updatedUserData = explode(',', $request->invite_username);
            $updatedContactData1 = explode(',',$request->contact_number);
            $name1 = str_replace(' ', '', $updatedContactData1);
            $updatedContactData1 = str_replace('-', '', $name1);
            // dd($updatedContactData);


            $updatedContactData = array();
            foreach ($updatedContactData1 as $key=>$value) {
                // echo "<br>";
                // echo $value;
                $updatedContactData[] = substr($value, -10);
                //$updatedContactData = $updatedContactData11;
            }
             // dd($updatedContactData);
            // die();
            // dd($updatedContactData);
            // dd(substr($updatedContactData2,-10));
            // $updatedContactData =substr($updatedContactData2,-10);
            // dd($updatedContactData);
            $updatedImageData = explode(',', $request->image);
            $usernameData = [];

            Log::info(json_encode($request->all()));

            foreach ($updatedUserData  as $key=>$value) {
                $usernameData['name'][$key]['name'] = $value;
            }
            $userDetailsusername[] = $usernameData['name'];

            foreach ($updatedContactData  as $key=>$value) {
                $usernameData['contact'][$key]['number'] = $value;

            }
            $userDetailsusername[] = $usernameData['contact'];
            foreach ($updatedImageData  as $key=>$value) {
                $usernameData['image'][$key]['image'] = $value;
            }
            $userDetailsusername[] = $usernameData['image'];

            $name = Arr::pluck($userDetailsusername[0], 'name');
            $number = Arr::pluck($userDetailsusername[1], 'number');
            $image = Arr::pluck($userDetailsusername[2], 'image');
            // dd($number);
            // $register_contact = User::pluck('contact_number');
            $register_contact = User::where('ref_id',$user->uuid)->pluck('contact_number');
            // echo "<pre>";
            // print_r($value);
            // echo "<br>";
            $current_date = date("Y-m-d");
            $regiserUser = [];
            $nonRegiserUser = [];
            for ($i=0; $i <count($name) ; $i++) {
                if(in_array(@$number[$i],@$register_contact->toArray())){
                    $regiserUser[] = [
                    'contact_number'  => @$number[$i],
                    'image'   => @$image[$i],
                    'name'    => @$name[$i],
                    ];
                }else{
                    if(!empty($number[$i])){
                        $check = InviteContactList::where('user_id',$user->id)->where('contact_number',@$number[$i])->where('created_at','>=',$current_date)->first();
                        if(!empty($check)){
                            $nonRegiserUser[] = [
                            'contact_number'  => @$number[$i],
                            'image'   => @$image[$i],
                            'name'    => @$name[$i],
                            'flage'   => "invite",
                            ];
                        } else {


                            $nonRegiserUser[] = [
                            'contact_number'  => @$number[$i],
                            'image'   => @$image[$i],
                            'name'    => @$name[$i],
                            'flage'   => "Notinvite",
                            ];
                        }
                    }
                }
            }

            // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Response.log';
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_Register: ".json_encode($regiserUser)."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "_Unregister: ".json_encode($nonRegiserUser)."\n", FILE_APPEND);
            // $user_code = $user->uuid;
            // $data_ref = User::where('ref_id',$user_code)->get();
            // if(count($data_ref) != 0){
            //     $data_ref = $data_ref;
            // }else{
            //     $data_ref = [];
            //     // return response()->json(['status'=> 'error','message'=>'Invite Contact Not Found']);
            // }

            // return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser,'data'=>$data_ref]);
            return Response::json(['status'=> 'success','register_contact'=> $regiserUser,'unregister_contact'=>$nonRegiserUser]);

        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Old Invite Contact List Email
    |--------------------------------------------------------------------------
    |
    */

    public function invitecontactlistemailAndroid(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            // $rules =[
            // 'users' => 'array|min:1',
            // ];

            // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Request.log';


            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForEmail: ".$request->invite_email."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForName: ".$request->invite_username."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForNumber: ".$request->contact_number."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForImage: ".$request->image."\n", FILE_APPEND);


            // $updatedUserData = explode(',', $request->invite_username);
            // $updatedUserEmail = explode(',', $request->invite_email);
            // $updatedContactData = explode(',', $request->contact_number);
            // $updatedImageData = explode(',', $request->image);

            $updatedUserData = $request->invite_username;
            $updatedUserEmail = $request->invite_email;
            $updatedContactData = $request->contact_number;
            $updatedImageData = $request->image;

            $usernameData = [];
            foreach ($updatedUserData  as $key=>$value) {
                $usernameData['name'][$key]['name'] = $value;
            }
            $userDetailsusername[] = $usernameData['name'];
            foreach ($updatedUserEmail  as $key=>$value) {
                $usernameData['email'][$key]['email'] = $value;
            }

            $userDetailsusername[] = $usernameData['email'];
            foreach ($updatedContactData  as $key=>$value) {
                $usernameData['contact'][$key]['number'] = $value;
            }
            $userDetailsusername[] = $usernameData['contact'];
            foreach ($updatedImageData  as $key=>$value) {
                $usernameData['image'][$key]['image'] = $value;
            }
            $userDetailsusername[] = $usernameData['image'];

            $name = Arr::pluck($userDetailsusername[0], 'name');
            $email = Arr::pluck($userDetailsusername[1], 'email');
            $number = Arr::pluck($userDetailsusername[2], 'number');
            $image = Arr::pluck($userDetailsusername[3], 'image');
            $register_contact = User::pluck('email');
            // echo "<pre>";
            // print_r($userDetailsusername);
            // die();
            $current_date = date("Y-m-d");
            $regiserUser = [];
            $nonRegiserUser = [];
            for ($i=0; $i <count($name) ; $i++) {
                if(in_array(@$email[$i],@$register_contact->toArray())){
                    $regiserUser[] = [
                    'email'=> @$email[$i],
                    'contact_number'  => @$number[$i],
                    'image'   => @$image[$i],
                    'name'    => @$name[$i],
                    ];
                }else{
                    if(!empty($email[$i])){
                        $check = InviteContactList::where('user_id',$user->id)->where('email',@$email[$i])->where('created_at','>=',$current_date)->first();
                        if(!empty($check)){
                            $nonRegiserUser[] = [
                            'email'=> @$email[$i],
                            'contact_number'  => @$number[$i],
                            'image'   => @$image[$i],
                            'name'    => @$name[$i],
                            'flage'   => "invite",
                            ];    
                        } else {
                            $nonRegiserUser[] = [
                            'email'=> @$email[$i],
                            'contact_number'  => @$number[$i],
                            'image'   => @$image[$i],
                            'name'    => @$name[$i],
                            'flage'   => "Notinvite",
                            ];    
                        }

                        // Invite_contact::create([
                        //     'user_id'=> @$user->id,
                        //     'contact_number'=> @$number[$i],
                        // ]);
                    }
                }
            }

            // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Response.log';
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_Register: ".json_encode($regiserUser)."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_Unregister: ".json_encode($nonRegiserUser)."\n", FILE_APPEND);
            // $user_code = $user->uuid;
            // $data_ref = User::where('ref_id',$user_code)->get();
            // if(count($data_ref) != 0){
            //     $data_ref = $data_ref;
            // }else{
            //     $data_ref = [];
            //     // return response()->json(['status'=> 'error','message'=>'Invite Contact Not Found']);
            // }

            // return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser,'data'=>$data_ref]);
            return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser]);

        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }
    
    
    public function invitecontactlistemail(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            // $rules =[
            // 'users' => 'array|min:1',
            // ];

            // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Request.log';


            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForEmail: ".$request->invite_email."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForName: ".$request->invite_username."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForNumber: ".$request->contact_number."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForImage: ".$request->image."\n", FILE_APPEND);


            $updatedUserData = explode(',', $request->invite_username);
            $updatedUserEmail = explode(',', $request->invite_email);
            $updatedContactData = explode(',', $request->contact_number);
            $updatedImageData = explode(',', $request->image);

            $usernameData = [];
            foreach ($updatedUserData  as $key=>$value) {
                $usernameData['name'][$key]['name'] = $value;
            }
            $userDetailsusername[] = $usernameData['name'];
            foreach ($updatedUserEmail  as $key=>$value) {
                $usernameData['email'][$key]['email'] = $value;
            }

            $userDetailsusername[] = $usernameData['email'];
            foreach ($updatedContactData  as $key=>$value) {
                $usernameData['contact'][$key]['number'] = $value;
            }
            $userDetailsusername[] = $usernameData['contact'];
            foreach ($updatedImageData  as $key=>$value) {
                $usernameData['image'][$key]['image'] = $value;
            }
            $userDetailsusername[] = $usernameData['image'];

            $name = Arr::pluck($userDetailsusername[0], 'name');
            $email = Arr::pluck($userDetailsusername[1], 'email');
            $number = Arr::pluck($userDetailsusername[2], 'number');
            $image = Arr::pluck($userDetailsusername[3], 'image');
            // $register_contact = User::pluck('email');
            $register_contact = User::where('ref_id',$user->uuid)->pluck('email');
            // echo "<pre>";
            // print_r($userDetailsusername);
            // die();
            $current_date = date("Y-m-d");
            $regiserUser = [];
            $nonRegiserUser = [];
            for ($i=0; $i <count($name) ; $i++) {
                if(in_array(@$email[$i],@$register_contact->toArray())){
                    $regiserUser[] = [
                    'email'=> @$email[$i],
                    'contact_number'  => @$number[$i],
                    'image'   => @$image[$i],
                    'name'    => @$name[$i],
                    ];
                }else{
                    if(!empty($email[$i])){
                        $check = InviteContactList::where('user_id',$user->id)->where('email',@$email[$i])->where('created_at','>=',$current_date)->first();
                        if(!empty($check)){
                            $nonRegiserUser[] = [
                            'email'=> @$email[$i],
                            'contact_number'  => @$number[$i],
                            'image'   => @$image[$i],
                            'name'    => @$name[$i],
                            'flage'   => "invite",
                            ];    
                        } else {
                            $nonRegiserUser[] = [
                            'email'=> @$email[$i],
                            'contact_number'  => @$number[$i],
                            'image'   => @$image[$i],
                            'name'    => @$name[$i],
                            'flage'   => "Notinvite",
                            ];    
                        }

                        // Invite_contact::create([
                        //     'user_id'=> @$user->id,
                        //     'contact_number'=> @$number[$i],
                        // ]);
                    }
                }
            }

            // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Response.log';
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_Register: ".json_encode($regiserUser)."\n", FILE_APPEND);
            // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_Unregister: ".json_encode($nonRegiserUser)."\n", FILE_APPEND);
            // $user_code = $user->uuid;
            // $data_ref = User::where('ref_id',$user_code)->get();
            // if(count($data_ref) != 0){
            //     $data_ref = $data_ref;
            // }else{
            //     $data_ref = [];
            //     // return response()->json(['status'=> 'error','message'=>'Invite Contact Not Found']);
            // }

            // return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser,'data'=>$data_ref]);
            return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser]);

        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }
    // public function invitecontactlistemail(Request $request){
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         // $rules =[
    //         // 'users' => 'array|min:1',
    //         // ];

    //         // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Request.log';


    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForEmail: ".$request->invite_email."\n", FILE_APPEND);
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForName: ".$request->invite_username."\n", FILE_APPEND);
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForNumber: ".$request->contact_number."\n", FILE_APPEND);
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_RequestForImage: ".$request->image."\n", FILE_APPEND);


    //         $updatedUserData = explode(',', $request->invite_username);
    //         $updatedUserEmail = explode(',', $request->invite_email);
    //         $updatedContactData = explode(',', $request->contact_number);
    //         $updatedImageData = explode(',', $request->image);

    //         $usernameData = [];
    //         foreach ($updatedUserData  as $key=>$value) {
    //             $usernameData['name'][$key]['name'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['name'];
    //         foreach ($updatedUserEmail  as $key=>$value) {
    //             $usernameData['email'][$key]['email'] = $value;
    //         }

    //         $userDetailsusername[] = $usernameData['email'];
    //         foreach ($updatedContactData  as $key=>$value) {
    //             $usernameData['contact'][$key]['number'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['contact'];
    //         foreach ($updatedImageData  as $key=>$value) {
    //             $usernameData['image'][$key]['image'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['image'];

    //         $name = Arr::pluck($userDetailsusername[0], 'name');
    //         $email = Arr::pluck($userDetailsusername[1], 'email');
    //         $number = Arr::pluck($userDetailsusername[2], 'number');
    //         $image = Arr::pluck($userDetailsusername[3], 'image');
    //         $register_contact = User::pluck('email');
    //         // echo "<pre>";
    //         // print_r($userDetailsusername);
    //         // die();
    //         $regiserUser = [];
    //         $nonRegiserUser = [];
    //         for ($i=0; $i <count($name) ; $i++) {
    //             if(in_array(@$email[$i],@$register_contact->toArray())){
    //                 $regiserUser[] = [
    //                     'email'=> @$email[$i],
    //                     'contact_number'  => @$number[$i],
    //                     'image'   => @$image[$i],
    //                     'name'    => @$name[$i],
    //                 ];
    //             }else{
    //                 if(!empty($email[$i])){
    //                     $nonRegiserUser[] = [
    //                         'email'=> @$email[$i],
    //                         'contact_number'  => @$number[$i],
    //                         'image'   => @$image[$i],
    //                         'name'    => @$name[$i],
    //                     ];
    //                 }
    //             }
    //         }

    //         // $path = storage_path().'/logs/'.date("d-m-Y").'_InvitecontactLogs_Response.log';
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_Register: ".json_encode($regiserUser)."\n", FILE_APPEND);
    //         // file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Email_Unregister: ".json_encode($nonRegiserUser)."\n", FILE_APPEND);
    //         // $user_code = $user->uuid;
    //         // $data_ref = User::where('ref_id',$user_code)->get();
    //         // if(count($data_ref) != 0){
    //         //     $data_ref = $data_ref;
    //         // }else{
    //         //     $data_ref = [];
    //         //     // return response()->json(['status'=> 'error','message'=>'Invite Contact Not Found']);
    //         // }

    //         // return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser,'data'=>$data_ref]);
    //         return Response::json(['status'=> 'success','register_email'=> $regiserUser,'unregister_email'=>$nonRegiserUser]);

    //     }catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
    //     }
    // }
    // public function joincontactemail(Request $request){
    //     try {
    //         $updatedUserData = explode(',', $request->invite_username);
    //         $updatedContactData = explode(',', $request->contact_number);
    //         $updatedImageData = explode(',', $request->image);
    //         $updatedEmailData = explode(',', $request->email);
    //         $usernameData = [];
    //         foreach ($updatedUserData  as $key=>$value) {
    //             $usernameData['name'][$key]['name'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['name'];
    //         foreach ($updatedContactData  as $key=>$value) {
    //             $usernameData['contact'][$key]['number'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['contact'];
    //         foreach ($updatedImageData  as $key=>$value) {
    //             $usernameData['image'][$key]['image'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['image'];

    //         foreach ($updatedEmailData  as $key=>$value) {
    //             $usernameData['email'][$key]['email'] = $value;
    //         }
    //         $userDetailsusername[] = $usernameData['email'];

    //         $name = Arr::pluck($userDetailsusername[0], 'name');
    //         $number = Arr::pluck($userDetailsusername[1], 'number');
    //         $image = Arr::pluck($userDetailsusername[2], 'image');
    //         $email = Arr::pluck($userDetailsusername[3], 'email');
    //         $register_contact = User::pluck('contact_number');

    //         $nonRegiserUser = [];
    //         for ($i=0; $i <count($name) ; $i++) {
    //                 if(!empty($name[$i])){
    //                     $nonRegiserUser[] = [
    //                         'contact_number'  => @$number[$i],
    //                         'image'   => @$image[$i],
    //                         'name'    => @$name[$i],
    //                         'email'    => @$email[$i],
    //                     ];
    //                 }
    //         }

    //         return Response::json(['status'=> 'success','joincontactemail'=>$nonRegiserUser]);

    //     }catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
    //     }
    // }
    public function joincontactemail(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $user_email_check = User::where('ref_id',$user->uuid)->get();
            if(sizeof($user_email_check) > 0){
                return Response::json(['status'=> 'success','data'=>$user_email_check]);
            }else{
                return Response::json(['status'=> 'error','data'=>'No Any referrals']);
            }
            // $updatedUserData = explode(',', $request->invite_username);
            // $updatedContactData1 = explode(',', $request->contact_number);
            // $updatedContactData = str_replace('-','', $updatedContactData1);
            // $updatedImageData = explode(',', $request->image);
            // $updatedEmailData = explode(',', $request->email);
            // $usernameData = [];
            // foreach ($updatedUserData  as $key=>$value) {
            //     $usernameData['name'][$key]['name'] = $value;
            // }
            // $userDetailsusername[] = $usernameData['name'];
            // foreach ($updatedContactData  as $key=>$value) {
            //     $usernameData['contact'][$key]['number'] = $value;
            // }
            // $userDetailsusername[] = $usernameData['contact'];
            // foreach ($updatedImageData  as $key=>$value) {
            //     $usernameData['image'][$key]['image'] = $value;
            // }
            // $userDetailsusername[] = $usernameData['image'];
            // $user_email_check = User::whereIn('email',$updatedEmailData)->orwhereIn('contact_number',$updatedContactData)->get();
            // $nonRegiserUser = [];
            // if(count($user_email_check)>0){
            //     foreach ($user_email_check as $key => $values) {
            //         if($values->ref_id == $user->uuid){
            //             // foreach ($user_email_check  as $key=>$value) {
            //             //     $usernameData['email'][$key]['email'] = $value;
            //             // }

            //             // $userDetailsusername[] = $usernameData['email'];
            //             // $name = Arr::pluck($userDetailsusername[0], 'name');
            //             // $number = Arr::pluck($userDetailsusername[1], 'number');
            //             // $image = Arr::pluck($userDetailsusername[2], 'image');
            //             // $email = Arr::pluck($userDetailsusername[3], 'email');
            //             // $register_contact = User::pluck('contact_number');

            //             $nonRegiserUser[] = [
            //                             'contact_number'  => @$values->contact_number,
            //                             'image'   => @$values->avatar,
            //                             'name'    => @$values->first_name,
            //                             'email'    => @$values->email,
            //                         ];


            //         }
            //     }
            //     return Response::json(['status'=> 'success','joincontactemail'=>$nonRegiserUser]);
            // }else{
            //     return Response::json(['status'=> 'error','message'=>'No any referrals','joincontactemail'=>$nonRegiserUser]);
            // }
        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Set User Wallet
    |--------------------------------------------------------------------------
    |
    */
    public function setUserWallet(Request $request){
        $validation_array =[
        'amount' => 'required'
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $driver_id = $user->id;
            $checkwallet = Wallet::where('user_id',$driver_id)->first();
            if(!empty($checkwallet)){
                $amount = (int)$checkwallet->amount + (int)$request->amount;
                Wallet::where('user_id', (int)$driver_id)->update(['amount' => $amount]);
                WalletHistory::create([
                    'user_id'       => $driver_id,
                    'amount'        => (int)$request->amount,
                    'description'   => '$'.(int)$request->amount.' added to Your Wallet'
                    ]);
                return response()->json(['status' => 'success','message' => 'Wallet Amount Updated Successfully', 'data'=> Wallet::where('user_id',$driver_id)->first()]);
            }else{
                $userwallet = Wallet::create(['user_id' => $driver_id,'amount'=>$request->amount]);
                WalletHistory::create([
                    'user_id'       => $driver_id,
                    'amount'        => $request->amount,
                    'description'   => '$'.$request->amount.' added to Your Wallet'
                    ]);
                return response()->json(['status' => 'success','message' => 'Wallet Amount Saved uccessfully', 'data'=>Wallet::where('user_id',$driver_id)->first()]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Wallet History
    |--------------------------------------------------------------------------
    |
    */
    public function getWalletHistory(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $user_id = $user->id;
            $checkwallet = Wallet::where('user_id',$user_id)->first();
            $userwallethistory = WalletHistory::with('refer_user_details','user_details')->where('user_id',$user_id)->get();
            if(!empty($checkwallet)){
                return response()->json(['status' => 'success','message' => 'Wallet Amount', 'data'=> $checkwallet, 'wallet_history'=>$userwallethistory,]);
            }else{
                return response()->json(['status' => 'error','message' => 'No Wallet Amount Found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | User Update Location
    |--------------------------------------------------------------------------
    |
    */
    public function UpdateLocation(Request $request){
        $validation_array =[
        'latitude'       => 'required',
        'longitude'      => 'required',
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status'    => 'error','message'   => $validation->errors()->first(),'data' => (object)[]]);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $user_data = User::where('id',$user->id)->first();
            if($user_data){
                $user_data->latitude    = request('latitude');
                $user_data->longitude   = request('longitude');
                $user_data->save();
                return response()->json(['status' => 'success','message' => 'Location Update Successfully','data' => $user_data]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Location
    |--------------------------------------------------------------------------
    |
    */
    public function getLocation(Request $request){
//        $validator = Validator::make($request->all(), [
//            'id'   => 'required',
//        ]);
//        if($validator->fails()) {
//            return response()->json(['status'   => 'error','message'  => $validator->messages()->first()]);
//        }
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }

            if($request->booking_id){
                $booking_status = Booking::where('id',request('booking_id'))->first();
            }else if($request->parcel_id){
                $booking_status = ParcelDetail::where('id',request('parcel_id'))->first();
            }else if($request->shuttle_id){
                $booking_status = LinerideUserBooking::where('id',request('shuttle_id'))->first();
            }

            $user_location = User::where('id',$booking_status->driver_id)->first();
            $user = User::where('id',$booking_status->user_id)->first();
            // $user_location = User::where('id',$user->id)->first();
            // $user = User::where('id',$user->id)->first();
            //Driver Address
            $lat= $user_location->latitude; //latitude
            $lng= $user_location->longitude; //longitude
            $driver_address = $this->getaddress($lat,$lng);
            if($driver_address){
                $destination_addresses = $driver_address;
            }else{
                echo "Not found";
            }
            //User Address
            $lat= $user->latitude; //latitude
            $lng= $user->longitude; //longitude
            $user_address = $this->getaddress($lat,$lng);
            // $address= getaddress($lat,$lng);
            if($user_address){
                $origin_addresses =$user_address;
            }else{
                echo "Not found";
            }
            $destination_addresses = $destination_addresses;
            $origin_addresses = $origin_addresses;
            $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
            $distance_arr = json_decode($distance_data);
            if ($distance_arr->status=='OK') {
                $destination_addresses = $distance_arr->destination_addresses[0];
                $origin_addresses = $distance_arr->origin_addresses[0];
            } else {
                echo "<p>The request was Invalid</p>";
                exit();
            }
            if ($origin_addresses=="" or $destination_addresses=="") {
                echo "<p>Destination or origin address not found</p>";
                exit();
            }
            // Get the elements as array
            $elements = $distance_arr->rows[0]->elements;
            $distance = $elements[0]->distance->text;
            $duration = $elements[0]->duration->text;

            if($request->booking_id || $request->shuttle_id){
                $user_location ['booking_status'] = $booking_status->trip_status;
            }else if($request->parcel_id){
                $user_location ['booking_status'] = $booking_status->parcel_status;
            }

            $avgrating = RatingReviews::where('from_user_id',$booking_status->driver_id)->orWhere('to_user_id',$booking_status->driver_id)->avg('rating');
            $user_location ['driver_avgrating'] = $avgrating;
            return response()->json(['status'  => 'success','message' => 'You are get location User And Driver successfully','data' => $user_location,'distance' => $distance,'duration' => $duration]);

        }catch(Exception $e){
            return response()->json(['status'  => 'error','message' => $e->getMessage()]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Address
    |--------------------------------------------------------------------------
    |
    */
    function getaddress($lat,$lng){
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.($lat).','.($lng).'&key='.env('GOOGLE_MAP_API_KEY');
        $json = @file_get_contents($url);
        $data=json_decode($json);
        $status = $data->status;
        if($status=="OK")
            return $data->results[0]->formatted_address;
        else
            return false;
    }

    /*
    |--------------------------------------------------------------------------
    | get User And Driver Support Category
    |--------------------------------------------------------------------------
    |
    */
    public function getTripHistory(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $user_id = $user->id;
            $tripshistory = Booking::where('driver_id', $user_id)->orWhere('user_id', $user_id)->get();
            if(!empty($tripshistory)){
                return response()->json(['status' => 'success','message' => 'Booking History', 'data'=>$tripshistory]);
            }else{
                return response()->json(['status' => 'error','message' => 'History not found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | get user/driver trip history list and details
    |--------------------------------------------------------------------------
    |
    */
    public function SupportCategory(Request $request){
        try {
            $supportcategory = SupportCategory::get();
            if(count($supportcategory) != 0){
                return response()->json(['status'=>'success','message'=> 'Support Category Listing!','data'=> $supportcategory]);
            }else{
                return response()->json(['status'=> 'error','message'=>'Support Category Listing Not Found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | get User Driver Notification
    |--------------------------------------------------------------------------
    |
    */
    public function Notification(Request $request){
        $validation_array =[
        'notification' => 'required',
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            User::where('id', $user->id)->update(['notification' => $request->notification]);
            return response()->json(['status' => 'success','message' => 'Notification Update Successfully','data' => User::find($user->id)]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | get User Driver Get Count Notification
    |--------------------------------------------------------------------------
    |
    */
    public function GetCountNotification(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $notification_count = UserNotification::where('sent_from_user',$user->id)->where('is_read','unread')->count();
            return response()->json(['status' => 'success','message' => 'All Notification Read','data' => $notification_count]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get User Driver Notification Check
    |--------------------------------------------------------------------------
    |
    */
    public function NotificationCheck(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $user_notification = User::where('id',$user->id)->first();
            $notification_list = $user_notification->notification;
            return response()->json(['status' => 'success','message' => 'Notification Successfully', 'data'=>$notification_list]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

/*
|--------------------------------------------------------------------------
| Get User Upcoming Rides
|--------------------------------------------------------------------------
|
*/
// 15 jun
// public function getUserUpcomingRides(Request $request){
    // try {
    //     $user = JWTAuth::parseToken()->authenticate();
    //     if(!$user){
    //         return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //     }
    //     $date = new DateTime();
    //     $date->setTimezone(new DateTimeZone('America/New_York'));
    //     $fdate = $date->format('Y-m-d');

    //     $date = new DateTime();
    //     $date->setTimezone(new DateTimeZone('America/New_York'));
    //     $ftime = $date->format('H:i:s');

    //     $upcomingdata = $pastdata = array();
    //     if($user->user_type == "user"){
    //         if(!empty($request->ride_setting_id)){
    //             if($request->ride_setting_id){
    //                 $upcoming = Booking::with(['driver','ride_setting'])
    //                 ->where('user_id', $user->id)
    //                 ->where('ride_setting_id', $request->ride_setting_id)
    //                 // ->where('booking_date','>=',$fdate)
    //                 ->where('booking_date','>=',date('Y-m-d'))
    //                 // ->whereTime('booking_start_time', '>=', $ftime)
    //                 ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
    //                 ->orderBy('id','DESC')->get();

    //                 $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
    //                 ->where('user_id', $user->id)
    //                 ->where('ride_setting_id', $request->ride_setting_id)
    //                 // ->where('booking_date','>=',$fdate)
    //                 ->where('booking_date','>=',date('Y-m-d'))
    //                 // ->whereTime('booking_start_time', '>=', $ftime)
    //                 ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
    //                 ->orderBy('id','DESC')->get();

    //                 $upcoming = $upcoming->merge($upcoming_shuttle);
    //                 usort($upcomingdata, $this->sortByDate('booking_date'));
    //                 array_multisort( array_column($upcomingdata, "trip_status","accepted"), SORT_ASC, $upcomingdata );
    //                 $past = Booking::with(['driver','ride_setting'])
    //                 ->whereHas('driver')
    //                 ->where('user_id', $user->id)
    //                 ->where('ride_setting_id', $request->ride_setting_id)
    //                 ->whereIn('trip_status',['completed','cancelled','emergency'])
    //                 ->orderBy('id','DESC')
    //                 ->get()->toArray();

    //                 $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
    //                 ->whereHas('driver')
    //                 ->where('user_id', $user->id)
    //                 ->where('ride_setting_id', $request->ride_setting_id)
    //                 ->whereIn('trip_status',['completed','rejected'])
    //                 ->orderBy('id','DESC')->get()->toArray();
    //                 $past = array_merge($past,$past_shuttle);
    //                 usort($past, $this->sortByDate('booking_date'));
    //                 $data['upcoming']                = $upcomingdata;
    //                 $data['past']                    = $past;
    //                 return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
    //             }
    //         } else {
    //             // $upcoming = Booking::with(['driver','ride_setting'])
    //             // ->where('user_id', $user->id)
    //             // ->where('booking_date','>=',date('Y-m-d'))
    //             // // ->where('booking_date','>=',$fdate)
    //             // // ->whereTime('booking_start_time', '>=', $ftime)
    //             // ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
    //             // ->orderBy('id','DESC')->get();

    //             // $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
    //             // ->where('user_id', $user->id)
    //             // ->where('booking_date','>=',date('Y-m-d'))
    //             // // ->where('booking_date','>=',$fdate)
    //             // // ->whereTime('booking_start_time', '>=', $ftime)
    //             // ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
    //             // ->orderBy('id','DESC')->get();
    //             // $upcomingdata = $upcoming->merge($upcoming_shuttle);

    //             $getPending = Booking::with(['driver','ride_setting'])
    //             ->where('user_id', $user->id)
    //             ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
    //             ->orderBy('id','DESC')->get();
    //             // dd($upcomingdata);
    //             if( sizeof($getPending) and !empty($getPending)){
    //                 $upcomingdata = [];
    //                 foreach ($getPending as $key => $value){
    //                     // dd($value);
    //                     // echo $value;
    //                     if($value['trip_status'] == 'pending'){

    //                         if($value['booking_type'] == "schedule"){
    //                             $date = $value['booking_date'];
    //                             $time = $value['booking_details']['booking_end_time'];
    //                             $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date.$time);
    //                             $from_time1 = $new_time->format('Y-m-d h:i');
    //                             $now = Carbon::now()->format('Y-m-d h:i');
    //                             if (\Carbon\Carbon::parse($now)->lte(\Carbon\Carbon::parse($from_time1))){
    //                                 $upcomingdata[] = $value;
    //                             }
    //                         }else{
    //                             $to_time =  strtotime("now");
    //                             $from_time = strtotime($value['created_at']);
    //                             $diff = round(abs($to_time - $from_time) / 60,10);
    //                             if($diff <= 10){
    //                                 $upcomingdata[] = $value;
    //                             }
    //                         }

    //                     }else if($value['trip_status'] == 'accepted' || $value['trip_status'] == 'on_going' || $value['trip_status'] == 'driver_arrived' || $value['trip_status'] == 'pick_up'){
    //                         if(
    //                             \Carbon\Carbon::parse($value['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
    //                             \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_date'])->format('Y-m-d')
    //                         ) {
    //                             $upcomingdata[] = $value;
    //                         }
    //                     }
    //                 }
    //             }

    //             $past = Booking::with(['driver','ride_setting'])
    //             ->whereHas('driver')
    //             ->where('user_id', $user->id)
    //             ->whereIn('trip_status',['completed','cancelled','emergency'])
    //             ->orderBy('id','DESC')
    //             ->get()->toArray();

    //             $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
    //             ->whereHas('driver')
    //             ->where('user_id', $user->id)
    //             ->whereIn('trip_status',['completed','rejected'])
    //             ->orderBy('id','DESC')->get()->toArray();
    //             $past = array_merge($past,$past_shuttle);
    //             usort($past, $this->sortByDate('booking_date'));
    //             $data['upcoming']                = $upcomingdata;
    //             $data['past']                    = $past;
    //             return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
    //         }
    //     } else {
    //         if(!empty($request->ride_setting_id)){
    //             $getPending = UserNotification::with([
    //                 'booking_details' => function($query) use ($request){
    //                     $query->where('ride_setting_id',$request->ride_setting_id);
    //                 },'shuttle_details'=> function($query) use ($request){
    //                     $query->where('ride_setting_id',$request->ride_setting_id);
    //                 },'user','driver'])->whereIn('notification_for', ['accepted'])->where('sent_to_user', $user->id)
    //             ->orderBy('id','desc')->get()->toArray();
    //             if( sizeof($getPending) and !empty($getPending)){
    //                 $upcomingdata = [];
    //                 foreach ($getPending as $key => $value){
    //                     if($value['notification_for'] == 'pending'){
    //                         $to_time =  strtotime("now");
    //                         $from_time = strtotime($value['created_at']);
    //                         $diff = round(abs($to_time - $from_time) / 60,60);
    //                         if($diff <= 60){
    //                             $upcomingdata[] = $value;
    //                         }
    //                     }else if($value['notification_for'] == 'accepted'){
    //                         if(
    //                             \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
    //                             \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') and
    //                             \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
    //                             \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d')
    //                         )
    //                             {
    //                                 if( !empty($value['booking_details']) || !empty($value['shuttle_details']))
    //                                     $upcomingdata[] = $value;
    //                             }
    //                         }
    //                     }
    //                 }
    //                 usort($upcomingdata, $this->sortByDate('created_at'));
    //                 array_multisort( array_column($upcomingdata, "notification_for","accepted"), SORT_ASC, $upcomingdata );
    //                 $past = Booking::with(['user','driver','ride_setting'])
    //                 ->where('driver_id', $user->id)
    //                 ->where('ride_setting_id', $request->ride_setting_id)
    //                 ->whereIn('trip_status',['completed','cancelled','emergency'])
    //                 ->orderBy('id','DESC')
    //                 ->get()->toArray();
    //                 $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
    //                 ->where('driver_id', $user->id)
    //                 ->where('ride_setting_id', $request->ride_setting_id)
    //                 ->whereIn('trip_status',['completed','rejected'])
    //                 ->orderBy('id','DESC')->get()->toArray();
    //                 $past = array_merge($past,$past_shuttle);
    //                 usort($past, $this->sortByDate('booking_date'));
    //                 $data['upcoming']                = $upcomingdata;
    //                 $data['past']                    = $past;
    //                 return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
    //             }else{
    //                 // dd("in");
    //             $outPending =  UserNotification::with(['out_town_data','booking_details','user','driver'])
    //             ->whereIn('notification_for', ['pending','accepted','on_going','pick_up'])
    //             ->where('sent_to_user', $user->id)->orderBy('id','desc')->groupBy('out_of_town')->get()->toArray();
    //             // dd($outPending);
    //         if( sizeof($outPending) and !empty($outPending)){
    //                 $upcomingdata1 = [];
    //                 foreach ($outPending as $key => $value){
    //                     if($value['flag_city'] == 'out_of_town'){
    //                         if(!empty($value['out_of_town'])){
    //                             // echo "<br>";
    //                             // echo $value['out_of_town'];
    //                             if($value['notification_for'] == 'accepted' || $value['notification_for'] == 'pending' || $value['notification_for'] == 'on_going' || $value['notification_for'] == 'pick_up'){
    //                              if(
    //                                 \Carbon\Carbon::parse($value['out_town_data']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
    //                                 \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['out_town_data']['booking_date'])->format('Y-m-d')
    //                             ){
    //                                $upcomingdata1[] = $value; 
    //                              }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //             // else {
    //             //     $upcomingdata1 = [];
    //             // }
    //             // die();
    //         // dd($upcomingdata1);
    //         $getPending =  UserNotification::with(['booking_details','shuttle_details','user','driver'])
    //             ->whereIn('notification_for', ['pending','accepted','on_going','pick_up'])
    //             ->where('sent_to_user', $user->id)->orderBy('id','desc')->get()->toArray();
    //             if( sizeof($getPending) and !empty($getPending)){
    //                 $upcomingdata = [];
    //                 foreach ($getPending as $key => $value){
    //                     // if($value['flag_city'] == 'out_of_town'){
    //                     //     if(!empty($value['out_of_town'])){

    //                     //         $out_of_towns = [$value['out_of_town']];

    //                     //         $out_town_data = OutTwonrideBooking::whereIn('id', $out_of_towns)->get()->toArray();
    //                     //         $prepare_out_town_data = [];
    //                     //         array_walk($out_town_data, function($value, $key) use(&$prepare_out_town_data){
    //                     //             $date = $value['booking_date'].' '.$value['booking_start_time'];
    //                     //             $date = \Carbon\Carbon::parse($date)->format('Y-m-d h:i:s');
    //                     //             $prepare_out_town_data[$key] = $value;
    //                     //             if (\Carbon\Carbon::parse($date)->gte(\Carbon\Carbon::now())) {
    //                     //                 $prepare_out_town_data[$key]['parse'] = "up_comming";
    //                     //             } else{
    //                     //                 $prepare_out_town_data[$key]['parse'] = "not_up_comming";
    //                     //             }
    //                     //         });
    //                     //         $value['out_town_data'] = $prepare_out_town_data;
    //                     //         $upcomingdata[] = $value;
    //                     //     }
    //                     // }
    //                     if(empty($value['flag_city'] == 'out_of_town')){
    //                         if($value['notification_for'] == 'pending'){
    //                             if($value['booking_details']['booking_type'] == "schedule"){
    //                                 // echo $value['booking_details']['id'];
    //                                 // echo "<br>";
    //                                 $date = $value['booking_details']['booking_date'];
    //                                 $time = $value['booking_details']['booking_end_time'];
    //                                 $new_time = DateTime::createFromFormat('Y-m-d g:i A', $date.$time);
    //                                 $from_time1 = $new_time->format('Y-m-d g:i');
    //                                 // $from_time1 = $new_time;
    //                                 // dd($from_time1);
    //                                 $ab =  $value['booking_details']['booking_date'].' '.$value['booking_details']['booking_end_time'];
    //                                 $from_time1 = str_replace("PM","",$ab);
    //                                 // dd($from_time1);
    //                                 $now = Carbon::now()->format('Y-m-d h:i');
    //                                 if (\Carbon\Carbon::parse($now)->lte(\Carbon\Carbon::parse($from_time1))){
    //                                     $upcomingdata[] = $value;
    //                                 } 
    //                             } 
    //                             else{
    //                                 $to_time =  strtotime("now");
    //                                 $from_time = strtotime($value['created_at']);
    //                                 $diff = round(abs($to_time - $from_time) / 60,10);
    //                                 if($diff <= 10){
    //                                     $upcomingdata[] = $value;
    //                                 }
    //                             }
    //                         }
    //                         else if($value['notification_for'] == 'accepted' || $value['notification_for'] == 'on_going' || $value['notification_for'] == 'pick_up'){
    //                             if(
    //                                 \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
    //                                 \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') and
    //                                 \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
    //                                 \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d')
    //                             ) {
    //                                 $upcomingdata[] = $value;
    //                             }
    //                         }
    //                     }
    //                 }
    //             }

    //             $upcomingdata2 = array_merge($upcomingdata,$upcomingdata1);
    //             usort($upcomingdata2, $this->sortByDate('created_at'));
    //             array_multisort( array_column($upcomingdata2, "notification_for","accepted"), SORT_ASC, $upcomingdata2 );
    //             $past = Booking::with(['user','driver','ride_setting'])
    //             ->where('driver_id', $user->id)
    //             ->whereIn('trip_status',['completed','cancelled','emergency'])
    //             ->orderBy('id','DESC')
    //             ->get()->toArray();

    //             $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
    //             ->where('driver_id', $user->id)
    //             ->whereIn('trip_status',['completed','rejected'])
    //             ->orderBy('id','DESC')->get()->toArray();
    //             $past = array_merge($past,$past_shuttle);
    //             $data['upcoming']                = $upcomingdata2;
    //             $data['past']                    = $past;
    //             return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
    //                 }
    //             }
    //         } catch (Exception $e) {
    //             return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    //         }
    //     }
public function getUserUpcomingRides(Request $request){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $fdate = $date->format('Y-m-d');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $ftime = $date->format('H:i:s');

        $upcomingdata = $pastdata = array();
        if($user->user_type == "user"){
            if(!empty($request->ride_setting_id)){
                if($request->ride_setting_id){
                    $upcoming = Booking::with(['driver','ride_setting'])
                    ->where('user_id', $user->id)
                    ->where('ride_setting_id', $request->ride_setting_id)
                    // ->where('booking_date','>=',$fdate)
                    ->where('booking_date','>=',date('Y-m-d'))
                    // ->whereTime('booking_start_time', '>=', $ftime)
                    ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
                    ->orderBy('id','DESC')->get();
                    $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
                    ->where('user_id', $user->id)
                    ->where('ride_setting_id', $request->ride_setting_id)
                    // ->where('booking_date','>=',$fdate)
                    ->where('booking_date','>=',date('Y-m-d'))
                    // ->whereTime('booking_start_time', '>=', $ftime)
                    ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
                    ->orderBy('id','DESC')->get();

                    $upcoming = $upcoming->merge($upcoming_shuttle);
                    usort($upcomingdata, $this->sortByDate('booking_date'));
                    array_multisort( array_column($upcomingdata, "trip_status","accepted"), SORT_ASC, $upcomingdata );
                    $past = Booking::with(['driver','ride_setting'])
                    ->whereHas('driver')
                    ->where('user_id', $user->id)
                    ->where('ride_setting_id', $request->ride_setting_id)
                    ->whereIn('trip_status',['completed','cancelled','emergency'])
                    ->orderBy('id','DESC')
                    ->get()->toArray();

                    $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
                    ->whereHas('driver')
                    ->where('user_id', $user->id)
                    ->where('ride_setting_id', $request->ride_setting_id)
                    ->whereIn('trip_status',['completed','rejected'])
                    ->orderBy('id','DESC')->get()->toArray();
                    $past = array_merge($past,$past_shuttle);
                    usort($past, $this->sortByDate('booking_date'));
                    $data['upcoming']                = $upcoming;
                    $data['past']                    = $past;
                    return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
                }
            } else {

                $getPending = Booking::with(['ride_setting'])
                ->where('user_id', $user->id)
                ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
                ->orderBy('id','DESC')->get();
                if( sizeof($getPending) and !empty($getPending)){
                    $upcomingdata = [];
                    foreach ($getPending as $key => $value){
                        if($value['trip_status'] == 'pending'){
                            if($value['booking_type'] == "schedule"){
                                $date = $value['booking_date'];
                                $time = $value['booking_end_time'];
                                $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date.$time);
                                // $from_time1 = $new_time->format('Y-m-d h:i');
                                $from_time1 =  $value['booking_date'].' '.$value['booking_end_time'];
                                    // $from_time1 = str_replace("PM","",$ab);
                                $now = Carbon::now()->format('Y-m-d h:i A');
                                if (\Carbon\Carbon::parse($now)->lte(\Carbon\Carbon::parse($from_time1))){
                                    $upcomingdata[] = $value;
                                    $value['booking_date'] = date("m-d-Y", strtotime($value['booking_date']));
                                    $value->driver = new \stdClass();
                                }
                            }else if($value['booking_type'] == "out_of_town"){
                                $date = $value['bend_date'];
                                $time = $value['booking_end_time'];
                                $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date.$time);
                                // $from_time1 = $new_time->format('Y-m-d h:i');
                                $from_time1 =  $value['bend_date'].' '.$value['booking_end_time'];
                                    // $from_time1 = str_replace("PM","",$ab);
                                $now = Carbon::now()->format('Y-m-d h:i A');
                                if (\Carbon\Carbon::parse($now)->lte(\Carbon\Carbon::parse($from_time1))){
                                    $upcomingdata[] = $value;
                                    $value['booking_date'] = date("m-d-Y", strtotime($value['booking_date']));
                                    $value->driver = new \stdClass();
                                }
                            }else{
                                $to_time =  strtotime("now");
                                $from_time = strtotime($value['created_at']);
                                $diff = round(abs($to_time - $from_time) / 60,10);
                                if($diff <= 10){
                                    $upcomingdata[] = $value;
                                    $value['booking_date'] = date("m-d-Y", strtotime($value['booking_date']));
                                    $value->driver = new \stdClass();
                                }
                            }
                        }else if($value['trip_status'] == 'accepted' || $value['trip_status'] == 'on_going' || $value['trip_status'] == 'driver_arrived' || $value['trip_status'] == 'pick_up'){
                            if(\Carbon\Carbon::parse($value['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() || \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_date'])->format('Y-m-d')) {
                                $upcomingdata[] = $value;
                                $value->driver = $value->driver;
                                $value['booking_date'] = date("m-d-Y", strtotime($value['booking_date']));
                            }
                        }
                    }
                }
                $past_ride = Booking::with(['driver','ride_setting'])->whereHas('driver')->where('user_id', $user->id)->whereIn('trip_status',['completed','cancelled','emergency'])->orderBy('id','DESC')->get()->toArray();
                $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])->whereHas('driver')->where('user_id', $user->id)->whereIn('trip_status',['completed','rejected'])->orderBy('id','DESC')->get()->toArray();
                $past = array_merge($past_ride,$past_shuttle);
                usort($past, $this->sortByDate('booking_date'));
                foreach ($past as $key => $value) {
                    $past[$key]['booking_date'] = date("m-d-Y", strtotime($value['booking_date']));
                }
                $data['upcoming'] = $upcomingdata;
                $data['past']     = $past;
                return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
            }
        } else {
            if(!empty($request->ride_setting_id)){
                $getPending = UserNotification::with(['booking_details' => function($query) use ($request){
                    $query->where('ride_setting_id',$request->ride_setting_id);},'shuttle_details'=> function($query) use ($request){
                        $query->where('ride_setting_id',$request->ride_setting_id);
                    },'user','driver'])->whereIn('notification_for', ['accepted'])->where('sent_to_user', $user->id)->orderBy('id','desc')->get()->toArray();
                if( sizeof($getPending) and !empty($getPending)){
                    $upcomingdata = [];
                    foreach ($getPending as $key => $value){
                        if($value['notification_for'] == 'pending'){
                            $to_time =  strtotime("now");
                            $from_time = strtotime($value['created_at']);
                            $diff = round(abs($to_time - $from_time) / 60,10);
                            if($diff <= 10){
                                $upcomingdata[] = $value;
                                $value['booking_date'] = date("m-d-Y", strtotime($value['booking_date']));
                            }
                        }else if($value['notification_for'] == 'accepted'){
                            if(\Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() || \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') and \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() || \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d')){
                                if( !empty($value['booking_details']) || !empty($value['shuttle_details']))
                                    $upcomingdata[] = $value;
                                if(!empty($value['shuttle_details'])){
                                    $value['shuttle_details']['booking_date'] = date("m-d-Y", strtotime($value['shuttle_details']['booking_date']));
                                }
                            }
                        }
                    }
                }
                usort($upcomingdata, $this->sortByDate('created_at'));
                array_multisort( array_column($upcomingdata, "notification_for","accepted"), SORT_DESC, $upcomingdata );
                $past = Booking::with(['user','driver','ride_setting'])->where('driver_id', $user->id)->where('ride_setting_id', $request->ride_setting_id)->whereIn('trip_status',['completed','cancelled','emergency'])->orderBy('id','DESC')->get()->toArray();
                $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])->where('driver_id', $user->id)->where('ride_setting_id', $request->ride_setting_id)->whereIn('trip_status',['completed','rejected'])->orderBy('id','DESC')->get()->toArray();
                $past = array_merge($past,$past_shuttle);
                usort($past, $this->sortByDate('booking_date'));
                foreach ($past as $key => $value) {
                    $past[$key]['booking_date'] = date("m-d-Y", strtotime($value['booking_date']));
                }
                $data['upcoming'] = $upcomingdata;
                $data['past']     = $past;
                return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
            }else{
                $outPending =  UserNotification::with(['out_town_data','booking_details','user','driver'])->whereIn('notification_for', ['pending','accepted','on_going','pick_up'])->where('sent_to_user', $user->id)->orderBy('id','desc')->groupBy('out_of_town')->get()->toArray();
                $outPendings =  UserNotification::with(['out_town_data','booking_details','user','driver'])->whereIn('notification_for', ['pending','accepted','on_going','pick_up'])->where('sent_to_user', $user->id)->orderBy('id','desc')->groupBy('out_of_town')->pluck('id','booking_id');
                if(sizeof($outPending) and !empty($outPending)){
                   $upcomingdata1 = [];
                   foreach ($outPending as $key => $value){
                    if($value['flag_city'] == 'out_of_town'){
                        if(!empty($value['out_of_town'])){
                            if($value['notification_for'] == 'accepted' || $value['notification_for'] == 'pending' || $value['notification_for'] == 'on_going' || $value['notification_for'] == 'pick_up'){
                                $date = $value['booking_details']['bend_date'];
                                $time = $value['booking_details']['booking_end_time'];
                                $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date.$time);
                                $from_time1 =  $value['booking_details']['bend_date'].' '.$value['booking_details']['booking_end_time'];
                                $now = Carbon::now()->format('Y-m-d h:i A');
                                if (\Carbon\Carbon::parse($now)->lte(\Carbon\Carbon::parse($from_time1))){
                                        // if(\Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() || \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d')){
                                  if(!empty($value['out_town_data'])){
                                   $value['out_town_data']['booking_date'] = date("m-d-Y", strtotime($value['out_town_data']['booking_date']));
                               }
                               if($value['flag_city'] == 'out_of_town' && $value['taxi_hailing'] == 'sharing'){
                                $booking_id = UserNotification::where('sent_to_user',$user->id)->where('notification_for','pending')->where('out_of_town',$value['out_of_town'])->where('taxi_hailing','sharing')->where('out_of_town','!=','')->pluck('booking_id');
                                $pendingBooking = UserNotification::where('sent_to_user',$user->id)->where('notification_for','pending')->where('out_of_town',$value['out_of_town'])->where('taxi_hailing','sharing')->where('out_of_town','!=','')->count();
                                $value['pendingBooking'] = (string)$pendingBooking;
                                $totalSeat = Booking::whereIn('id',$booking_id)->where('seats','!=','')->sum('seats');
                                $total_luggage = Booking::whereIn('id',$booking_id)->where('total_luggage','!=','')->sum('total_luggage');
                                $value['totalSeat'] = (string)$totalSeat;
                                $value['total_luggage'] = (string)$total_luggage;
                            }else{
                                $value['totalSeat'] = "";
                                $value['total_luggage'] ="";
                            }
                            $value['booking_details']['booking_date'] = date("m-d-Y", strtotime($value['booking_details']['booking_date']));
                            $upcomingdata1[] = $value; 
                        }
                    }
                }
            }
        }
    }else {
     $upcomingdata1 = [];
 }
 $getPending =  UserNotification::with(['booking_details','shuttle_details','user','driver'])->whereIn('notification_for', ['pending','accepted','on_going','pick_up'])->where('sent_to_user', $user->id)->whereNotIn('id',$outPendings)->orderBy('id','desc')->get()->toArray();
 if( sizeof($getPending) and !empty($getPending)){
    $upcomingdata = [];
    foreach ($getPending as $key => $value){
        if($value['taxi_hailing'] != 'sharing'){
            if($value['notification_for'] == 'pending'){
                if($value['booking_details']['booking_type'] == "schedule"){
                    $date = $value['booking_details']['booking_date'];
                    $time = $value['booking_details']['booking_end_time'];
                    $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date.$time);
                    $from_time1 =  $value['booking_details']['booking_date'].' '.$value['booking_details']['booking_end_time'];
                    $now = Carbon::now()->format('Y-m-d h:i A');
                    if (\Carbon\Carbon::parse($now)->lte(\Carbon\Carbon::parse($from_time1))){
                        $value['booking_details']['booking_date'] = date("m-d-Y", strtotime($value['booking_details']['booking_date']));
                        if(!empty($value['shuttle_details'])){
                            $value['shuttle_details']['booking_date'] = date("m-d-Y", strtotime($value['shuttle_details']['booking_date']));
                        }            
                        $upcomingdata[] = $value;
                    }
                }else if($value['booking_details']['booking_type'] == "out_of_town"){
                    $date = $value['booking_details']['bend_date'];
                    $time = $value['booking_details']['booking_end_time'];
                    $new_time = DateTime::createFromFormat('Y-m-d h:i A', $date.$time);
                    $from_time1 =  $value['booking_details']['bend_date'].' '.$value['booking_details']['booking_end_time'];
                    $now = Carbon::now()->format('Y-m-d h:i A');
                    if (\Carbon\Carbon::parse($now)->lte(\Carbon\Carbon::parse($from_time1))){
                        $value['booking_details']['booking_date'] = date("m-d-Y", strtotime($value['booking_details']['booking_date']));
                        if(!empty($value['shuttle_details'])){
                            $value['shuttle_details']['booking_date'] = date("m-d-Y", strtotime($value['shuttle_details']['booking_date']));
                        }           
                        $upcomingdata[] = $value;
                    } 
                }else{
                    $to_time =  strtotime("now");
                    $from_time = strtotime($value['created_at']);
                    $diff = round(abs($to_time - $from_time) / 60,10);
                    if($diff <= 10){
                        $value['booking_details']['booking_date'] = date("m-d-Y", strtotime($value['booking_details']['booking_date']));
                        if(!empty($value['shuttle_details'])){
                            $value['shuttle_details']['booking_date'] = date("m-d-Y", strtotime($value['shuttle_details']['booking_date']));
                        }
                        $upcomingdata[] = $value;
                    }
                }
            }else if($value['notification_for'] == 'accepted' || $value['notification_for'] == 'on_going' || $value['notification_for'] == 'pick_up'){
             if(\Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() || \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') and \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() || \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d')) {
                if(!empty($value['shuttle_details'])){
                    $value['shuttle_details']['booking_date'] = date("m-d-Y", strtotime($value['shuttle_details']['booking_date']));
                }
                $value['booking_details']['booking_date'] = date("m-d-Y", strtotime($value['booking_details']['booking_date']));
                $upcomingdata[] = $value;
            }
        }
    }
}
}
$upcomingdata2 = array_merge($upcomingdata,$upcomingdata1);
usort($upcomingdata2, $this->sortByDate('created_at'));
array_multisort( array_column($upcomingdata2, "notification_for","accepted"), SORT_DESC, $upcomingdata2 , SORT_DESC);
// dd($upcomingdata2);

$past = Booking::with(['user','driver','ride_setting'])->where('driver_id', $user->id)->whereIn('trip_status',['completed','cancelled','emergency'])->orderBy('id','DESC')->get()->toArray();
$past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])->where('driver_id', $user->id)->whereIn('trip_status',['completed','rejected'])->orderBy('id','DESC')->get()->toArray();
$past = array_merge($past,$past_shuttle);
foreach ($past as $key => $value) {
   $past[$key]['booking_date'] = date("m-d-Y", strtotime($value['booking_date']));
}
$data['upcoming'] = $upcomingdata2;
$data['past']     = $past;
return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
}
}
} catch (Exception $e) {
   return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
}
}
// public function getUserUpcomingRides(Request $request){
//     try {
//         $user = JWTAuth::parseToken()->authenticate();
//         if(!$user){
//             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
//         }
//         $date = new DateTime();
//         $date->setTimezone(new DateTimeZone('America/New_York'));
//         $fdate = $date->format('Y-m-d');

//         $date = new DateTime();
//         $date->setTimezone(new DateTimeZone('America/New_York'));
//         $ftime = $date->format('H:i:s');

//         $upcomingdata = $pastdata = array();
//         if($user->user_type == "user"){
//             if(!empty($request->ride_setting_id)){
//                 if($request->ride_setting_id){
//                     $upcoming = Booking::with(['driver','ride_setting'])
//                     ->where('user_id', $user->id)
//                     ->where('ride_setting_id', $request->ride_setting_id)
//                     // ->where('booking_date','>=',$fdate)
//                     ->where('booking_date','>=',date('Y-m-d'))
//                     // ->whereTime('booking_start_time', '>=', $ftime)
//                     ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
//                     ->orderBy('id','DESC')->get();

//                     $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                     ->where('user_id', $user->id)
//                     ->where('ride_setting_id', $request->ride_setting_id)
//                     // ->where('booking_date','>=',$fdate)
//                     ->where('booking_date','>=',date('Y-m-d'))
//                     // ->whereTime('booking_start_time', '>=', $ftime)
//                     ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
//                     ->orderBy('id','DESC')->get();

//                     $upcoming = $upcoming->merge($upcoming_shuttle);
//                     usort($upcomingdata, $this->sortByDate('booking_date'));
//                     array_multisort( array_column($upcomingdata, "trip_status","accepted"), SORT_ASC, $upcomingdata );
//                     $past = Booking::with(['driver','ride_setting'])
//                     ->whereHas('driver')
//                     ->where('user_id', $user->id)
//                     ->where('ride_setting_id', $request->ride_setting_id)
//                     ->whereIn('trip_status',['completed','cancelled'])
//                     ->orderBy('id','DESC')
//                     ->get()->toArray();

//                     $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                     ->whereHas('driver')
//                     ->where('user_id', $user->id)
//                     ->where('ride_setting_id', $request->ride_setting_id)
//                     ->whereIn('trip_status',['completed','rejected'])
//                     ->orderBy('id','DESC')->get()->toArray();
//                     $past = array_merge($past,$past_shuttle);
//                     usort($past, $this->sortByDate('booking_date'));
//                     $data['upcoming']                = $upcomingdata;
//                     $data['past']                    = $past;
//                     return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
//                 }
//             } else {
//                 // $upcoming = Booking::with(['driver','ride_setting'])
//                 // ->where('user_id', $user->id)
//                 // ->where('booking_date','>=',date('Y-m-d'))
//                 // // ->where('booking_date','>=',$fdate)
//                 // // ->whereTime('booking_start_time', '>=', $ftime)
//                 // ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
//                 // ->orderBy('id','DESC')->get();

//                 // $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 // ->where('user_id', $user->id)
//                 // ->where('booking_date','>=',date('Y-m-d'))
//                 // // ->where('booking_date','>=',$fdate)
//                 // // ->whereTime('booking_start_time', '>=', $ftime)
//                 // ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
//                 // ->orderBy('id','DESC')->get();
//                 // $upcomingdata = $upcoming->merge($upcoming_shuttle);

//                 $getPending = Booking::with(['driver','ride_setting'])
//                 ->where('user_id', $user->id)
//                 ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
//                 ->orderBy('id','DESC')->get();
//                 // dd($upcomingdata);
//                 if( sizeof($getPending) and !empty($getPending)){
//                     $upcomingdata = [];
//                     foreach ($getPending as $key => $value){
//                         // dd($value);
//                         // echo $value;
//                         if($value['trip_status'] == 'pending'){
//                             $to_time =  strtotime("now");
//                             $from_time = strtotime($value['created_at']);
//                             // dd($from_time);
//                             $diff = round(abs($to_time - $from_time) / 60,10);
//                             if($diff <= 10){
//                                 $upcomingdata[] = $value;
//                             }
//                         }else if($value['trip_status'] == 'accepted' || $value['trip_status'] == 'on_going' || $value['trip_status'] == 'driver_arrived'){
//                             if(
//                                 \Carbon\Carbon::parse($value['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
//                                 \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_date'])->format('Y-m-d')
//                             ) {
//                                 $upcomingdata[] = $value;
//                             }
//                         }
//                     }
//                 }

//                 $past = Booking::with(['driver','ride_setting'])
//                 ->whereHas('driver')
//                 ->where('user_id', $user->id)
//                 ->whereIn('trip_status',['completed','cancelled'])
//                 ->orderBy('id','DESC')
//                 ->get()->toArray();

//                 $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 ->whereHas('driver')
//                 ->where('user_id', $user->id)
//                 ->whereIn('trip_status',['completed','rejected'])
//                 ->orderBy('id','DESC')->get()->toArray();
//                 $past = array_merge($past,$past_shuttle);
//                 usort($past, $this->sortByDate('booking_date'));
//                 $data['upcoming']                = $upcomingdata;
//                 $data['past']                    = $past;
//                 return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
//             }
//         } else {
//             if(!empty($request->ride_setting_id)){
//                 $getPending = UserNotification::with([
//                     'booking_details' => function($query) use ($request){
//                         $query->where('ride_setting_id',$request->ride_setting_id);
//                     },'shuttle_details'=> function($query) use ($request){
//                         $query->where('ride_setting_id',$request->ride_setting_id);
//                     },'user','driver'])->whereIn('notification_for', ['accepted'])->where('sent_to_user', $user->id)
//                 ->orderBy('id','desc')->get()->toArray();
//                 if( sizeof($getPending) and !empty($getPending)){
//                     $upcomingdata = [];
//                     foreach ($getPending as $key => $value){
//                         if($value['notification_for'] == 'pending'){
//                             $to_time =  strtotime("now");
//                             $from_time = strtotime($value['created_at']);
//                             $diff = round(abs($to_time - $from_time) / 60,60);
//                             if($diff <= 60){
//                                 $upcomingdata[] = $value;
//                             }
//                         }else if($value['notification_for'] == 'accepted'){
//                             if(
//                                 \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
//                                 \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') and
//                                 \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
//                                 \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d')
//                             )
//                                 {
//                                     if( !empty($value['booking_details']) || !empty($value['shuttle_details']))
//                                         $upcomingdata[] = $value;
//                                 }
//                             }
//                         }
//                     }
//                     usort($upcomingdata, $this->sortByDate('created_at'));
//                     array_multisort( array_column($upcomingdata, "notification_for","accepted"), SORT_ASC, $upcomingdata );
//                     $past = Booking::with(['user','driver','ride_setting'])
//                     ->where('driver_id', $user->id)
//                     ->where('ride_setting_id', $request->ride_setting_id)
//                     ->whereIn('trip_status',['completed','cancelled'])
//                     ->orderBy('id','DESC')
//                     ->get()->toArray();
//                     $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                     ->where('driver_id', $user->id)
//                     ->where('ride_setting_id', $request->ride_setting_id)
//                     ->whereIn('trip_status',['completed','rejected'])
//                     ->orderBy('id','DESC')->get()->toArray();
//                     $past = array_merge($past,$past_shuttle);
//                     usort($past, $this->sortByDate('booking_date'));
//                     $data['upcoming']                = $upcomingdata;
//                     $data['past']                    = $past;
//                     return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
//                 }else{
//                     $getPending =  UserNotification::with(['booking_details','shuttle_details','user','driver'])
//                     ->whereIn('notification_for', ['pending','accepted'])
//                     ->where('sent_to_user', $user->id)->orderBy('id','desc')->get()->toArray();
//                     if( sizeof($getPending) and !empty($getPending)){
//                         $upcomingdata = [];
//                         foreach ($getPending as $key => $value){

//                             if($value['notification_for'] == 'pending'){
//                                 $to_time =  strtotime("now");
//                                 $from_time = strtotime($value['created_at']);
//                                 $diff = round(abs($to_time - $from_time) / 60,10);
//                                 if($diff <= 10)
//                                 {
//                                     $upcomingdata[] = $value;
//                                 }
//                                 // $to_time =  strtotime("now");
//                                 // $from_time = strtotime($value['created_at']);
//                                 // $diff = round(abs($to_time - $from_time) / 60,60);
//                                 // if($diff <= 60){
//                                 //     $upcomingdata[] = $value;
//                                 // } 
//                             }else if($value['notification_for'] == 'accepted'){
//                                 if(
//                                     \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
//                                     \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') and
//                                     \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
//                                     \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d')
//                                 )
//                                     {
//                                         $upcomingdata[] = $value;
//                                     }
//                                 }
//                             }
//                         }
//                         usort($upcomingdata, $this->sortByDate('created_at'));
//                         array_multisort( array_column($upcomingdata, "notification_for","accepted"), SORT_ASC, $upcomingdata );
//                         $past = Booking::with(['user','driver','ride_setting'])
//                         ->where('driver_id', $user->id)
//                         ->whereIn('trip_status',['completed','cancelled'])
//                         ->orderBy('id','DESC')
//                         ->get()->toArray();

//                         $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                         ->where('driver_id', $user->id)
//                         ->whereIn('trip_status',['completed','rejected'])
//                         ->orderBy('id','DESC')->get()->toArray();
//                         $past = array_merge($past,$past_shuttle);
//                         $data['upcoming']                = $upcomingdata;
//                         $data['past']                    = $past;
//                         return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
//                     }
//                 }
//             } catch (Exception $e) {
//                 return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
//             }
//         }



public function jkgetUserUpcomingRides(Request $request){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $fdate = $date->format('Y-m-d');

        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('America/New_York'));
        $ftime = $date->format('H:i:s');

        $upcomingdata = $pastdata = array();
        if($user->user_type == "user"){
            if(!empty($request->ride_setting_id)){
                if($request->ride_setting_id){
                    $upcoming = Booking::with(['driver','ride_setting'])
                    ->where('user_id', $user->id)
                    ->where('ride_setting_id', $request->ride_setting_id)
                    ->where('booking_date','>=',$fdate)
                    ->whereTime('booking_start_time', '>=', $ftime)
                    ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
                    ->orderBy('id','DESC')->get();

                    $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
                    ->where('user_id', $user->id)
                    ->where('ride_setting_id', $request->ride_setting_id)
                    ->where('booking_date','>=',$fdate)
                    ->whereTime('booking_start_time', '>=', $ftime)
                    ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
                    ->orderBy('id','DESC')->get();

                    $upcoming = $upcoming->merge($upcoming_shuttle);
                    usort($upcomingdata, $this->sortByDate('booking_date'));
                    array_multisort( array_column($upcomingdata, "trip_status","accepted"), SORT_ASC, $upcomingdata );
                    $past = Booking::with(['driver','ride_setting'])
                    ->whereHas('driver')
                    ->where('user_id', $user->id)
                    ->where('ride_setting_id', $request->ride_setting_id)
                    ->whereIn('trip_status',['completed','cancelled'])
                    ->orderBy('id','DESC')
                    ->get()->toArray();

                    $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
                    ->whereHas('driver')
                    ->where('user_id', $user->id)
                    ->where('ride_setting_id', $request->ride_setting_id)
                    ->whereIn('trip_status',['completed','rejected'])
                    ->orderBy('id','DESC')->get()->toArray();
                    $past = array_merge($past,$past_shuttle);
                    usort($past, $this->sortByDate('booking_date'));
                    $data['upcoming']                = $upcomingdata;
                    $data['past']                    = $past;
                    return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
                }
            } else {
                $upcoming = Booking::with(['driver','ride_setting'])
                ->where('user_id', $user->id)
                ->where('booking_date','>=',$fdate)
                // ->whereTime('booking_start_time', '>=', $ftime)
                ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
                ->orderBy('id','DESC')->get();

                $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
                ->where('user_id', $user->id)
                ->where('booking_date','>=',$fdate)
                // ->whereTime('booking_start_time', '>=', $ftime)
                ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
                ->orderBy('id','DESC')->get();
                $upcomingdata = $upcoming->merge($upcoming_shuttle);
                $past = Booking::with(['driver','ride_setting'])
                ->whereHas('driver')
                ->where('user_id', $user->id)
                ->whereIn('trip_status',['completed','cancelled'])
                ->orderBy('id','DESC')
                ->get()->toArray();

                $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
                ->whereHas('driver')
                ->where('user_id', $user->id)
                ->whereIn('trip_status',['completed','rejected'])
                ->orderBy('id','DESC')->get()->toArray();
                $past = array_merge($past,$past_shuttle);
                usort($past, $this->sortByDate('booking_date'));
                $data['upcoming']                = $upcomingdata;
                $data['past']                    = $past;
                return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
            }
        } else {
            if(!empty($request->ride_setting_id)){
                $getPending = UserNotification::with([
                    'booking_details' => function($query) use ($request){
                        $query->where('ride_setting_id',$request->ride_setting_id);
                    },'shuttle_details'=> function($query) use ($request){
                        $query->where('ride_setting_id',$request->ride_setting_id);
                    },'user','driver'])->whereIn('notification_for', ['accepted'])->where('sent_to_user', $user->id)
                ->orderBy('id','desc')->get()->toArray();
                if( sizeof($getPending) and !empty($getPending)){
                    $upcomingdata = [];
                    foreach ($getPending as $key => $value){
                        if($value['notification_for'] == 'pending'){
                            $to_time =  strtotime("now");
                            $from_time = strtotime($value['created_at']);
                            $diff = round(abs($to_time - $from_time) / 60,60);
                            if($diff <= 60){
                                $upcomingdata[] = $value;
                            }
                        }else if($value['notification_for'] == 'accepted'){
                            if(
                                \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
                                \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') and
                                \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
                                \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d')
                                )
                            {
                                if( !empty($value['booking_details']) || !empty($value['shuttle_details']))
                                    $upcomingdata[] = $value;
                            }
                        }
                    }
                }
                usort($upcomingdata, $this->sortByDate('created_at'));
                array_multisort( array_column($upcomingdata, "notification_for","accepted"), SORT_ASC, $upcomingdata );
                $past = Booking::with(['user','driver','ride_setting'])
                ->where('driver_id', $user->id)
                ->where('ride_setting_id', $request->ride_setting_id)
                ->whereIn('trip_status',['completed','cancelled'])
                ->orderBy('id','DESC')
                ->get()->toArray();
                $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
                ->where('driver_id', $user->id)
                ->where('ride_setting_id', $request->ride_setting_id)
                ->whereIn('trip_status',['completed','rejected'])
                ->orderBy('id','DESC')->get()->toArray();
                $past = array_merge($past,$past_shuttle);
                usort($past, $this->sortByDate('booking_date'));
                $data['upcoming']                = $upcomingdata;
                $data['past']                    = $past;
                return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
            }else{
                $getPending =  UserNotification::with(['booking_details','shuttle_details','user','driver'])
                ->whereIn('notification_for', ['pending','accepted'])
                ->where('sent_to_user', $user->id)->orderBy('id','desc')->get()->toArray();
                if( sizeof($getPending) and !empty($getPending)){
                    $upcomingdata = [];
                    foreach ($getPending as $key => $value){

                        if($value['notification_for'] == 'pending'){
                            $to_time =  strtotime("now");
                            $from_time = strtotime($value['created_at']);
                            $diff = round(abs($to_time - $from_time) / 60,10);
                            if($diff <= 10)
                            {
                                $upcomingdata[] = $value;
                            }
                                // $to_time =  strtotime("now");
                                // $from_time = strtotime($value['created_at']);
                                // $diff = round(abs($to_time - $from_time) / 60,60);
                                // if($diff <= 60){
                                //     $upcomingdata[] = $value;
                                // } 
                        }else if($value['notification_for'] == 'accepted'){
                            if(
                                \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
                                \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['booking_details']['booking_date'])->format('Y-m-d') and
                                \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d') == \Carbon\Carbon::now()->toDateString() ||
                                \Carbon\Carbon::now()->toDateString() <= \Carbon\Carbon::parse($value['shuttle_details']['booking_date'])->format('Y-m-d')
                                )
                            {
                                $upcomingdata[] = $value;
                            }
                        }
                    }
                }
                usort($upcomingdata, $this->sortByDate('created_at'));
                array_multisort( array_column($upcomingdata, "notification_for","accepted"), SORT_ASC, $upcomingdata );
                $past = Booking::with(['user','driver','ride_setting'])
                ->where('driver_id', $user->id)
                ->whereIn('trip_status',['completed','cancelled'])
                ->orderBy('id','DESC')
                ->get()->toArray();

                $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
                ->where('driver_id', $user->id)
                ->whereIn('trip_status',['completed','rejected'])
                ->orderBy('id','DESC')->get()->toArray();
                $past = array_merge($past,$past_shuttle);
                $data['upcoming']                = $upcomingdata;
                $data['past']                    = $past;
                return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
            }
        }
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}
// public function getUserUpcomingRides(){
//     try {
//         $user = JWTAuth::parseToken()->authenticate();
//         if(!$user){
//             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
//         }
//         $upcomingdata = $pastdata = array();
//         if($user->user_type == "user"){
//             $upcoming = Booking::with(['driver','ride_setting'])
//             //->whereHas('driver')
//             ->where('user_id', $user->id)
//             ->where('booking_date','>=',date('Y-m-d'))
//             ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
//             ->orderBy('id','DESC')->get();

//             $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 //->whereHas('driver')
//                 ->where('user_id', $user->id)
//                 ->where('booking_date','>=',date('Y-m-d'))
//                 ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
//                 ->orderBy('id','DESC')->get();

//             $upcoming = $upcoming->merge($upcoming_shuttle);


//             if(sizeof($upcoming) > 0){
//                 foreach($upcoming as $key => $ride){
//                     $bookingtime = explode(' ', $ride->booking_start_time);
//                     $date =   \Carbon\Carbon::parse($ride->booking_date.' '.$bookingtime[0]);
//                     $dt = \Carbon\Carbon::now();
//                     $totalDuration = $date->diffForHumans($dt);
//                     if(!empty($ride->total_amount))
//                         $ride->upcoming_total_fare = $ride->total_amount;
//                     else
//                         $ride->upcoming_total_fare = 0;


//                     $getPending =  UserNotification::where('notification_for','pending')
//                         ->where('booking_id', $ride->id)
//                         ->orWhere('shuttle_id', $ride->id)
//                         ->first();
//                     if(!empty($getPending)){
//                         $ride = $getPending;
//                     }


//                     if($ride->trip_status == 'accepted' || $ride->trip_status == 'driver_arrived' || $ride->trip_status == 'on_going' || $ride->trip_status == 'pick_up' || $ride->notification_for == 'pending'){
//                         array_push($upcomingdata, $ride);
//                     }elseif($date->isFuture()){
//                         array_push($upcomingdata, $ride);
//                     }
//                 }
//             }
//             usort($upcomingdata, $this->sortByDate('booking_date'));

//             $past = Booking::with(['driver','ride_setting'])
//             ->whereHas('driver')
//             ->where('user_id', $user->id)
//                     // ->where('booking_date','<=',date('Y-m-d'))
//             ->whereIn('trip_status',['completed','cancelled'])
//             ->orderBy('id','DESC')
//             ->get()->toArray();

//             $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 ->whereHas('driver')
//                 ->where('user_id', $user->id)
//                 ->whereIn('trip_status',['completed','rejected'])
//                 ->orderBy('id','DESC')->get()->toArray();

//             $past = array_merge($past,$past_shuttle);
//             usort($past, $this->sortByDate('booking_date'));

//             $data['upcoming']                = $upcomingdata;
//             $data['past']                    = $past;
//             return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
//         } else {
//             $upcoming = Booking::with(['user','driver','ride_setting'])
//             ->whereHas('driver')
//             ->where('driver_id', $user->id)
//             ->where('booking_date','>=',date('Y-m-d'))
//             ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
//             ->orderBy('id','DESC')
//             ->get();

//             $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 ->whereHas('driver')
//                 ->where('driver_id', $user->id)
//                 ->where('booking_date','>=',date('Y-m-d'))
//                 ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
//                 ->orderBy('id','DESC')->get();

//             $upcoming = $upcoming->merge($upcoming_shuttle);

//             if(sizeof($upcoming) > 0){
//                 foreach($upcoming as $key => $ride){
//                     $bookingtime = explode(' ', $ride->booking_start_time);
//                     $date =   \Carbon\Carbon::parse($ride->booking_date.' '.$bookingtime[0]);
//                     $dt = \Carbon\Carbon::now();
//                     $totalDuration = $date->diffForHumans($dt);

//                     if(!empty($ride->total_amount))
//                         $ride->upcoming_total_fare = $ride->total_amount;
//                     else
//                         $ride->upcoming_total_fare = 0;

//                     $getPending =  UserNotification::with(['booking_details','shuttle_details','user','driver'])->where('notification_for','pending')
//                         ->where('booking_id', $ride->id)
//                         ->orWhere('shuttle_id', $ride->id)
//                         ->first();
//                     if(!empty($getPending)){
//                         $ride = $getPending;
//                     }

//                     if($ride->trip_status == 'accepted' || $ride->trip_status == 'driver_arrived' || $ride->trip_status == 'on_going' || $ride->trip_status == 'pick_up' || $ride->notification_for == 'pending'){
//                         array_push($upcomingdata, $ride);
//                     }elseif($date->isFuture()){
//                         array_push($upcomingdata, $ride);
//                     }
//                 }
//             }
//             $past = Booking::with(['user','driver','ride_setting'])
//             ->whereHas('driver')
//             ->where('driver_id', $user->id)
//                     // ->where('booking_date','<=',date('Y-m-d'))
//             ->whereIn('trip_status',['completed','cancelled'])
//             ->orderBy('id','DESC')
//             ->get()->toArray();

//             $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 ->whereHas('driver')
//                 ->where('driver_id', $user->id)
//                 ->whereIn('trip_status',['completed','rejected'])
//                 ->orderBy('id','DESC')->get()->toArray();

//             $past = array_merge($past,$past_shuttle);
//             usort($past, $this->sortByDate('booking_date'));

//             $data['upcoming']                = $upcomingdata;
//             $data['past']                    = $past;
//             return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
//         }
//     } catch (Exception $e) {
//         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
//     }
// }
// public function getUserUpcomingRides(){
//     try {
//         $user = JWTAuth::parseToken()->authenticate();
//         if(!$user){
//             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
//         }
//         $upcomingdata =$pastdata= array();
//         if($user->user_type == "user"){
//             $upcoming = Booking::with(['driver','ride_setting'])
//             ->whereHas('driver')
//             ->where('user_id', $user->id)
//             ->where('booking_date','>=',date('Y-m-d'))
//             ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
//             ->orderBy('id','DESC')->get();

//             $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 ->whereHas('driver')
//                 ->where('user_id', $user->id)
//                 ->where('booking_date','>=',date('Y-m-d'))
//                 ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
//                 ->orderBy('id','DESC')->get();

//             $upcoming = $upcoming->merge($upcoming_shuttle);


//             if(sizeof($upcoming) > 0){
//                 foreach($upcoming as $key => $ride){
//                     $bookingtime = explode(' ', $ride->booking_start_time);
//                     $date =   \Carbon\Carbon::parse($ride->booking_date.' '.$bookingtime[0]);
//                     $dt = \Carbon\Carbon::now();
//                     $totalDuration = $date->diffForHumans($dt);
//                     if(!empty($ride->total_amount))
//                         $ride->upcoming_total_fare = $ride->total_amount;
//                     else
//                         $ride->upcoming_total_fare = 0;

//                     if($ride->trip_status == 'accepted' || $ride->trip_status == 'driver_arrived' || $ride->trip_status == 'on_going' || $ride->trip_status == 'pick_up' || $ride->trip_status == 'pending'){
//                         array_push($upcomingdata, $ride);
//                     }elseif($date->isFuture()){
//                         array_push($upcomingdata, $ride);
//                     }
//                 }
//             }
//             usort($upcomingdata, $this->sortByDate('booking_date'));

//             $past = Booking::with(['driver','ride_setting'])
//             ->whereHas('driver')
//             ->where('user_id', $user->id)
//                     // ->where('booking_date','<=',date('Y-m-d'))
//             ->whereIn('trip_status',['completed','cancelled'])
//             ->orderBy('id','DESC')
//             ->get()->toArray();

//             $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 ->whereHas('driver')
//                 ->where('user_id', $user->id)
//                 ->whereIn('trip_status',['completed','rejected'])
//                 ->orderBy('id','DESC')->get()->toArray();



//             $past = array_merge($past,$past_shuttle);
//             usort($past, $this->sortByDate('booking_date'));

//             $data['upcoming']                = $upcomingdata;
//             $data['past']                    = $past;
//             return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
//         } else {
//             $upcoming = Booking::with(['user','driver','ride_setting'])
//             ->whereHas('driver')
//             ->where('driver_id', $user->id)
//             ->where('booking_date','>=',date('Y-m-d'))
//             ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived'])
//             ->orderBy('id','DESC')
//             ->get();

//             $upcoming_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 ->whereHas('driver')
//                 ->where('driver_id', $user->id)
//                 ->where('booking_date','>=',date('Y-m-d'))
//                 ->whereIn('trip_status', ['pending', 'accepted','on_going', 'driver_arrived','pick_up'])
//                 ->orderBy('id','DESC')->get();
//                 // dd($upcoming_shuttle);

//             $upcoming = $upcoming->merge($upcoming_shuttle);

//             if(sizeof($upcoming) > 0){
//                 foreach($upcoming as $key => $ride){
//                     $bookingtime = explode(' ', $ride->booking_start_time);
//                     $date =   \Carbon\Carbon::parse($ride->booking_date.' '.$bookingtime[0]);
//                     $dt = \Carbon\Carbon::now();
//                     $totalDuration = $date->diffForHumans($dt);

//                     if(!empty($ride->total_amount))
//                         $ride->upcoming_total_fare = $ride->total_amount;
//                     else
//                         $ride->upcoming_total_fare = 0;

//                     if($ride->trip_status == 'accepted' || $ride->trip_status == 'driver_arrived' || $ride->trip_status == 'on_going' || $ride->trip_status == 'pick_up' || $ride->trip_status == 'pending'){
//                         array_push($upcomingdata, $ride);
//                     }elseif($date->isFuture()){
//                         array_push($upcomingdata, $ride);
//                     }
//                 }
//             }
//             $past = Booking::with(['user','driver','ride_setting'])
//             ->whereHas('driver')
//             ->where('driver_id', $user->id)
//                     // ->where('booking_date','<=',date('Y-m-d'))
//             ->whereIn('trip_status',['completed','cancelled'])
//             ->orderBy('id','DESC')
//             ->get()->toArray();

//             $past_shuttle = LinerideUserBooking::with(['driver','ride_setting','user'])
//                 ->whereHas('driver')
//                 ->where('driver_id', $user->id)
//                 ->whereIn('trip_status',['completed','rejected'])
//                 ->orderBy('id','DESC')->get()->toArray();

//             $past = array_merge($past,$past_shuttle);
//             usort($past, $this->sortByDate('booking_date'));

//             $data['upcoming']                = $upcomingdata;
//             $data['past']                    = $past;
//             return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$data, 'user_data'=>User::find($user->id)]);
//         }
//     } catch (Exception $e) {
//         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
//     }
// }

function sortByDate($key)
{
    return function ($a, $b) use ($key) {
        $t1 = strtotime($a[$key]);
        $t2 = strtotime($b[$key]);
        return $t2-$t1;
    };

}

/*
|--------------------------------------------------------------------------
| Get Driver Upcoming Rides
|--------------------------------------------------------------------------
|
*/
public function getDriverUpcomingRides(){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $allrides = Booking::where('booking_type','schedule')->where('trip_status','!=','completed')->where('trip_status','!=','cancelled')->where('driver_id', $user->id)->get();
        foreach ($allrides as $key => $value) {
            $value->booking_date = date("m-d-Y", strtotime($value->booking_date));
        }
        return response()->json(['status' => 'success','message' => 'Get Upcoming Trip Details', 'data'=>$allrides, 'user_data'=>User::find($user->id)]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

    /*
    |--------------------------------------------------------------------------
    |  Get Driver Daily Monthly Report
    |--------------------------------------------------------------------------
    |
    */
    public function getDriverDailyReport(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $house_edge = Setting::where('code','house_edge')->first();
            $jk = 100 - $house_edge->value;
            $allrides = Booking::where('booking_date','=',date('Y-m-d'))->where('trip_status','completed')->where('driver_id', $user->id)->get();
            $total_per_day=0;
            if(sizeof($allrides) > 0){
                $total_per_day1 = array_sum(array_pluck($allrides, 'total_amount'));
                $total_per_day2 = $total_per_day1 * $jk /100;
                $total_per_day3 = array_sum(array_pluck($allrides, 'tip_amount'));
                $total_per_day  = $total_per_day2 + $total_per_day3;
            }
            $daily_data['earnings_total_amount'] = number_format((float)$total_per_day, 1, '.', '');
            $daily_data['today_total_trips'] = $allrides->count();
            // $allrides = Booking::where('booking_date','=',date('Y-m-d'))->where('trip_status','completed')->where('driver_id', $user->id)->get();
            // $total_per_day=0;
            // if(sizeof($allrides) > 0){
            //     $total_per_day = array_sum(array_pluck($allrides, 'total_amount'));
            // }
            // $daily_data['earnings_total_amount'] = $total_per_day;
            // $daily_data['today_total_trips'] = $allrides->count();
            return response()->json(['status' => 'success','message' => 'Get Driver Report', 'daily_data'=> $daily_data]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    |  Get Driver All Monthly Report
    |--------------------------------------------------------------------------
    |
    */
    public function getDriverAllDailyReport(Request $request){
        $validator = Validator::make($request->all(), [
            'type'   => 'required',
            ]);
        if($validator->fails()) {
            return response()->json(['status'   => 'error','message'  => $validator->messages()->first()]);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }

            $total_per_day=$total_per_month=$distance_day=$distance_month=0;
            $rating=RatingReviews::where('to_user_id', $user->id)->where('status','approved')->get();
            // Daily Driver Report
            if($request->type == 'daily'){
                $today = Carbon::now()->format('Y-m-d');
                $allrides = Booking::with(['ride_setting','user'])->whereDate('booking_date',$today)->where('trip_status','completed')->where('driver_id', $user->id)->get();
                $parcel_ride = ParcelDetail::with(['ride_setting','user'])->whereDate('booking_date',$today)->where('parcel_status','completed')->where('driver_id', $user->id)->get();
                $shuttle_ride = LinerideUserBooking::with(['ride_setting','user'])->whereDate('booking_date',$today)->where('trip_status','completed')->where('driver_id', $user->id)->get();

                $allrides = $allrides->merge($parcel_ride);
                $allrides = $allrides->merge($shuttle_ride);

                $amount=$total_km_day=$total_km_month=array();
                if(sizeof($allrides) > 0){
                    $total_per_day = array_sum(array_pluck($allrides, 'total_amount'));
                    $distance_day = array_sum(array_pluck($allrides, 'total_km')) + array_sum(array_pluck($allrides, 'total_distance'));
                }
                $daily_data['total_earning'] = $total_per_day;
                $daily_data['trip_per_day'] = $allrides->count();
                $daily_data['total_distance_daily'] =  number_format((float)$distance_day, 1, '.', '');
                $daily_data['daily_trips'] = $allrides;
                $daily_data['avg_star_rating'] = number_format((float)$rating->avg('rating'), 2, '.', '');
                return response()->json(['status' => 'success','message' => 'Get Daily Report', 'data'=> $daily_data]);
            }
            // Weekly Driver Report
            elseif($request->type == 'weekly'){
                $today = Carbon::now()->format('Y-m-d');
                $old_date = Carbon::now()->subDays(365)->format('Y-m-d');
                $allrides = Booking::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])
                ->where('trip_status','completed')
                ->where('driver_id', $user->id)->get();

                $parcel_ride = ParcelDetail::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])->where('parcel_status','completed')->where('driver_id', $user->id)->get();
                $shuttle_ride = LinerideUserBooking::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])->where('trip_status','completed')->where('driver_id', $user->id)->get();

                $allrides = $allrides->merge($parcel_ride);
                $allrides = $allrides->merge($shuttle_ride);

                $amount = $total_km_day=$total_km_month = array();
                if(sizeof($allrides) > 0){
                    $total_per_day = array_sum(array_pluck($allrides, 'total_amount'));
                    $distance_day = array_sum(array_pluck($allrides, 'total_km')) + array_sum(array_pluck($allrides, 'total_distance'));
                }
                $daily_data['total_earning'] = $total_per_day;
                $daily_data['trip_per_day'] = $allrides->count();
                $daily_data['total_distance_daily'] =  number_format((float)$distance_day, 1, '.', '');
                $daily_data['daily_trips'] = $allrides;
                $daily_data['avg_star_rating'] = number_format((float)$rating->avg('rating'), 2, '.', '');
                return response()->json(['status' => 'success','message' => 'Get weekly Report', 'data'=> $daily_data]);
            }
            // Monthly Driver Report
            elseif($request->type == 'monthly'){
                $today = Carbon::now()->format('Y-m-d');
                $old_date = Carbon::now()->subDays(30)->format('Y-m-d');
                $allrides = Booking::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])
                ->where('trip_status','completed')
                ->where('driver_id', $user->id)->get();

                $parcel_ride = ParcelDetail::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])->where('parcel_status','completed')->where('driver_id', $user->id)->get();
                $shuttle_ride = LinerideUserBooking::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])->where('trip_status','completed')->where('driver_id', $user->id)->get();

                $allrides = $allrides->merge($parcel_ride);
                $allrides = $allrides->merge($shuttle_ride);

                $amount = $total_km_day=$total_km_month = array();
                if(sizeof($allrides) > 0){
                    $total_per_day = array_sum(array_pluck($allrides, 'total_amount'));
                    $distance_day = array_sum(array_pluck($allrides, 'total_km')) + array_sum(array_pluck($allrides, 'total_distance'));
                }
                $daily_data['total_earning'] = $total_per_day;
                $daily_data['trip_per_day'] = $allrides->count();
                $daily_data['total_distance_daily'] =  number_format((float)$distance_day, 1, '.', '');
                $daily_data['daily_trips'] = $allrides;
                $daily_data['avg_star_rating'] = number_format((float)$rating->avg('rating'), 2, '.', '');
                return response()->json(['status' => 'success','message' => 'Get Monthly Report', 'data'=> $daily_data]);
            }
            // Year Driver Report
            elseif($request->type == 'yearly'){
                $today = Carbon::now()->format('Y-m-d');
                $old_date = Carbon::now()->subDays(365)->format('Y-m-d');
                $allrides = Booking::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])
                ->where('trip_status','completed')
                ->where('driver_id', $user->id)->get();

                $parcel_ride = ParcelDetail::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])->where('parcel_status','completed')->where('driver_id', $user->id)->get();
                $shuttle_ride = LinerideUserBooking::with(['ride_setting','user'])->whereBetween('booking_date',[$old_date, $today])->where('trip_status','completed')->where('driver_id', $user->id)->get();

                $allrides = $allrides->merge($parcel_ride);
                $allrides = $allrides->merge($shuttle_ride);

                $amount = $total_km_day=$total_km_month = array();
                if(sizeof($allrides) > 0){
                    $total_per_day = array_sum(array_pluck($allrides, 'total_amount'));
                    $distance_day = array_sum(array_pluck($allrides, 'total_km')) + array_sum(array_pluck($allrides, 'total_distance'));
                }
                $daily_data['total_earning'] = $total_per_day;
                $daily_data['trip_per_day'] = $allrides->count();
                $daily_data['total_distance_daily'] =  number_format((float)$distance_day, 1, '.', '');
                $daily_data['daily_trips'] = $allrides;
                $daily_data['avg_star_rating'] = number_format((float)$rating->avg('rating'), 2, '.', '');
                return response()->json(['status' => 'success','message' => 'Get Yearly Report', 'data'=> $daily_data]);
            }else{
                return response()->json(['status' => 'success','message' => 'Record Not Found', 'data'=> true]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Vehicle List
    |--------------------------------------------------------------------------
    |
    */
    public function getVehicleList(Request $request){
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $distance_setting = Setting::where('code','search_driver_area')->first();
            $service = $request->service_id;
            if($request->start_latitude){
                $start_latitude         = $request->start_latitude;
            }else{
                $start_latitude         = $user->latitude;
            }
            if($request->start_longitude){
                $start_longitude         = $request->start_longitude;
            }else{
                $start_longitude         = $user->longitude;
            }


            if($request->get('lineride_distance') !== null){
                $distance = $request->get('lineride_distance');
            }else if($distance_setting !== null){
                $distance = $distance_setting->value;
            } else {
                $distance = 1;
            }
            $distance_In_value_setting = Setting::where('code','distance_in')->first();
            $distance_In_setting = $distance_In_value_setting->value;


            if($service == '2'){
                $drop_latitude         = $request->drop_latitude;
                $drop_longitude         = $request->drop_longitude;
                $dateing = $request->date;
                $pick_up_location_user = $request->pick_up_location_user;
                $drop_location_user = $request->drop_location_user;
                $booking_seat = $request->booking_seat;
                $booking_start_time = $request->booking_start_time;
                $booking_end_time = $request->booking_end_time;
                $user_start_date = $request->start_date;
                $user_end_date = $request->end_date;
                $taxi_hailing = "sharing";
                $resData = $this->getouttowndriverkmresult($start_latitude, $start_longitude, $drop_latitude, $drop_longitude, $dateing,$service, $pick_up_location_user, $drop_location_user, $booking_seat,$booking_start_time,$booking_end_time,$taxi_hailing,$user_start_date,$user_end_date);
            }else{
                $resData = $this->getdriverkmmlresult($start_latitude, $start_longitude, $distance, $service,$distance_In_setting);
            }
            if(sizeof($resData) > 0){
                $resDatass = array_column($resData, 'vehicle_id');
                $resData_unique = array_unique($resDatass);
                $arrayData = array();
                foreach ($resData_unique as $res) {
                    $arrayDatas = Vehicle::where('id',$res)->first();
                    if(!empty($arrayDatas)){
                        $arrayData[] = $arrayDatas;
                    }
                }
                return response()->json(['status' => 'success','message' => 'Vehicle List found','data' => $arrayData]);
            }else {
                return response()->json(['status' => 'error','message' => 'Vehicle List Not found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }
  /*  public function getouttowndriverkmresult($start_latitude, $start_longitude, $drop_latitude, $drop_longitude, $dateing, $service, $pick_up_location_user,$drop_location_user, $booking_seat, $booking_start_time, $booking_end_time,$taxi_hailing){
        $resultData = User::whereHas('driver_details_settings', function($q) use($service){
            $q->where('ride_type','=', $service);
        })->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved'])->get();
        $driverDetails=[];
        $driverDetailsData1 = [];
        foreach ($resultData as $key => $value) {
            $getdriversidecheckDataAll = OutTwonrideBooking::where('driver_id', $value->id)->where('booking_date','=',$dateing)->get();
            if($getdriversidecheckDataAll->count() > 0){
                foreach ($getdriversidecheckDataAll as $getdriversidecheckData){
                    if(isset($booking_seat)  && ((int)$getdriversidecheckData->seat_available >= (int)$booking_seat)){
                        $start_time = explode(' ', $booking_start_time);
                        $end_time = explode(' ', $booking_end_time);
                        $check_start_time = explode(' ', $getdriversidecheckData->booking_start_time);
                        $check_end_time = explode(' ', $getdriversidecheckData->booking_end_time);

                        $start_date = Carbon::createFromTimeString( $dateing.' '.$start_time[0]);
                        $end_date = Carbon::createFromTimeString( $dateing.' '.$end_time[0]);
                        $begin = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_start_time[0]);
                        $end   = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_end_time[0]);
                        if (($start_date >= $begin) && ($end <= $end_date)){
                    // if($start_date->between($begin, $end) || $end_date->between($begin, $end)){
                            $driverDetails['vehicle_id'] = $value->vehicle_id;
                        // $driverDetails['driver_id'] = $value->id;
                            if(!empty($getdriversidecheckData->start_latitude))
                            {
                                $driver_latitude = $getdriversidecheckData->start_latitude;
                            }
                            if(!empty($getdriversidecheckData->start_longitude)){
                                $driver_longitude = $getdriversidecheckData->start_longitude;
                            }

                            $origin_addresses = $pick_up_location_user;
                            $destination_addresses = $getdriversidecheckData->pick_up_location;
                            $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                            $distance_arr = json_decode($distance_data);
                            $elements = $distance_arr->rows[0]->elements;
                            if(!empty($elements[0]->distance)){
                                $distance = $elements[0]->distance->text;
                                $distance = explode(' ', $distance);
                                $jk =$distance[0];
                                $distance = number_format((float)str_replace(',', '', $jk), 2, '.', '');
                                $distance = (float)$distance * 0.621371;
                            }else{
                                $distance = '';
                            }
                        // dd("in");
                        // echo $distance;

                            if(!empty($getdriversidecheckData->drop_location)){
                                $drop_origin_addresses = $drop_location_user;
                                $drop_destination_addresses = $getdriversidecheckData->drop_location;
                                $drop_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($drop_origin_addresses).'&destinations='.urlencode($drop_destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                                $drop_distance_arr = json_decode($drop_distance_data);
                                $drop_elements = $drop_distance_arr->rows[0]->elements;
                                if(!empty($drop_elements[0]->distance)){
                                    $drop_distance = $drop_elements[0]->distance->text;
                                    $drop_distance = explode(' ', $drop_distance);
                                    $jk = $drop_distance[0];
                                    $drop_distance = number_format((float)str_replace(',', '', $jk), 2, '.', '');
                                    $drop_distance = (float)$drop_distance * 0.621371;
                                }
                            }

                            if(( $getdriversidecheckData->seat_booked == '' || ( $getdriversidecheckData->seat_booked == '0')) && $booking_seat == 'vip' ){
                                if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
                                    $booking_origin_address = $pick_up_location_user;
                                    $booking_end_address = $drop_location_user;
                                    $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
                                    $userdistance_arr = json_decode($user_distance_data);
                                    $elements = $userdistance_arr->rows[0]->elements;
                                    $user_distance = $elements[0]->distance->text;
                                    $user_distance = explode(' ', $user_distance);
                                    $user_distance = round($user_distance[0], 1);
                                    $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
                                    $startmiles = (float)$miles - (float)20;
                                    $endmiles = (float)$miles + (float)20;
                                    if((float)$user_distance <= (float)$endmiles){
                                        $driverDetailsData1[] = $driverDetails;
                                    }
                                }else if(@$drop_distance <= (float)(20) && @$distance <= (float)(20)) {
                                    $driverDetailsData1[] = $driverDetails;
                                }
                            }else if( $taxi_hailing == 'sharing' ){
                                if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
                                    $booking_origin_address = $pick_up_location_user;
                                    $booking_end_address = $drop_location_user;
                                    $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
                                    $userdistance_arr = json_decode($user_distance_data);
                                    $elements = $userdistance_arr->rows[0]->elements;
                                    $user_distance = $elements[0]->distance->text;
                                    $user_distance = explode(' ', $user_distance);
                                    $user_distance = round($user_distance[0], 1);
                                    $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
                                    $startmiles = (float)$miles - (float)20;
                                    $endmiles = (float)$miles + (float)20;
                                    if((float)$user_distance <= (float)$endmiles){
                                        $driverDetailsData1[] = $driverDetails;
                                    }
                                }else if(@$drop_distance <= (float)(20) && @$distance <= (float)(20)) {
                                    $driverDetailsData1[] = $driverDetails;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $driverDetailsData1;
    }*/
    public function getouttowndriverkmresult($start_latitude, $start_longitude, $drop_latitude, $drop_longitude, $dateing, $service, $pick_up_location_user,$drop_location_user, $booking_seat, $booking_start_time, $booking_end_time,$taxi_hailing,$user_start_date,$user_end_date){
        $resultData = User::whereHas('driver_details_settings', function($q) use($service){
            $q->where('ride_type','=', $service);
        })->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved','availability_status'=>'on'])->get();
        $driverDetails=[];
        $driverDetailsData1 = [];
        foreach ($resultData as $key => $value) {
            $getdriversidecheckDataAll = \DB::table('out_of_town_booking')->where('booking_date',$dateing)->where('driver_id',$value->id)->where('deleted_at',null)->get();
            if($getdriversidecheckDataAll->count() > 0){
                foreach ($getdriversidecheckDataAll as $getdriversidecheckData){
                    if(isset($booking_seat)  && ((int)$getdriversidecheckData->seat_available >= (int)$booking_seat)){
                        $start_time = date("H:i", strtotime($booking_start_time)); //user start time
                        $end_time = date("H:i", strtotime($booking_end_time)); //user end time
                        $check_start_time = date("H:i", strtotime($getdriversidecheckData->booking_start_time));  //driver start time
                        $check_end_time = date("H:i", strtotime($getdriversidecheckData->booking_end_time)); //driver start time
                        $start_date = Carbon::createFromTimeString( $user_start_date.' '.$start_time);
                        $end_date = Carbon::createFromTimeString( $user_end_date.' '.$end_time);
                        $begin = Carbon::createFromTimeString( $getdriversidecheckData->start_date.' '.$check_start_time)->subMinute(30);
                        $end = Carbon::createFromTimeString( $getdriversidecheckData->end_date.' '.$check_end_time)->addMinutes(30);
                        // if (($start_date >= $begin) && ($end_date <= $end)){
                        
                        if (($start_date <= $end) && ($end_date >= $begin)){
                        // if($start_date->between($begin, $end) || $end_date->between($begin, $end)){
                            $driverDetails['vehicle_id'] = $value->vehicle_id;
                        // $driverDetails['driver_id'] = $value->id;
                            if(!empty($getdriversidecheckData->start_latitude))
                            {
                                $driver_latitude = $getdriversidecheckData->start_latitude;
                            }
                            if(!empty($getdriversidecheckData->start_longitude)){
                                $driver_longitude = $getdriversidecheckData->start_longitude;
                            }

                            $origin_addresses = $pick_up_location_user;
                            $destination_addresses = $getdriversidecheckData->pick_up_location;
                            $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                            $distance_arr = json_decode($distance_data);
                            $elements = $distance_arr->rows[0]->elements;
                            if(!empty($elements[0]->distance)){
                                $distance = $elements[0]->distance->text;
                                $distance = explode(' ', $distance);
                                $jk =$distance[0];
                                $distance = number_format((float)str_replace(',', '', $jk), 2, '.', '');
                                $distance = (float)$distance * 0.621371;
                            }else{
                                $distance = '';
                            }
                        // dd("in");
                        // echo $distance;

                            if(!empty($getdriversidecheckData->drop_location)){
                                $drop_origin_addresses = $drop_location_user;
                                $drop_destination_addresses = $getdriversidecheckData->drop_location;
                                $drop_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($drop_origin_addresses).'&destinations='.urlencode($drop_destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                                $drop_distance_arr = json_decode($drop_distance_data);
                                $drop_elements = $drop_distance_arr->rows[0]->elements;
                                if(!empty($drop_elements[0]->distance)){
                                    $drop_distance = $drop_elements[0]->distance->text;
                                    $drop_distance = explode(' ', $drop_distance);
                                    $jk = $drop_distance[0];
                                    $drop_distance = number_format((float)str_replace(',', '', $jk), 2, '.', '');
                                    $drop_distance = (float)$drop_distance * 0.621371;
                                }
                            }

                            if(( $getdriversidecheckData->seat_booked == '' || ( $getdriversidecheckData->seat_booked == '0')) && $booking_seat == 'vip' ){
                                if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
                                    $booking_origin_address = $pick_up_location_user;
                                    $booking_end_address = $drop_location_user;
                                    $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
                                    $userdistance_arr = json_decode($user_distance_data);
                                    $elements = $userdistance_arr->rows[0]->elements;
                                    $user_distance = $elements[0]->distance->text;
                                    $user_distance = explode(' ', $user_distance);
                                    $user_distance = str_replace(',','',$user_distance[0]);
                                    $user_distance = round($user_distance, 1);
                                    // $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
                                    $miles = round((float)($getdriversidecheckData->mailes), 1);
                                    $startmiles = (float)$miles - (float)20;
                                    $endmiles = (float)$miles + (float)20;
                                    if((float)($user_distance / 1.609344) <= (float)$endmiles){
                                        $driverDetailsData1[] = $driverDetails;
                                    }
                                }else if(@$drop_distance <= (float)(20) && @$distance <= (float)(20)) {
                                    $driverDetailsData1[] = $driverDetails;
                                }
                            }else if($taxi_hailing == 'sharing'){
                                if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
                                    $booking_origin_address = $pick_up_location_user;
                                    $booking_end_address = $drop_location_user;
                                    $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
                                    $userdistance_arr = json_decode($user_distance_data);
                                    $elements = $userdistance_arr->rows[0]->elements;
                                    $user_distance = $elements[0]->distance->text;
                                    $user_distance = explode(' ', $user_distance);
                                    $user_distance = str_replace(',','',$user_distance[0]);
                                    $user_distance = round($user_distance, 1);
                                    $miles = round((float)($getdriversidecheckData->mailes), 1);
                                    $startmiles = (float)$miles - (float)20;
                                    $endmiles = (float)$miles + (float)20;
                                    if((float)($user_distance / 1.609344) <= (float)$endmiles){
                                        if((int)$getdriversidecheckData->seat_available >= (int)$booking_seat){
                                            $driverDetailsData1[] = $driverDetails;
                                        }
                                    }
                                }else if(@$drop_distance <= (float)(20) && @$distance <= (float)(20)) {
                                    if((int)$getdriversidecheckData->seat_available >= (int)$booking_seat){
                                        $driverDetailsData1[] = $driverDetails;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        // die();
    // dd($driverDetailsData1);
        return $driverDetailsData1;
    }
    public function getdriverkmmlresult($start_latitude, $start_longitude, $distance, $service,$distance_In_setting){
        if($distance_In_setting === 'km'){
            $kmml = "K";
        }else{
            $kmml = "M";
        }
        $resultData = User::whereHas('driver_details_settings', function($q) use($service){
            $q->where('ride_type','=', $service);
        })->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved','availability_status'=>'on'])->get();
        $driverDetails=[];
        $driverDetailsData1 = [];
        foreach ($resultData as $key => $value) {
            $driverDetails['vehicle_id'] = $value->vehicle_id;
            if(!empty($value->latitude)){
                $driver_latitude = $value->latitude;
            }else{
                $driver_latitude = $value->add_latitude;
            }
            if(!empty($value->longitude)){
                $driver_longitude = $value->longitude;
            } else {
                $driver_longitude = $value->add_longitude;
            }
            $driverDetail['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$start_latitude,$start_longitude,$kmml);
            if($driverDetail['distance'] <= $distance){
                $driverDetailsData1[] = $driverDetails;
            }
        }
        return $driverDetailsData1;
    }

    public function distanceByLatLong($lat1, $lon1, $lat2, $lon2, $unit) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);
        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Check Validation
    |--------------------------------------------------------------------------
    |
    */
    public function checkValidation(Request $request){
        $validation_array =[
        'email'           => 'required|unique:users',
        'contact_number'  => 'required|unique:users|min:10|max:15',
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'error','message'=> $validation->errors()->first(),'data'=> (object)[]]);
        }else{
            return response()->json(['status' => 'success','message' => 'All Validation Cleared']);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Driver Documents
    |--------------------------------------------------------------------------
    |
    */
    public function getDriverDocuments(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $documents = DriverDocuments::where('driver_id',$user->id)->get();
            return response()->json(['status' => 'success','message' => 'Get Driver Documents', 'data'=>$documents, 'user_data'=>User::find($user->id)]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Driver Vehicle Documents
    |--------------------------------------------------------------------------
    |
    */
    public function getDriverVehicleDocuments(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $documents = DriverVehicleDocument::where('driver_id',$user->id)->get();
            return response()->json(['status' => 'success','message' => 'Get Vehicle Documents', 'data'=>$documents, 'user_data'=>User::find($user->id)]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Notification
    |--------------------------------------------------------------------------
    |
    */
    public function getAllNotification(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $notification = UserNotification::where('sent_from_user',$user->id)->orWhere('sent_to_user',$user->id)->orderBy('id','desc')->get();

            $userNotificationData = [];
            $notificationID = [];

            if(!empty($notification)){
                foreach ($notification as $userNotification) {
                    if($userNotification->notification_for == "pending"){
                        $to_time =  strtotime("now");
                        $from_time = strtotime($userNotification->created_at);
                        $diff = round(abs($to_time - $from_time) / 60,10);
                        if($diff <= 10){
                            $userNotificationData['id'] = $userNotification->id;
                        }else{
                            $userNotificationData['id']='0';
                        }
                    }else{
                        $userNotificationData['id']=$userNotification->id;
                    }

                    $notificationID[] = $userNotificationData;
                }
            }

            if(!empty($notificationID)){
                $notification = UserNotification::with(['booking_details.ride_setting','driver_details','user_details'])->whereIn('id',$notificationID)->orderBy('id','desc')->get();
                foreach ($notification as $value) {
                    $booking = Booking::where('id',$value->booking_id)->first();
                    $destination_addresses = @$booking->drop_location;
                    $origin_addresses = @$booking->pick_up_location;
                    $distance_In_value_setting = Setting::where('code','distance_in')->first();
                    if($distance_In_value_setting->value == "km"){
                        $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                    }
                    if($distance_In_value_setting->value == "miles") {
                        $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                    }
                    // $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode(@$origin_addresses).'&destinations='.urlencode(@$destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                    $distance_arr = json_decode($distance_data);
                    $elements = @$distance_arr->rows[0]->elements;
                    $value['distance'] = @$elements[0]->distance->text;
                    $value['duration'] = @$elements[0]->duration->text;
                    $value['booking_details']['booking_date'] = date("m-d-Y", strtotime($value['booking_details']['booking_date']));
                    $value['created_date'] = @$value['created_at']->format('m-d-Y H:i:s');
                    // if($elements[0]->status == "ZERO_RESULTS"){
                    //     $value['distance'] = "0";
                    //     $value['duration'] = "0";
                    // }else{
                    //     $value['distance'] = $elements[0]->distance->text;
                    //     $value['duration'] = $elements[0]->duration->text;
                    // }

                }

            }
            return response()->json(['status' => 'success','message' => 'Get notification', 'data'=>$notification]);
            // if($user->user_type == "user"){
            //     $getPending =  UserNotification::with(['booking_details','driver_details','user_details'])->where('sent_from_user', $user->id)->orderBy('id','desc')->get()->toArray();
            // } else {
            //     $getPending =  UserNotification::with(['booking_details','driver_details','user_details'])->where('sent_to_user', $user->id)->orderBy('id','desc')->get()->toArray();
            // }
            // $upcomingdata = [];
            // if( sizeof($getPending) and !empty($getPending)){

            //     foreach ($getPending as $key => $value){
            //         if($value['notification_for'] == 'pending'){
            //             $to_time =  strtotime("now");
            //             $from_time = strtotime($value['created_at']);
            //             $diff = round(abs($to_time - $from_time) / 60,10);
            //             if($diff <= 10){
            //                 $upcomingdata[] = $value;
            //             }
            //         }else if($value['notification_for'] == 'accepted' || $value['notification_for'] == 'cancel' || $value['notification_for'] == 'completed' || $value['notification_for'] == 'emergency' || $value['notification_for'] == 'on_going' || $value['notification_for'] == 'pick_up'){
            //             $upcomingdata[] = $value;
            //         }
            //     }
            // }
            // return response()->json(['status' => 'success','message' => 'Get notification', 'data'=>$upcomingdata]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Set Read Notification
    |--------------------------------------------------------------------------
    |
    */
    public function setReadNotification(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $notification = UserNotification::where('sent_to_user',$user->id)->update(['is_read'=>'read']);
            return response()->json(['status' => 'success','message' => 'All Notification Read']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Ride Setting
    |--------------------------------------------------------------------------
    |
    */
    public function getRideSetting(){
        try {
            return response()->json(['status' => 'success','message' =>'All Ride Setting','data'=>RideSetting::where('status','active')->get()]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Company
    |--------------------------------------------------------------------------
    |
    */
    public function getAllCompany(){
        try {
            return response()->json(['status' => 'success','message' =>'All Company List','data'=>User::where('user_type','company')->select('id', 'company_name')->get()]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Emergency Type
    |--------------------------------------------------------------------------
    |
    */
    public function getAllEmergencyType(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }

            if($user->user_type == "user"){
                $contact_list = EmergencyType::where("status","active")->where('id','6')->get();
            } else {
                $contact_list = EmergencyType::where("status","active")->get();
            }
            if(count($contact_list) != 0){
                return response()->json(['status' => 'success','message' => 'All Emergency Type', 'data'=>$contact_list]);
            }else{
                return response()->json(['status' => 'error','message' => 'No Emergency contact List found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Check User Status
    |--------------------------------------------------------------------------
    |
    */
    public function checkUserStatus(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!empty($user) ){
                if($user->status === 'active'){
                    return response()->json(['status' => 'success','code'=>'200', 'message' => 'User is Active']);
                }else{
                    return response()->json(['status' => 'error','code'=>'400', 'message' => 'User Blocked']);
                }
            }else{
                return response()->json(['status' => 'error','code'=>'200', 'message' => 'User not found']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Set User Report
    |--------------------------------------------------------------------------
    |
    */
    public function setUserReport(Request $request){
        $validation_array =[
        'description'  => 'required',
        'booking_id'   => 'required',
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'error','message'=> $validation->errors()->first(),'data'=> (object)[]]);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            if($request->get('booking_id')){
                $getbookingdetail = Booking::find($request->get('booking_id'));
            }
            if($user->id == $getbookingdetail->user_id){
                $touserid = $getbookingdetail->driver_id;
            }else{
                $touserid = $getbookingdetail->user_id;
            }
            $userreport = UserReport::create([
                'from_user_id' => @$user->id,
                'to_user_id' => @$touserid,
                'title' => "Set User Repoert",
                'description' => $request->get('description'),
                'date' => Carbon::now()->format('Y-m-d H:i'),
                'time' => Carbon::now()->format('H:i'),
                'booking_id' => @$getbookingdetail->id,
                ]);
            return response()->json(['status' => 'success','message' => 'User Report Request Submitted Successfully', 'data'=>$userreport]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

/*
|--------------------------------------------------------------------------
| Get User Parcel List
|--------------------------------------------------------------------------
|
*/
public function getUserParcelList(){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $past_parcelist = ParcelDetail::with(['parcel_images','parcel_packages','driver','user'])
        ->where('booking_date','>=', Carbon::now()->format('Y-m-d'))
        ->whereIn('parcel_status',['completed','cancelled'])
        ->where('user_id', $user->id)->get();
        // dd($past_parcelist);
        $upcoming_parcelist = ParcelDetail::with(['parcel_images','parcel_packages','driver','user'])
        ->where('booking_date','>=', Carbon::now()->format('Y-m-d'))
        ->whereIn('parcel_status', [ 'pending','accepted','on_going', 'driver_arrived'])
        ->where('user_id', $user->id)->get();

        $upcomingdata = array();
        if(sizeof($upcoming_parcelist) > 0){
            foreach($upcoming_parcelist as $key => $ride){
                $bookingtime = explode(' ', $ride->booking_start_time);
                $date =   \Carbon\Carbon::parse($ride->booking_date.' '.$bookingtime[0]);
                $dt = \Carbon\Carbon::now();
                $totalDuration = $date->diffForHumans($dt);

                if($ride->parcel_status == 'accepted' || $ride->parcel_status == 'driver_arrived' || $ride->parcel_status == 'on_going'){
                    array_push($upcomingdata, $ride);
                }elseif($date->isFuture()){
                    array_push($upcomingdata, $ride);
                }
            }
        }

        $data['past_parcelist']       = $past_parcelist;
        $data['upcoming_parcelist']    = $upcomingdata;
        return response()->json(['status' => 'success','message' => 'Get User Parcel List', 'data'=>$data]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

/*
|--------------------------------------------------------------------------
| Get User Parcel List
|--------------------------------------------------------------------------
|
*/
public function getDriverParcelList(){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $past_parcelist = ParcelDetail::with(['parcel_images','parcel_packages','driver','user'])->where('booking_date','<=', Carbon::now()->format('Y-m-d'))
        ->where('driver_id', $user->id)
        ->whereIn('parcel_status',['completed','cancelled'])
        ->orderBy('parcel_details.id','DESC')->get();


        $upcoming_parcelist = ParcelDetail::with(['parcel_images','parcel_packages','driver','user'])
        ->where('booking_date','>=', Carbon::now()->format('Y-m-d'))
        ->where('driver_id', $user->id)
        ->whereIn('parcel_status', [ 'accepted','on_going', 'driver_arrived'])
        ->orderBy('parcel_details.id','DESC')->get();
        $upcomingdata=array();
        if(sizeof($upcoming_parcelist) > 0){
            foreach($upcoming_parcelist as $key => $ride){
                $bookingtime = explode(' ', $ride->booking_start_time);
                $date =   \Carbon\Carbon::parse($ride->booking_date.' '.$bookingtime[0]);
                $dt = \Carbon\Carbon::now();
                $totalDuration = $date->diffForHumans($dt);

                if($ride->parcel_status == 'accepted' || $ride->parcel_status == 'driver_arrived' || $ride->parcel_status == 'on_going'){
                    array_push($upcomingdata, $ride);
                }elseif($date->isFuture()){
                    array_push($upcomingdata, $ride);
                }
            }
        }

//        $pending_parcelist = ParcelDetail::with(['parcel_images','parcel_packages'])->where('parcel_details.booking_date','>', Carbon::now()->format('Y-m-d'))
//            ->where('user_notifications.sent_to_user', $user->id)
//            ->join('user_notifications','user_notifications.parcel_id','=','parcel_details.id')
//            ->orderBy('parcel_details.id','DESC')
//            ->get();

        $pending_parcelist = ParcelDetail::with(['parcel_images','parcel_packages','driver','user'])->where('parcel_details.booking_date','>', Carbon::now()->format('Y-m-d'))
        ->where('parcel_drivers.driver_id', $user->id)
        ->where('parcel_drivers.status', 'pending')
        ->join('parcel_drivers','parcel_drivers.parcel_id','=','parcel_details.id')
        ->orderBy('parcel_details.id','DESC')
        ->get();

        $data['pending_parcelist']    = $pending_parcelist;
        $data['past_parcelist']       = $past_parcelist;
        $data['upcoming_parcelist']   = $upcomingdata;
        return response()->json(['status' => 'success','message' => 'Get Driver Parcel List', 'data'=>$data]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

/*
|--------------------------------------------------------------------------
| Get Parcel Details
|--------------------------------------------------------------------------
|
*/
public function getParcelDetails(Request $request){
    $validation_array =[
    'parcel_id'  => 'required',
    ];
    $validation = Validator::make($request->all(),$validation_array);
    if($validation->fails()){
        return response()->json(['status' => 'error','message'=> $validation->errors()->first(),'data'=> (object)[]]);
    }
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $past_parcelist = ParcelDetail::with(['parcel_images','parcel_packages'])->where('id',$request->parcel_id)->first();
        $data           = $past_parcelist;
        $image_path     = url('/storage/parcel_image/');
        return response()->json(['status' => 'success','message' => 'Get Parcel Details', 'data'=>$past_parcelist]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

/*
|--------------------------------------------------------------------------
| Parcel Booking Cancelled
|--------------------------------------------------------------------------
|
*/
public function parcelbookingCancelled(Request $request){
    $validation_array =[
    'parcel_id'       => 'required'
    ];
    $validation = Validator::make($request->all(),$validation_array);
    if($validation->fails()){
        return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
    }
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $parceldetail = ParcelDetail::where('id', $request->parcel_id)->first();
        if(!empty($parceldetail)){
            if($request->extra_notes){
                $extra_notes = $request->extra_notes;
                ParcelDetail::where('id', $parceldetail->id)->update(['parcel_status'=>'cancelled', 'extra_notes' => $extra_notes]);
            }else{
                ParcelDetail::where('id', $parceldetail->id)->update(['parcel_status'=>'cancelled']);
            }
            // Notification for user
            $data['message'] =  'Parcel Booking Cancelled Successfully';
            $data['type'] = 'parcel_cancelled';
            $data['user_id'] = $parceldetail->user_id;
            $data['notification'] = Event::dispatch('send-notification-assigned-user',array($parceldetail,$data));

            // Notification for driver
            $driver_data['message'] =  'Parcel Booking Cancelled Successfully';
            $driver_data['type'] = 'parcel_cancelled';
            $driver_data['driver_id'] = $parceldetail->driver_id;
            $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($parceldetail,$driver_data));
            if($user->id == $parceldetail->user_id){
                $touserid = $parceldetail->driver_id;
            }else{
                $touserid = $parceldetail->user_id;
            }
            PopupNotification::create([
                'from_user_id'      => $user->id,
                'to_user_id'        => $touserid,
                'title'             => 'Booking Cancelled',
                'description'       => 'Parcel Booking Cancelled Successfully',
                'date'              => Carbon::now()->format('Y-m-d H:i'),
                'time'              => Carbon::now()->format('H:i'),
                'parcel_id'         => $parceldetail->id,
                ]);
            return response()->json(['status' => 'success','message' => 'Parcel Booking has been cancelled Successfully', 'data'=>ParcelDetail::find($request->parcel_id)]);
        }else{
            return response()->json(['status' => 'error','message' => 'Something went Wrong']);
        }
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

/*
|--------------------------------------------------------------------------
| Set admin Percent to Wallet
|--------------------------------------------------------------------------
|
*/
public function setAdminPercent(){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $getAdmin = User::where('user_type','superadmin')->first();
        $getAdminrate = Setting::where('code','comission_rate')->first();
        if(!empty($getAdmin)){
            $checkadminwallet = Wallet::where('user_id', $getAdmin->id)->first();
            // Used Dummy Amount
            $fill_amount = (223 * (int)$getAdminrate->value) / 100;
            if(!empty($checkadminwallet)){
                $final_amount = round((float)$fill_amount + (float)$checkadminwallet->amount);
                Wallet::where('user_id', $getAdmin->id)->update(['amount'=> $final_amount]);
            }else{
                Wallet::create(['amount'=> round($fill_amount), 'user_id'=>$getAdmin->id]);
            }
            WalletHistory::create([
                'user_id'       => $getAdmin->id,
                'amount'        => round($fill_amount),
                'description'   => '$ '.round($fill_amount).' added to Your Wallet for Ride Booking',
                ]);
            return response()->json(['status' => 'success','message' => 'Admin Amount Added Successfully']);
        }else{
            return response()->json(['status' => 'error','message' => 'Something went wrong']);
        }
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}


public function makecarType(Request $request){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $vehicles = VehicleBody::get();
        return response()->json(['status' => 'success','message' => 'Get Vehicle Successfully','data'=>$vehicles]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}
/*
|--------------------------------------------------------------------------
| Get Car Type
|--------------------------------------------------------------------------
|
*/
public function getcarType(Request $request){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        $vehicles = Vehicle::get();
        // $vehicles = Vehicle::where('vehicle_model_id',$request->vehicle_model_id)->get();
        return response()->json(['status' => 'success','message' => 'Get Vehicle Successfully','data'=>$vehicles]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

    /*
    |--------------------------------------------------------------------------
    | Get User Behavior List
    |--------------------------------------------------------------------------
    |
    */
    public function get_user_behavior_list(Request $request){
        try {
            $CompleteTripReview = UserBehavior::where('flag','1')->get();
            return response()->json(['status' => 'success','message' => 'Successfully','data'=>$CompleteTripReview]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

/*
|--------------------------------------------------------------------------
| Get Driver Behavior List
|--------------------------------------------------------------------------
|
*/
public function get_driver_behavior_list(Request $request){
    try {
        $CompleteTripReview = UserBehavior::where('flag','0')->get();
        return response()->json(['status' => 'success','message' => 'Successfully','data'=>$CompleteTripReview]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

/*
|--------------------------------------------------------------------------
| Get All Parcel Type
|--------------------------------------------------------------------------
|
*/
public function getAllParcelType(){
    try {
        $parceltypes = ParcelType::orderBy('id','DESC')->get();
        $packagetypes = Setting::where('code','package_type')->first();
        $data['parcel_types'] = $parceltypes;
            //$data['package_types'] = json_decode($packagetypes->value);
        return response()->json(['status' => 'success','message' => 'Parcel Types','data'=>$data]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

public function getAllParcelPackage(){
    try {
        $parceltypes = ParcelType::orderBy('id','DESC')->get();
        $packagetypes = Setting::where('code','package_type')->first();
            // $data['parcel_types'] = $parceltypes;
        $data['package_types'] = json_decode($packagetypes->value);
        return response()->json(['status' => 'success','message' => 'Parcel Package','data'=>$data]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

/*
|--------------------------------------------------------------------------
| Get Ongoing Trip
|--------------------------------------------------------------------------
|
*/
public function getOngoingTrip(){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        if($user->user_type == "driver"){
            $ongoing = Booking::with(['driver','user','ride_setting'])->where('trip_status','on_going')->where('driver_id',$user->id)->orderBy('id','desc')->first();
        }else{
            $ongoing = Booking::with(['driver','user','ride_setting'])->where('trip_status','on_going')->where('user_id',$user->id)->orderBy('id','desc')->first();
        }
        if(!empty($ongoing)){
            $destination_addresses = $ongoing->drop_location;
            $origin_addresses = $ongoing->pick_up_location;
            $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
            $distance_arr = json_decode($distance_data);
            $elements = $distance_arr->rows[0]->elements;
            $distance = $elements[0]->distance->text;
            $duration = $elements[0]->duration->text;
            $ongoing['distance'] = $distance;
            $ongoing['duration'] = $duration;
        }else{
            $ongoing = "";
        }
        return response()->json(['status' => 'success','message' => 'Last Trip On Going','data'=>$ongoing]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

public function getparcelOngoingTrip(){
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if(!$user){
            return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
        }
        if($user->user_type == "driver"){
            $ongoing = ParcelDetail::with(['driver','user'])->where('parcel_status','on_going')->where('driver_id',$user->id)->orderBy('id','DESC')->first();
        }else{
            $ongoing = ParcelDetail::with(['driver','user'])->where('parcel_status','on_going')->where('user_id',$user->id)->orderBy('id','DESC')->first();
        }
        if(!empty($ongoing)){
            $destination_addresses = $ongoing->drop_location;
            $origin_addresses = $ongoing->pick_up_location;
            $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
            $distance_arr = json_decode($distance_data);
            $elements = $distance_arr->rows[0]->elements;
            $distance = $elements[0]->distance->text;
            $duration = $elements[0]->duration->text;
            $ongoing['distance'] = $distance;
            $ongoing['duration'] = $duration;
        }
        return response()->json(['status' => 'success','message' => 'Last Trip On Going','data'=>$ongoing]);
    } catch (Exception $e) {
        return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

    /*
    |--------------------------------------------------------------------------
    | Parcel Booking Cancelled
    |--------------------------------------------------------------------------
    |
    */
    public function shuttleBookingDetail(Request $request){
        $validation_array =[
        'shuttle_id'       => 'required'
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $data = LinerideUserBooking::with(['user','driver','driver_shuttle'])->where('id', $request->shuttle_id)->first();
            return response()->json(['status' => 'success','message' => 'Shuttle Booking Detail Successfully', 'data'=>$data]);

        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Get Driver All Rating Reviews
    |--------------------------------------------------------------------------
    |
    */
    public function getDriverAllRatingReviews(Request $request){
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $rating_reviews = RatingReviews::with('from_user')->where('to_user_id',$user->id)->where('status','approved')->orderBy('id','DESC')->get()->toArray();
            $rating = RatingReviews::where('to_user_id', $user->id)->where('status','approved')->get();
            $avg_star_rating = number_format((float)$rating->avg('rating'), 1, '.', '');

            $five_rating    = RatingReviews::where('to_user_id', $user->id)->whereIn('rating',['5.0'])->where('status','approved')->get()->count();
            $four_rating    = RatingReviews::where('to_user_id', $user->id)->whereIn('rating',['4.0','4.5'])->where('status','approved')->get()->count();
            $three_rating   = RatingReviews::where('to_user_id', $user->id)->whereIn('rating',['3.0','3.5'])->where('status','approved')->get()->count();
            $two_rating     = RatingReviews::where('to_user_id', $user->id)->whereIn('rating',['2.0','2.5'])->where('status','approved')->get()->count();
            $one_rating     = RatingReviews::where('to_user_id', $user->id)->whereIn('rating',['1.0','1.5'])->where('status','approved')->get()->count();

            $all_rating = [];
            $all_rating['five_rating']      = $five_rating;
            $all_rating['four_rating']      = $four_rating;
            $all_rating['three_rating']     = $three_rating;
            $all_rating['two_rating']       = $two_rating;
            $all_rating['one_rating']       = $one_rating;

            if(count($rating_reviews) != 0){
                return response()->json(['status'=>'success','message'=> 'Driver Rating Reviews!','data'=> $rating_reviews, 'avg_star_rating'=>$avg_star_rating, 'all_rating' => $all_rating ]);
            }else{
                return response()->json(['status'=> 'error','message'=>'Rating Reviews Record List Not Found',  'avg_star_rating'=>0, 'all_rating' => [] ]);
            }
        }catch(Exception $e){
            return response()->json(['status'  => 'error','message' => $e->getMessage()]);
        }
    }

    // public function getDriverOutTwonRides(Request $request){
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         // dd($user->id);
    //         $data = OutTwonrideBooking::where('driver_id', $user->id)->where('booking_date','=>',$request->current_date)->get();
    //         // dd($data);
    //         return response()->json(['status' => 'success','message' => 'Out Twon Booking Detail Successfully', 'data'=>$data ,'Driver_Data'=>User::find($user->id)]);

    //     } catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    //     }
    // }

    // public function getDriverOutTwonRides(Request $request){
    //     try {
    //         $user = JWTAuth::parseToken()->authenticate();
    //         if(!$user){
    //             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    //         }
    //         // dd($user->id);
    //         $data = OutTwonrideBooking::where('driver_id', $user->id)->where('booking_date','=>',$request->current_date)->get();
    
    //         if(sizeof($data) > 0){
    //         return response()->json(['status' => 'success','message' => 'Out Twon Booking Detail Successfully', 'data'=>$data ,'Driver_Data'=>User::find($user->id)]);    
    //     } else {
    //         return response()->json(['status' => 'error','message' => 'No Out Twon Booking Detail']);    
    //     }
    

    //     } catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    //     }
    // }
    public function getDriverOutTwonRides(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }

            // $mytime = Carbon::now();
            // dd($mytime);
            // $data = OutTwonrideBooking::where('driver_id', $user->id)->where('booking_date','=>',$request->current_date)->get();
            $data = OutTwonrideBooking::where('driver_id', $user->id)->where('booking_date','>=',$request->current_date)->get();
            foreach ($data as $key => $value) {
                if($value->seat_available == '0'){
                    $value['outTownrideStatus'] = 'Booked';
                }else{
                    $value['outTownrideStatus'] = '';
                }
            }
            if(sizeof($data) > 0){
               return response()->json(['status' => 'success','message' => 'Out Twon Booking Detail Successfully', 'data'=>$data ,'Driver_Data'=>User::find($user->id)]);    
           } else {
               return response()->json(['status' => 'error','message' => 'No Out Twon Booking Detail']);    
           }


       } catch (Exception $e) {
          return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
      }
  }


  public function filtering(Request $request){
     $validation_array =[
     'ride_setting_id'       => 'required'
     ];
     $validation = Validator::make($request->all(),$validation_array);
     if($validation->fails()){
      return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
  }
  try {
      $user = JWTAuth::parseToken()->authenticate();
      if(!$user){
       return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
   }
   if($user->user_type == "user"){
       if($request->ride_setting_id == "1"){
        $data = Booking::with(['driver','ride_setting'])
        ->whereHas('driver')
        ->where('ride_setting_id', "1")
        ->where('user_id', $user->id)
        ->orderBy('id','DESC')->get();

    } elseif($request->ride_setting_id == "2") {
        $data = Booking::with(['driver','ride_setting'])
        ->whereHas('driver')
        ->where('ride_setting_id', "2")
        ->where('user_id', $user->id)
        ->orderBy('id','DESC')->get();
    } elseif($request->ride_setting_id == "3") {
        $data = LinerideUserBooking::with(['driver','ride_setting','user'])
        ->whereHas('driver')
        ->where('ride_setting_id', "3")
        ->where('user_id', $user->id)
        ->orderBy('id','DESC')->get();
    }elseif($request->ride_setting_id == "4") {
        $data = ParcelDetail::with(['user','ride_setting','driver'])
        ->whereHas('driver')
        ->where('ride_setting_id', "4")
        ->where('user_id', $user->id)
        ->orderBy('id','DESC')->get();
    }
} else {
   if($request->ride_setting_id == "1"){
    $data = Booking::with(['driver','ride_setting'])
    ->whereHas('driver')
    ->where('ride_setting_id', "1")
    ->where('driver_id', $user->id)
    ->orderBy('id','DESC')->get();

} elseif($request->ride_setting_id == "2") {
    $data = Booking::with(['driver','ride_setting'])
    ->whereHas('driver')
    ->where('ride_setting_id', "2")
    ->where('driver_id', $user->id)
    ->orderBy('id','DESC')->get();
} elseif($request->ride_setting_id == "3") {
    $data = LinerideUserBooking::with(['driver','ride_setting','user'])
    ->whereHas('driver')
    ->where('ride_setting_id', "3")
    ->where('driver_id', $user->id)
    ->orderBy('id','DESC')->get();
}elseif($request->ride_setting_id == "4") {
    $data = ParcelDetail::with(['user','ride_setting','driver'])
    ->whereHas('driver')
    ->where('ride_setting_id', "4")
    ->where('driver_id', $user->id)
    ->orderBy('id','DESC')->get();
}
}
return response()->json(['status' => 'success','message' => 'Successfully Record', 'data'=>$data]);

} catch (Exception $e) {
  return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
}
}

    /*
    |--------------------------------------------------------------------------
    | Check User Payment Status
    |--------------------------------------------------------------------------
    |
    */
    public function checkUserPayment(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }

            $checkBooking = Booking::where('user_id', $user->id)->first();
            $checkParcelBooking = ParcelDetail::where('user_id', $user->id)->first();
            $checkShuttleBooking = LinerideUserBooking::where('user_id', $user->id)->first();
            if(empty($checkBooking) && empty($checkParcelBooking) && empty($checkShuttleBooking)){
                return response()->json(['status' => 'success','message' => 'User has no Booking']);
            }

            $allUserBooking = Booking::where('user_id', $user->id)->where('trip_status', 'completed')->get();
            $allUserParcelBooking = ParcelDetail::where('user_id', $user->id)->where('parcel_status', 'completed')->get();
            $allUserShuttleBooking = LinerideUserBooking::where('user_id', $user->id)->where('trip_status', 'completed')->get();

            if($allUserBooking->count() > 0){
                foreach($allUserBooking as $booking){
                    $checkBookingPendingPayment = TransactionDetail::where('user_id', $user->id)->where('booking_id', $booking->id)->first();
                    if(empty($checkBookingPendingPayment)){
                        return response()->json(['status' => 'error','message' => 'Your previous Booking Payment is Pending, Please Pay first to book another ride.']);
                    }
                }
            }else if($allUserParcelBooking->count() > 0){
                foreach($allUserParcelBooking as $pbooking){
                    $checkParcelPendingPayment = TransactionDetail::where('user_id', $user->id)->where('parcel_id', $pbooking->id)->first();
                    if(empty($checkParcelPendingPayment)){
                        return response()->json(['status' => 'error','message' => 'Your previous Parcel Booking Payment is Pending, Please Pay first to book another ride']);
                    }
                }
            }else if($allUserShuttleBooking->count() > 0){
                foreach($allUserShuttleBooking as $sbooking){
                    $checkShuttlePendingPayment = TransactionDetail::where('user_id', $user->id)->where('shuttle_id', $sbooking->id)->first();
                    if(empty($checkShuttlePendingPayment)){
                        return response()->json(['status' => 'error','message' => 'Your previous Shuttle Booking Payment is Pending, Please Pay first to book another ride']);
                    }
                }
            }else{
                return response()->json(['status' => 'success','message' => 'Your all Payment Done ']);
            }
            return response()->json(['status' => 'success','message' => 'Your all Payment Done']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }
    public function getNetwork(){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $user_code = $user->uuid;
            $code_check = User::where('ref_id',$user_code)->orderBy('id', 'DESC')->get();
            if(!empty($code_check)){
                return response()->json(['status' => 'success','message' => 'My NetWork Record Successfully', 'data' => $code_check]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }


    public function SaveBankDetail(Request $request){
        $validation_array =[
        'bank_name'          => 'required',
        'routing_number'     => 'required',
        'account_number'     => 'required',
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status' => 'error','message'   => $validation->errors()->first(),'data'=> (object)[]]);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }

            $bank_data = BankDetail::where('id',$request->id)->where('user_id',$user->id)->first();
            if(!empty($bank_data)){
                $bankdata = BankDetail::where('id', $request->id)->update([ 
                    'bank_name'      => $request->bank_name,
                    'routing_number' => $request->routing_number,
                    'account_number' => $request->account_number,
                    ]);
                $msg = "Bank details updated successfully";
            } else {
                $data['bank_name']         =request('bank_name');
                $data['routing_number']    =request('routing_number');
                $data['account_number']    =request('account_number');
                $data['user_id']           =$user->id;
                $bankdata = BankDetail::Create($data);
                $msg = "Bank details saved successfully";

            // $stripe = new \Stripe\StripeClient('sk_test_4eC39HqLyjWDarjtT1zdp7dc');
            // $customer = $stripe->accounts->create([
            //     'type' => 'custom',
            //     'country' => 'US',
            //     'email' => 'jaymin@mailinator.com',
            //     'capabilities' => [
            //         'card_payments' => ['requested' => true],
            //         'transfers' => ['requested' => true],
            //     ],
            // ]);
            // dd($customer);
            }
            return response()->json(['status' => 'success','message' => $msg]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }


    public function GetBankDetail(){
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $bank_data = BankDetail::where('user_id',$user->id)->get();
        // dd($bank_data);
            if(sizeof($bank_data) > 0){
                return response()->json(['status' => 'success','message' => 'You are successfully BankDetail!','data' => $bank_data]);
            }else{
                return response()->json(['status' => 'success','message' => 'You are BankDetail Not successfully!']);

            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }

    }

    public function jk(){
        try{
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
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }

    }
    public function paymentMethod(){
        try{
            $response = array();
            if(\Settings::get('wallet') == true){
                $response['wallet'] = 'visible';
            }else{
                $response['wallet'] = 'hide';
            }
            // if(\Settings::get('case_on_delivery') == true){
            //     $response['case_on_delivery'] = 'visible';
            // }else{
            //      $response['case_on_delivery'] = 'hide';
            // }
            if(\Settings::get('stripe') == true){
                $response['stripe'] = 'visible';
            }else{
                $response['stripe'] = 'hide';
            }
            return response()->json(['status' => 'success','message' => 'Payment Method getting successfully','data'=>$response]);
        }catch(Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);    
        }
    }
    public function searchdriverofcron(){
        $service = '2';
        $driverDetails=[];
        $driverDetailsData1 = [];
        $current_date = Carbon::now()->format('Y-m-d');
        $booking_datas = Booking::where('trip_status','pending')->where('booking_type','out_of_town')->where('booking_date','>=',$current_date)->get();
        foreach ($booking_datas as $key => $booking_data) {
            $resultData = User::whereHas('driver_details_settings', function($q) use($service){
                $q->where('ride_type','=', $service);})->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved','availability_status'=>'on'])->where('vehicle_id',$booking_data->vehicle_id)->get();
            foreach ($resultData as $key => $value) {
                $getdriversidecheckDataAll = \DB::table('out_of_town_booking')->where('booking_date',$booking_data->booking_date)->where('driver_id',$value->id)->where('deleted_at',null)->get();
                if($getdriversidecheckDataAll->count() > 0){
                    foreach ($getdriversidecheckDataAll as $getdriversidecheckData){
                        if(isset($booking_data->seats)  && ((int)$getdriversidecheckData->seat_available >= (int)$booking_data->seats)){
                            $start_time = date("H:i", strtotime($booking_data->booking_start_time)); 
                            $end_time = date("H:i", strtotime($booking_data->booking_end_time)); 
                            $check_start_time = date("H:i", strtotime($getdriversidecheckData->booking_start_time)); 
                            $check_end_time = date("H:i", strtotime($getdriversidecheckData->booking_end_time)); 
                            $start_date = Carbon::createFromTimeString($booking_data->bstart_date.' '.$start_time);
                            $end_date = Carbon::createFromTimeString($booking_data->bend_date.' '.$end_time);
                            $begin = Carbon::createFromTimeString($getdriversidecheckData->start_date.' '.$check_start_time)->subMinute(30);
                            $end = Carbon::createFromTimeString($getdriversidecheckData->end_date.' '.$check_end_time)->addMinutes(30);
                            if (($start_date <= $end) && ($end_date >= $begin)){
                                $driverDetails['driver_id'] = $value->id;
                                $driverDetails['email'] = $value->email;
                                $driverDetails['availability_status'] = $value->availability_status;
                                $driverDetails['out_town_driver_id'] = $getdriversidecheckData->id;
                                $driverDetails['booking_id'] = $booking_data->id;
                                $driverDetails['vehicle_id'] = $booking_data->vehicle_id;
                                if(!empty($getdriversidecheckData->start_latitude))
                                {
                                    $driver_latitude = $getdriversidecheckData->start_latitude;
                                }
                                if(!empty($getdriversidecheckData->start_longitude)){
                                    $driver_longitude = $getdriversidecheckData->start_longitude;
                                }
                                $origin_addresses = $booking_data->pick_up_location;
                                $destination_addresses = $getdriversidecheckData->pick_up_location;
                                $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
                                $distance_arr = json_decode($distance_data);
                                $elements = $distance_arr->rows[0]->elements;
                                if(!empty($elements[0]->distance)){
                                    $distance = $elements[0]->distance->text;
                                    $distance = explode(' ', $distance);
                                    $jk =$distance[0];
                                    $distance = number_format((float)str_replace(',', '', $jk), 2, '.', '');
                                    $distance = (float)$distance * 0.621371;
                                }else{
                                    $distance = '';
                                }
                                if(!empty($getdriversidecheckData->drop_location)){
                                    $drop_origin_addresses = $booking_data->drop_location;
                                    $drop_destination_addresses = $getdriversidecheckData->drop_location;
                                    $drop_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($drop_origin_addresses).'&destinations='.urlencode($drop_destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
                                    $drop_distance_arr = json_decode($drop_distance_data);
                                    $drop_elements = $drop_distance_arr->rows[0]->elements;
                                    if(!empty($drop_elements[0]->distance)){
                                        $drop_distance = $drop_elements[0]->distance->text;
                                        $drop_distance = explode(' ', $drop_distance);
                                        $jk = $drop_distance[0];
                                        $drop_distance = number_format((float)str_replace(',', '', $jk), 2, '.', '');
                                        $drop_distance = (float)$drop_distance * 0.621371;
                                    }
                                }
                                if(($getdriversidecheckData->seat_booked == '' || ( $getdriversidecheckData->seat_booked == '0')) && $booking_data->seats == 'vip' ){
                                    if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
                                        $booking_origin_address = $booking_data->pick_up_location;
                                        $booking_end_address = $booking_data->drop_location;
                                        $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
                                        $userdistance_arr = json_decode($user_distance_data);
                                        $elements = $userdistance_arr->rows[0]->elements;
                                        $user_distance = $elements[0]->distance->text;
                                        $user_distance = explode(' ', $user_distance);
                                        $user_distance = str_replace(',','',$user_distance[0]);
                                        $user_distance = round($user_distance, 1);
                                        $miles = round((float)($getdriversidecheckData->mailes), 1);
                                        $startmiles = (float)$miles - (float)20;
                                        $endmiles = (float)$miles + (float)20;
                                        if((float)($user_distance/1.609344) <= (float)$endmiles){
                                            $driverDetailsData1[] = $driverDetails;
                                        }
                                    }else if(@$drop_distance <= (float)(20) && @$distance <= (float)(20)) {
                                        $driverDetailsData1[] = $driverDetails;
                                    }
                                }else if( $booking_data->taxi_hailing == 'sharing' ){
                                    if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
                                        $booking_origin_address = $booking_data->pick_up_location;
                                        $booking_end_address = $booking_data->drop_location;
                                        $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
                                        $userdistance_arr = json_decode($user_distance_data);
                                        $elements = $userdistance_arr->rows[0]->elements;
                                        $user_distance = $elements[0]->distance->text;
                                        $user_distance = explode(' ', $user_distance);
                                        $user_distance = str_replace(',','',$user_distance[0]);
                                        $user_distance = round($user_distance, 1);
                                        $miles = round((float)($getdriversidecheckData->mailes), 1);
                                        $startmiles = (float)$miles - (float)20;
                                        $endmiles = (float)$miles + (float)20;
                                        if((float)($user_distance / 1.609344) <= (float)$endmiles){
                                            if((int)$getdriversidecheckData->seat_available >= (int)$booking_data->seats){
                                                $driverDetailsData1[] = $driverDetails;
                                            }
                                        }
                                    }else if(@$drop_distance <= (float)(20) && @$distance <= (float)(20)) {
                                        if((int)$getdriversidecheckData->seat_available >= (int)$booking_data->seats){
                                            $driverDetailsData1[] = $driverDetails;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        if(count($driverDetailsData1)>0){
            $initial=0;
            foreach ($driverDetailsData1 as $res) {
                $initial=$initial++;
                $booking_detail = Booking::where('id',$res['booking_id'])->first();
                $user_detail = User::where('id',$booking_detail->user_id)->first();
                if(!empty($user_detail)){
                    $user_name = @$user_detail->last_name.' '.@$user_detail->first_name;
                } else {
                    $user_name = @$user_detail->company_name;
                }
                $ride_setting_detail = RideSetting::where('id',$booking_detail->ride_setting_id)->first();
                $destination_addresses = $booking_detail->drop_location;
                $origin_addresses = $booking_detail->pick_up_location;
                if(!empty($booking_detail)){
                    $distance = $booking_detail->total_km .' miles';
                } else {
                    $distance = 0;
                }
                $checkIfNotifiactionAlreadySent = BookingNotifications::where('booking_id',$res['booking_id'])->where('driver_id',$res['driver_id'])->where('is_send','1')->first();
                if($res !== null){
                    $driver = User::where('id',$res['driver_id'])->first();
                    $vehicle = VehicleType::where('id',$driver->vehicle_id)->first();

                    $avgrating = RatingReviews::where('from_user_id',$res['driver_id'])->orWhere('to_user_id',$res['driver_id'])->avg('rating');
                    if(empty($avgrating)){
                        $avgrating = 0;
                    }
                    $distance_In_value_setting = Setting::where('code','distance_in')->first();
                    if($distance_In_value_setting->value == "km"){
                        $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                    } elseif($distance_In_value_setting->value == "miles") {
                        $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
                    }

                    if($booking_detail->ride_setting_id == "2"){
                        $flag_city = "out_of_town";
                        $end_time = '- '.$booking_detail->booking_end_time;
                    }
                    $distance_arr = json_decode($distance_data);
                    $elements = $distance_arr->rows[0]->elements;
                    $duration = $elements[0]->duration->text;
                    $distance = $elements[0]->distance->text;

                    $start_time = $booking_detail->booking_start_time;
                    if($booking_detail->ride_setting_id == "1"){
                        if($booking_detail->booking_type == 'schedule'){
                            $end_time = '- '.$booking_detail->booking_end_time;
                        }else{
                            $end_time = '';
                        }
                    }
                    $date_format = date_create($booking_detail->booking_date);
                    $dateing = date_format($date_format,"m/d/Y").' '.@$start_time.' '.@$end_time;

                    $driver_data['elements'] = $elements;
                    $driver_data['duration'] = $duration;
                    $driver_data['distance'] = $distance;
                    $driver_data['user_name'] = @$user_name;
                    $driver_data['avatar'] = $user_detail->avatar;
                    $driver_data['ride_setting'] = $ride_setting_detail->name;
                    $driver_data['booking_id'] = $booking_detail->id;
                    $driver_data['booking_type'] = $booking_detail->booking_type;
                    $driver_data['taxi_healing'] = $booking_detail->taxi_hailing;
                    $driver_data['pick_up_location'] = $booking_detail->pick_up_location;
                    $driver_data['drop_location'] = $booking_detail->drop_location;
                    $driver_data['start_latitude'] = $booking_detail->start_latitude;
                    $driver_data['start_longitude'] = $booking_detail->start_longitude;
                    $driver_data['drop_latitude'] = $booking_detail->drop_latitude;
                    $driver_data['drop_longitude'] = $booking_detail->drop_longitude;
                    $driver_data['car_name'] = $vehicle->name;
                    $driver_data['date_time'] = @$dateing;
                    $driver_data['driver_review'] = $avgrating;
                    // $driver_data['message'] =  'New Booking Arrived from '.@$user_detail->last_name.' '.@$user_detail->first_name;
                    $driver_data['message'] =  'New Booking Arrived';
                    $driver_data['type'] = 'new_booking';
                    $driver_data['driver_id'] = $res['driver_id'];
                    $driver_data['availability_status'] = $res['availability_status'];
                    $driver_data['title']        =  'RueRun';
                    $driver_data['sound']        = 'default';
                    if(!empty(@$res['out_town_driver_id'])){
                        $driver_data['out_town_driver_id']        =  @$res['out_town_driver_id'];
                    } else {
                        $driver_data['out_town_driver_id']        =  "";
                    }
                    if($checkIfNotifiactionAlreadySent === null){
                        $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver,$driver_data));
                        BookingNotifications::create([
                            'driver_id'  => $res['driver_id'],
                            'booking_id' => $res['booking_id'],
                            'is_send'    => '1',
                            ]);
                        UserNotification::create([
                            'sent_from_user' => $user_detail->id,
                            'sent_to_user' => $res['driver_id'],
                            'booking_id' => @$driver_data['booking_id'],
                            'out_of_town' => @$res['out_town_driver_id'],
                            'taxi_hailing' => @$driver_data['taxi_healing'],
                            'flag_city' => @$flag_city,
                            'notification_for' => 'pending',
                            'title' => "New Booking Pending",
                            'description' => "Your Booking Pending",
                            'admin_flag' => "0",
                            ]);
                    }
                }
            }
            return response()->json(['status'  => 'success','booking_status' => "pending",'message' => 'Driver Data Found','data'  => new \stdClass()]);
        }else{
            $message = 'Driver Data Not Found';
            return response()->json(['status'  => 'error','booking_status' => "",'message' => 'Driver Data Not Found','data'  => new \stdClass()]);
        }
    }
}