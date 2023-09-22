<?php

namespace App\Http\Controllers\Api\User;

use App\Jobs\sendNotification;
use App\Models\LinerideBooking;
use App\Models\LinerideUserBooking;
use App\Models\ParcelDriver;
use App\Models\ParcelPackage;
use App\Models\RideSetting;
use App\Models\ShuttleDriver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Conversions;
use App\Models\RatingReviews;
use App\Models\Promocodes;
use Carbon\Carbon;
use Stripe\Charge;
use Stripe\Customer;
use App\Models\CardDetails;
use App\Models\DriverDetails;
use App\Models\Setting;
Use App\Models\Support;
Use App\Models\SupportCategory;
use App\Models\ParcelDetail;
use App\Models\ParcelImage;
use App\Models\Booking;
use App\Models\OutTwonrideBooking;
use Illuminate\Support\Str;
use Event;
use PushNotification;
use App\Models\PopupNotification, App\Models\BookingNotifications;
use App\Models\Wallet, App\Models\WalletHistory, App\Models\TransactionDetail, App\Models\UserNotification, App\Models\ReferWallets, App\Models\VehicleType;
use DateTime;
use App\Jobs\CompletedForTrip;
use App\Jobs\PaymentReceipt;
use DateTimeZone;
class AuthController extends Controller{
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
| API Logout
|--------------------------------------------------------------------------
*/
public function logout(Request $request){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$user = User::find($user->id);
		$user->availability_status = "off";
		$user->device_token = "";
		$user->login_token = "";
		$user->save();
		JWTAuth::invalidate($request->token);
		return response()->json(['status'  => 'success','message' => 'User logged out successfully']);
	}catch (\Exception $e) {
		return response()->json(['status'  => 'error','message' => $e->getMessage()]);
	}
}

/*
|--------------------------------------------------------------------------
| Create User Conversions
|--------------------------------------------------------------------------
|
*/
public function CreateConversions(Request $request){
	$validation_array =[
	'sent_to'       => 'required',
	'message'       => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status'    => 'error','message'   => $validation->errors()->first(),'data'      => (object)[]]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$data['sent_from']=$user->id;
		$data['sent_to']=request('sent_to');
		$data['message']=request('message');
		$data['is_read']="unread";
		$data['trip_id']=request('trip_id');
		$userdata = Conversions::Create($data);
		return response()->json(['status' => 'success','message' => 'You are successfully Conversions!','data' => $data]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Create User Rating Reviews
|--------------------------------------------------------------------------
|
*/
public function CreateUserRatingReviews(Request $request){
	$validation_array =[
	'rating'          => 'required',
	'to_user_id'      => 'required',
	'booking_id'      => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status'    => 'error','message'   => $validation->errors()->first(),'data'      => (object)[]]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$data['from_user_id']    =   $user->id;
		$data['to_user_id']      =   request('to_user_id');
		$data['rating']          =   request('rating');
		$data['booking_id']      =   request('booking_id');
		$data['is_read_user']    =   "read";
		$data['comment']         =   request('comment');
		$userratingreviews = RatingReviews::Create($data);
		return response()->json(['status' => 'success','message' => 'You are successfully User Rating Reviews!','data' => $data]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| User Rating Reviews Status UnRead And Read
|--------------------------------------------------------------------------
|
*/
public function UserRatingReviewsStatus(Request $request){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$data = RatingReviews::find($request->id);
		if($data){
			$data->is_read_user='read';
			$data->save();
			return response()->json(['status' => 'success','message' => 'You are successfully User Rating Reviews!','data' => $data]);
		}
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Driver Listing
|--------------------------------------------------------------------------
|
*/
public function driverlisting(Request $request){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$data = User::where('user_type','driver')->get();
		if(count($data) != 0){
			return response()->json(['status'=>'success','message'=> 'You are successfully Driver Listing!','data'=> $data]);
		}else{
			return response()->json(['status'=> 'error','message'=>'Driver Listing Not Found']);
		}
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Driver Details
|--------------------------------------------------------------------------
|
*/
public function driverdetails(Request $request){
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$data = User::where('user_type','driver')->where('id',$request->driver_id)->get();
		if(count($data) != 0){
			return response()->json(['status'=>'success','message'=> 'You are successfully Driver Details!','data'=> $data]);
		}else{
			return response()->json(['status'=> 'error','message'=>'Driver Details Not Found']);
		}
	}catch(Exception $e){
		return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
	}
}

/*
|--------------------------------------------------------------------------
| Add card details
|--------------------------------------------------------------------------
|
*/
public function add_CardDetails(Request $request){
	$validator = [
	'card_number'          =>'required|integer',
	'card_holder_name'     =>'required',
	'card_expiry_month'    =>'required',
	'card_expiry_year'     =>'required',
	'billing_address'      =>'required',
	'card_type'            =>'required',
	'bank_name'            =>'required',
        //'cvv'                  =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status'    => 'error','message'   => $validation->errors()->first()]);
	}
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$card_details = CardDetails::create([
			'user_id'           => $user->id,
			'card_number'       => request('card_number'),
			'card_holder_name'  => request('card_holder_name'),
			'card_expiry_month' => request('card_expiry_month'),
			'card_expiry_year'  => request('card_expiry_year'),
			'billing_address'   => request('billing_address'),
			'bank_name'         => request('bank_name'),
			'card_type'         => request('card_type'),
			'card_name'         => request('card_type'),
			'cvv'               => request('cvv')
			]);
		return response()->json(['status'=>'success','message'=>'Card Details Added Successfully','data'=>$card_details]);
	}catch(Exception $e){
		return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
	}
}

/*
|--------------------------------------------------------------------------
| edit card details
|--------------------------------------------------------------------------
|
*/
public function edit_CardDetails(Request $request){
	$validator = [
	'card_id'  =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
	}
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$card_details = CardDetails::where('user_id',$user->id)->where('id',request('card_id'))->first();
		if($card_details){
			$card_details->user_id           = $user->id;
			$card_details->card_number       = request('card_number');
			$card_details->card_holder_name  = request('card_holder_name');
			$card_details->card_expiry_month = request('card_expiry_month');
			$card_details->card_expiry_year  = request('card_expiry_year');
			$card_details->billing_address   = request('billing_address');
			$card_details->bank_name         = request('bank_name');
			$card_details->card_type         = request('card_type');
			$card_details->card_name         = request('card_type');
			$card_details->cvv               = request('cvv');
			$card_details->save();
			return response()->json(['status'=>'success','message'=>'Card Details Updated Successfully','data'=>$card_details]);
		}else{
			return response()->json(['status'=>'error','message'=>'Card Details Not Found']);
		}
	}catch(Exception $e){
		return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
	}
}

/*
|--------------------------------------------------------------------------
| delete card details
|--------------------------------------------------------------------------
|
*/
public function delete_CardDetails(Request $request){
	$validator = [
	'card_id'   =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status'    => 'error','message'   => $validation->errors()->first()]);
	}
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$card_details = CardDetails::where('user_id',$user->id)->where('id',request('card_id'))->first();
		if($card_details){
			$card_details->delete();
			return response()->json(['status'=>'success','message'=>'Card Details Deleted Successfully']);
		}else{
			return response()->json(['status'=>'error','message'=>'Card Details Not Found']);
		}
	}catch(Exception $e){
		return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
	}
}

/*
|--------------------------------------------------------------------------
| view card details
|--------------------------------------------------------------------------
|
*/
public function view_CardDetails(Request $request){
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$card_details = CardDetails::where('user_id',$user->id)->get();
		if(count($card_details) != 0){
			return response()->json(['status'=>'success','message'=> 'Card Details Getting Successfully','data'=> $card_details]);
		}else{
			return response()->json(['status'=> 'error','message'=>'Card Details Not Found']);
		}
	}catch(Exception $e){
		return response()->json(['status'=> 'error','message'  => $e->getMessage()]);
	}
}

/*
|--------------------------------------------------------------------------
| default card details
|--------------------------------------------------------------------------
|
*/
public function default_CardDetails(Request $request){
	$validator = [
	'card_id'   =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status'    => 'error','message'   => $validation->errors()->first()]);
	}
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$card_default = CardDetails::where('user_id',$user->id)->where('id',request('card_id'))->first();
		$card_notdefault = CardDetails::where('user_id',$user->id)->where('id','!=',request('card_id'))->pluck('id');
		if($card_default){
			$card_default->default_status = '1';
			$card_default->save();
			CardDetails::whereIn('id', $card_notdefault)->update(['default_status' => '0']);
			return response()->json(['status'=>'success','message'=> 'Card Details Addedd in Default','data'=> $card_default]);
		}else{
			return response()->json(['status'=> 'error','message'=>'Card Details Not Found']);
		}
	}catch(Exception $e){
		return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
	}
}

/*
|--------------------------------------------------------------------------
| First Driver Get Nearest Driver
|--------------------------------------------------------------------------
|
*/
public function get_nearest_driver(Request $request){
	$validator = [
	'service_id'            =>'required',
	'lineride_distance'     =>'integer',
	'vehicle_id'            =>'required',
	'booking_id'            =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$distance_setting = Setting::where('code','search_driver_area')->first();
		$user_booking = Booking::with(['driver'])->where('id',$request->get('booking_id'))->first();
		Booking::where('id', $request->booking_id)->update([
			'vehicle_id'     => $request->vehicle_id,
			]);
		$vehicle_id = $request->vehicle_id;
		$service = $request->service_id;
		if($request->latitude){
			$latitude         = $request->latitude;
		}else{
			$latitude         = $user->latitude;
		}
		if($request->longitude){
			$longitude         = $request->longitude;
		}else{
			$longitude         = $user->longitude;
		}

		if($request->get('lineride_distance') !== null){
			$distance = $request->get('lineride_distance');
		}else if($distance_setting !== null){
			$distance = $distance_setting->value;
		} else {
			$distance = 1;
		}
		$getdriversid = '';
		$distance_In_value_setting = Setting::where('code','distance_in')->first();
		$distance_In_setting = $distance_In_value_setting->value;

		if($request->service_id == '2'){
			if($distance_In_setting === 'km'){
				$user_check_booking = Booking::where('id',$request->get('booking_id'))->first();
				$pick_up_location_user = $user_check_booking->pick_up_location;
				$drop_location_user = $user_check_booking->drop_location;
				$start_l = $user_check_booking->start_latitude;
				$start_o = $user_check_booking->start_longitude;
				$droup_l = $user_check_booking->drop_latitude;
				$droup_o = $user_check_booking->drop_longitude;
				$dateing = $user_check_booking->booking_date;

				$booking_id = $user_check_booking->id;
				$booking_seat = $user_check_booking->seats;
				$resData = $this->getouttowndriverkmresult($start_l, $start_o, $droup_l, $droup_o, $dateing,$service, $getdriversid, $vehicle_id, $pick_up_location_user, $drop_location_user, $booking_seat, $booking_id);
			}elseif($distance_In_setting === 'miles'){
				$user_check_booking = Booking::where('id',$request->get('booking_id'))->first();
				$pick_up_location_user = $user_check_booking->pick_up_location;
				$drop_location_user = $user_check_booking->drop_location;
				$start_l = $user_check_booking->start_latitude;
				$start_o = $user_check_booking->start_longitude;
				$droup_l = $user_check_booking->drop_latitude;
				$droup_o = $user_check_booking->drop_longitude;
				$dateing = $user_check_booking->booking_date;

				$booking_seat = $user_check_booking->seats;
				$booking_id = $user_check_booking->id;
				$resData = $this->getouttowndriverkmresult($start_l, $start_o, $droup_l, $droup_o, $dateing,$service, $getdriversid, $vehicle_id, $pick_up_location_user, $drop_location_user, $booking_seat, $booking_id);
			}else{
				$user_booking->driver = new \stdClass();
				$message = 'Driver Data Not Found';
				return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver,]);
			}
            // if(!empty($user_booking->driver)){
            //     return response()->json(['status'  => 'success','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Found','data'    => $user_booking->driver]);
            // }else{
            //     $this->getOutTownDriverList($latitude, $longitude, $request->booking_id, $distance_In_setting, $service, $getdriversid, $vehicle_id);
            // }
		}else{
			if($distance_In_setting === 'km'){
				$user_check_booking = Booking::where('id',$request->get('booking_id'))->first();
				$droup_latitude = $user_check_booking->drop_latitude;
				$droup_longitude = $user_check_booking->drop_longitude;
				$resData = $this->getdriverkmresult($latitude, $longitude, $distance, $service, $getdriversid, $vehicle_id, $droup_latitude, $droup_longitude);
			}elseif($distance_In_setting === 'miles'){
				$user_check_booking = Booking::where('id',$request->get('booking_id'))->first();
				$droup_latitude = $user_check_booking->drop_latitude;
				$droup_longitude = $user_check_booking->drop_longitude;

				$resData = $this->getdrivermlresult($latitude, $longitude, $distance, $service, $getdriversid, $vehicle_id, $droup_latitude, $droup_longitude);
			}else{
				$user_booking->driver = new \stdClass();
				$message = 'Driver Data Not Found';
				return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver]);
			}
		}
		if(!empty($resData)){
			$initial=0;

			$booking_detail = Booking::where('id',$request->booking_id)->first();
			$user_detail = User::where('id',$user->id)->first();
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
			foreach ($resData as $res) {
				$initial=$initial++;
				$checkIfNotifiactionAlreadySent = BookingNotifications::where('booking_id',$request->get('booking_id'))->where('driver_id',$res['driver_id'])->where('is_send','1')->first();
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

					if($request->service_id == "2"){
						$flag_city = "out_of_town";
						$end_time = '- '.$booking_detail->booking_end_time;
					}
                    // $taxi_hailing = @$request->taxi_hailing;
                    // $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
					$distance_arr = json_decode($distance_data);
					$elements = $distance_arr->rows[0]->elements;
					$duration = $elements[0]->duration->text;
					$distance = $elements[0]->distance->text;

					$start_time = $booking_detail->booking_start_time;
					if($request->service_id == "1"){
						if($booking_detail->booking_type == 'schedule'){
							$end_time = '- '.$booking_detail->booking_end_time;
						}else{
							$end_time = '';
						}
					}
					/*$booking_dates = explode('-',$booking_detail->booking_date);
					$mm_dd_yyyy = $booking_dates[0] . '/' . $booking_dates[1] . '/' . $booking_dates[2];
					$date_format = date_create($mm_dd_yyyy);*/
					$date_format = date_create($booking_detail->booking_date);
					$dateing = date_format($date_format,"m/d/Y").' '.@$start_time.' '.@$end_time;

					$driver_data['elements'] = $elements;
					$driver_data['duration'] = $duration;
					$driver_data['distance'] = $distance;
					$driver_data['user_name'] = @$user_name;
                    // $driver_data['availability_status'] = @$user_detail->availability_status;
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
                    // $driver_data['out_town_driver_id']        =  @$res['out_town_driver_id'];
                //     var_dump($res['availability_status']);
                // exit;
					if($checkIfNotifiactionAlreadySent === null){
						$driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver,$driver_data));
						BookingNotifications::create([
							'driver_id'  => $res['driver_id'],
							'booking_id' => $request->get('booking_id'),
							'is_send'    => '1',
							]);
						UserNotification::create([
							'sent_from_user' => $user->id,
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
			if(empty($user_booking->driver)){
				$data = new \stdClass();
			}else{
				$data = $user_booking->driver;
				if(!empty($data->first_name)){
					$user_name = @$user_detail->last_name.' '.@$user_detail->first_name;
				} else {
					$user_name = @$user_detail->company_name;
				}
				$pickup_total_km = $this->pickupdistance($user->latitude, $user->longitude, $data->latitude, $data->longitude);
				$data['pickup_total_km'] = number_format((float)$pickup_total_km, 2, '.', '');
				$booking_detail = Booking::where('id',$request->booking_id)->first();
				$data['booking_date'] = $booking_detail->booking_date;
                //User Address
				$lat= $user->latitude;
				$lng= $user->longitude;
				$user_address = $this->getaddress($lat,$lng);
				if($user_address){
					$origin_addresses = $user_address;
				}
                //Driver Address
				$lat= $data->latitude;
				$lng= $data->longitude;
				$driver_address = $this->getaddress($lat,$lng);
				if($driver_address){
					$destination_addresses = $driver_address;
				}
				$destination_addresses = $destination_addresses;
				$origin_addresses = $origin_addresses;
				$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
				$distance_arr = json_decode($distance_data);
				$elements = $distance_arr->rows[0]->elements;
				$distance = $elements[0]->distance->text;
				$duration = $elements[0]->duration->text;

				$avgrating = RatingReviews::where('from_user_id',$data)->orWhere('to_user_id',$data)->avg('rating');
				if(empty($avgrating)){
					$avgrating = 0;
				}

				$data['driver_review'] = $avgrating;


				$data['arrival_time'] = $duration;
				$data['booking_date'] = $user_booking->booking_date .' '. $user_booking->booking_start_time;
				$data['booking_start_time'] = $user_booking->booking_start_time;
				$data['booking_end_time'] = @$user_booking->booking_end_time;
			}
			return response()->json(['status'  => 'success','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Found','data'    => $data]);
		}else{
			$user_booking->driver = new \stdClass();
			$message = 'Driver Data Not Found';
			return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver,]);
		}
	}catch (Exception $e) {
		$user_booking->driver = new \stdClass();
		$message = 'Driver Data Not Found';
		return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found mkmkmk','data'    => $user_booking->driver,]);
	}
}
// public function get_nearest_driver(Request $request){
//     $validator = [
//         'service_id'            =>'required',
//         'lineride_distance'     =>'integer',
//         'vehicle_id'            =>'required',
//         'booking_id'            =>'required',
//     ];
//     $validation = Validator::make($request->all(),$validator);
//     if($validation->fails()){
//         return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
//     }
//     try {
//         $user = JWTAuth::parseToken()->authenticate();
//         if(!$user){
//             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
//         }
//         $distance_setting = Setting::where('code','search_driver_area')->first();
//         $user_booking = Booking::with(['driver'])->where('id',$request->get('booking_id'))->first();
//         Booking::where('id', $request->booking_id)->update([
//             'vehicle_id'     => $request->vehicle_id,
//         ]);
//         $vehicle_id = $request->vehicle_id;
//         $service = $request->service_id;
//         if($request->latitude){
//             $latitude         = $request->latitude;
//         }else{
//             $latitude         = $user->latitude;
//         }
//         if($request->longitude){
//             $longitude         = $request->longitude;
//         }else{
//             $longitude         = $user->longitude;
//         }
//         if($request->get('lineride_distance') !== null){
//             $distance = $request->get('lineride_distance');
//         }else if($distance_setting !== null){
//             $distance = $distance_setting->value;
//         } else {
//             $distance = 1;
//         }
//         $getdriversid = '';
//         $distance_In_value_setting = Setting::where('code','distance_in')->first();
//         $distance_In_setting = $distance_In_value_setting->value;
//         if($distance_In_setting === 'km'){
//             $resData = $this->getdriverkmresult($latitude, $longitude, $distance, $service, $getdriversid, $vehicle_id);

//         }elseif($distance_In_setting === 'miles'){
//             $resData = $this->getdrivermlresult($latitude, $longitude, $distance, $service, $getdriversid, $vehicle_id);
//         }else{
//             $user_booking->driver = new \stdClass();
//             $message = 'Driver Data Not Found';
//             return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver,]);
//         }

//         if(!empty($resData)){
//             $initial=0;

//             $booking_detail = Booking::where('id',$request->booking_id)->first();
//             $user_detail = User::where('id',$user->id)->first();
//             if(!empty($user_detail)){
//                 $user_name = @$user_detail->last_name.' '.@$user_detail->first_name;
//             } else {
//                 $user_name = @$user_detail->company_name;
//             }
//             $ride_setting_detail = RideSetting::where('id',$booking_detail->ride_setting_id)->first();
//             $destination_addresses = $booking_detail->drop_location;
//             $origin_addresses = $booking_detail->pick_up_location;
//             if(!empty($booking_detail)){
//                 $distance = $booking_detail->total_km .' km';
//             } else {
//                 $distance = 0;
//             }
//             foreach ($resData as $res) {
//                 $initial=$initial++;
//                 $checkIfNotifiactionAlreadySent = BookingNotifications::where('booking_id',$request->get('booking_id'))->where('driver_id',$res['driver_id'])->where('is_send','1')->first();
//                 if($res !== null){
//                     $driver = User::where('id',$res['driver_id'])->first();
//                     $vehicle = VehicleType::where('id',$driver->vehicle_id)->first();

//                     $avgrating = RatingReviews::where('from_user_id',$res['driver_id'])->orWhere('to_user_id',$res['driver_id'])->avg('rating');
//                     if(empty($avgrating)){
//                         $avgrating = 0;
//                     }

//                     $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
//                     $distance_arr = json_decode($distance_data);
//                     $elements = $distance_arr->rows[0]->elements;
//                     $duration = $elements[0]->duration->text;
//                     $driver_data['elements'] = $elements;
//                     $driver_data['duration'] = $duration;
//                     $driver_data['distance'] = $distance;
//                     $driver_data['user_name'] = @$user_name;
//                     $driver_data['avatar'] = $user_detail->avatar;
//                     $driver_data['ride_setting'] = $ride_setting_detail->name;
//                     $driver_data['booking_id'] = $booking_detail->id;
//                     $driver_data['booking_type'] = $booking_detail->booking_type;
//                     $driver_data['pick_up_location'] = $booking_detail->pick_up_location;
//                     $driver_data['drop_location'] = $booking_detail->drop_location;
//                     $driver_data['start_latitude'] = $booking_detail->start_latitude;
//                     $driver_data['start_longitude'] = $booking_detail->start_longitude;
//                     $driver_data['drop_latitude'] = $booking_detail->drop_latitude;
//                     $driver_data['drop_longitude'] = $booking_detail->drop_longitude;
//                     $driver_data['car_name'] = $vehicle->name;
//                     $driver_data['date_time'] = @$booking_detail->booking_date .' '. @$booking_detail->booking_start_time;
//                     $driver_data['driver_review'] = $avgrating;
//                     $driver_data['message'] =  'New Booking Arrived from '.@$user_detail->last_name.' '.@$user_detail->first_name;
//                     $driver_data['type'] = 'new_booking';
//                     $driver_data['driver_id'] = $res['driver_id'];
//                     $driver_data['title']        =  'RueRun';
//                     $driver_data['sound']        = 'default';
//                     if($checkIfNotifiactionAlreadySent === null){
//                         $driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver,$driver_data));
//                         BookingNotifications::create([
//                             'driver_id'  => $res['driver_id'],
//                             'booking_id' => $request->get('booking_id'),
//                             'is_send'    => '1',
//                         ]);
//                         UserNotification::create([
//                             'sent_from_user' => $user->id,
//                             'sent_to_user' => $res['driver_id'],
//                             'booking_id' => @$driver_data['booking_id'],
//                             'notification_for' => 'pending',
//                             'title' => "New Booking Pending",
//                             'description' => "Your Booking Pending",
//                             'admin_flag' => "0",
//                         ]);
//                     }
//                 }
//             }
//             if(!empty($user_booking->driver)){
//                 $data = $user_booking->driver;
//                 if(!empty($data->first_name)){
//                     $user_name = @$user_detail->last_name.' '.@$user_detail->first_name;
//                 } else {
//                     $user_name = @$user_detail->company_name;
//                 }
//                 $pickup_total_km = $this->pickupdistance($user->latitude, $user->longitude, $data->latitude, $data->longitude);
//                 $data['pickup_total_km'] = number_format((float)$pickup_total_km, 2, '.', '');
//                 $booking_detail = Booking::where('id',$request->booking_id)->first();
//                 $data['booking_date'] = $booking_detail->booking_date;
//                 //User Address
//                 $lat= $user->latitude;
//                 $lng= $user->longitude;
//                 $user_address = $this->getaddress($lat,$lng);
//                 if($user_address){
//                     $origin_addresses = $user_address;
//                 }
//                 //Driver Address
//                 $lat= $data->latitude;
//                 $lng= $data->longitude;
//                 $driver_address = $this->getaddress($lat,$lng);
//                 if($driver_address){
//                     $destination_addresses = $driver_address;
//                 }
//                 $destination_addresses = $destination_addresses;
//                 $origin_addresses = $origin_addresses;
//                 $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
//                 $distance_arr = json_decode($distance_data);
//                 $elements = $distance_arr->rows[0]->elements;
//                 $distance = $elements[0]->distance->text;
//                 $duration = $elements[0]->duration->text;

//                 $avgrating = RatingReviews::where('from_user_id',$data)->orWhere('to_user_id',$data)->avg('rating');
//                    if(empty($avgrating)){
//                         $avgrating = 0;
//                     }

//                 $data['driver_review'] = $avgrating;


//                 $data['arrival_time'] = $duration;
//                 $data['booking_date'] = $user_booking->booking_date .' '. $user_booking->booking_start_time;
//             }else{
//                 $data = new \stdClass();
//             }
//             return response()->json(['status'  => 'success','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Found','data'    => $data,]);
//         }else{
//             $user_booking->driver = new \stdClass();
//             $message = 'Driver Data Not Found';
//             return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver,]);
//         }
//     }catch (Exception $e) {
//         $user_booking->driver = new \stdClass();
//         $message = 'Driver Data Not Found';
//         return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver,]);
//     }
// }

function getaddress($lat,$lng){
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.($lat).','.($lng).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo';
	$json = @file_get_contents($url);
	$data=json_decode($json);
	$status = $data->status;
	if($status=="OK")
		return $data->results[0]->formatted_address;
	else
		return false;
}

public function pickupdistance($lat1, $lon1, $lat2, $lon2) {
	$units = Setting::where('code','distance_in')->first();
	if($units === 'km'){
		$unit = "K";
	}else{
		$unit = "M";
	}
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
| Get Driver KM
|--------------------------------------------------------------------------
|
*/
public function getdriverkmresult($latitude, $longitude, $distance, $service, $getdriversid, $vehicle_id, $droup_latitude, $droup_longitude){
	// dd($latitude, $longitude);
	$resultData = User::whereHas('driver_details_settings', function($q) use($service){
		$q->where('ride_type','=', $service);
	})->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved','availability_status'=>'on'])->where('vehicle_id',$vehicle_id)->get();
	$driverDetails=[];
	$driverDetailsData1 = [];
    // dd($resultData);
	foreach ($resultData as $key => $value) {
		$getdriversidecheckData = Booking::where('driver_id', $value->id)->whereIn('trip_status',['on_going','driver_arrived'])->first();
		if(empty($getdriversidecheckData)){
			$driverDetails['driver_id'] = $value->id;
			$driverDetails['email'] = $value->email;
			$driverDetails['availability_status'] = $value->availability_status;
			if(!empty($value->latitude))
			{
				$driver_latitude = $value->latitude;
			}else{
				$driver_latitude = $value->add_latitude;
			}
			if(!empty($value->longitude)){
				$driver_longitude = $value->longitude;
			} else {
				$driver_longitude = $value->add_longitude;
			}
			$driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$latitude,$longitude,'K');
			$driverDetails_droup['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$droup_latitude, $droup_longitude,'K');
			if($driverDetails['distance'] <= $distance){
				if($driverDetails_droup['distance'] <= $distance){
					$driverDetailsData1[] = $driverDetails;    
				}
			}
		}
	}
	return $driverDetailsData1;
}

/*
|--------------------------------------------------------------------------
| Get Driver ML
|--------------------------------------------------------------------------
|
*/
public function getdrivermlresult($latitude, $longitude, $distance, $service, $getdriversid, $vehicle_id, $droup_latitude, $droup_longitude){
	$resultData = User::whereHas('driver_details_settings', function($q) use($service){
		$q->where('ride_type','=', $service);
	})->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved','availability_status'=>'on'])->where('vehicle_id',$vehicle_id)->get();
	$driverDetails=[];
	$driverDetailsData1 = [];
	foreach ($resultData as $key => $value) {
		$getdriversidecheckData = Booking::where('driver_id', $value->id)->whereIn('trip_status',['on_going','driver_arrived'])->first();
		if(empty($getdriversidecheckData)){
			file_put_contents(public_path().'/webservice_logs/'.date("d-m-Y").'_notificationss.log', "\n\n".date("d-m-Y H:i:s").":".json_encode(['data'=>$value->id])."\n", FILE_APPEND);
			$driverDetails['driver_id'] = $value->id;
			$driverDetails['email'] = $value->email;
			$driverDetails['availability_status'] = $value->availability_status;
			if(!empty($value->latitude))
			{
				$driver_latitude = $value->latitude;
			}else{
				$driver_latitude = $value->add_latitude;
			}
			if(!empty($value->longitude)){
				$driver_longitude = $value->longitude;
			} else {
				$driver_longitude = $value->add_longitude;
			}
			$driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$latitude,$longitude,'M');
			$driverDetails_droup['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$droup_latitude, $droup_longitude,'M');
			if($driverDetails['distance'] <= $distance){
				if($driverDetails_droup['distance'] <= $distance){
					$driverDetailsData1[] = $driverDetails;    
				}
			}
		}
	}
	return $driverDetailsData1;
}

public function oldsss_getdrivermlresult($latitude, $longitude, $distance, $service, $getdriversid, $vehicle_id, $droup_latitude, $droup_longitude){
	$resultData = User::whereHas('driver_details_settings', function($q) use($service){
		$q->where('ride_type','=', $service);
	})->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved'])->where('latitude','!=', '')->where('longitude','!=', '')->where('vehicle_id',$vehicle_id)->get();
	$driverDetails=[];
	$driverDetailsData = [];
	foreach ($resultData as $key => $value) {
		$getdriversidecheckData = Booking::where('driver_id', $value->id)->whereIn('trip_status',['on_going','driver_arrived'])->first();
		if(empty($getdriversidecheckData)){
			$driverDetails['driver_id'] = $value->id;
			$driverDetails['email'] = $value->email;
			$driverDetails['availability_status'] = $value->availability_status;
			if(!empty($value->latitude))
			{
				$driver_latitude = $value->latitude;
			}else{
				$driver_latitude = $value->add_latitude;
			}
			if(!empty($value->longitude)){
				$driver_longitude = $value->longitude;
			} else {
				$driver_longitude = $value->add_longitude;
			}
			$driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$latitude,$longitude,'M');
			$driverDetails_droup['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$droup_latitude, $droup_longitude,'M');
			if($driverDetails['distance'] <= $distance){
				if($driverDetails_droup['distance'] <= $distance){
					$driverDetailsData[] = $driverDetails;
				}
			}
		}
	}
    // dd($driverDetailsData);
	return $driverDetailsData;
}

/*
|--------------------------------------------------------------------------
| Get Nearest Driver
|--------------------------------------------------------------------------
|
*/
public function distanceByLatLong($lat1, $lon1, $lat2, $lon2, $unit) {
	$theta = $lon1-$lon2;
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
| User Create Supports
|--------------------------------------------------------------------------
|
*/
public function CreateUserSupports(Request $request){
	$validation_array =[
	'email'           => 'required',
	'support_cat_id'  => 'required',
	'description'     => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status'=> 'error','message' => $validation->errors()->first(),'data'      => (object)[]]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$data['user_id']                =  $user->id;
		$data['email']                  =  request('email');
		$data['support_categories_id']  =  request('support_cat_id');
		$data['description']            =  request('description');
		$userratingreviews = Support::Create($data);
		return response()->json(['status' => 'success','message' => 'You are successfully submit Query!','data' => $data]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Create New User  Parcel Booking
|--------------------------------------------------------------------------
|
*/
public function parcelBooking(Request $request){
	if($request->booking_type == 'immediate'){
		$validation_array =[
		'pick_up_location'    => 'required',
		'drop_location'       => 'required',
		'start_latitude'      => 'required',
		'start_longitude'     => 'required',
		'drop_latitude'       => 'required',
		'drop_longitude'      => 'required',
		'parcel_image'        => 'required',
		'recepient_name'      => 'required',
		'contact_number'      => 'required',
		'booking_type'        => 'required',
		'package_type'        => 'required',
		'parcel_length'       => 'required',
		'parcel_deep'         => 'required',
		'parcel_height'       => 'required',
		'parcel_weight'       => 'required',
		];
	}else{
		$validation_array =[
		'pick_up_location'    => 'required',
		'drop_location'       => 'required',
		'start_latitude'      => 'required',
		'start_longitude'     => 'required',
		'drop_latitude'       => 'required',
		'drop_longitude'      => 'required',
		'parcel_image'        => 'required',
		'recepient_name'      => 'required',
		'contact_number'      => 'required',
		'booking_type'        => 'required',
		'package_type'        => 'required',
		'parcel_length'       => 'required',
		'parcel_deep'        => 'required',
		'parcel_height'       => 'required',
		'parcel_weight'       => 'required',
            // 'booking_date'        => 'required',
		];
	}
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
	}
	try{
		if($request->promo_id){
			$promo_id = $request->promo_id;
		}else{
			$promo_id = '';
		}
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		if($request->description){
			$description = $request->description;
		}else{
			$description = '';
		}
		$origin_addresses = $request->pick_up_location;
		$destination_addresses = $request->drop_location;
		$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
		$distance_arr = json_decode($distance_data);
		$elements = $distance_arr->rows[0]->elements;
		$total_km = $elements[0]->distance->text;
		$duration = $elements[0]->duration->text;
		if($request->booking_date){
			$booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
		}else{
			$booking_date = Carbon::now()->format('Y-m-d');
		}

            // check existing booking  for location  and time interval
		$checkBooking = ParcelDetail::where('user_id', @$user->id)
		->where('pick_up_location', @$request->pick_up_location)
		->where('drop_location', @$request->drop_location)
		->where('booking_start_time', Carbon::now()->format('H:i a'))
                //->where('booking_end_time', @$request->booking_end_time)
		->where('booking_date', @$booking_date)
		->whereNotIn('parcel_status', ["cancelled","pending","completed"])
		->first();
		if(!empty($checkBooking)){
			return response()->json(['status' => 'error','message' => 'You have already booked for this Locations and Time Intervals']);
		}


		$parcelBooking = ParcelDetail::create([
			'booking_type'             => $request->booking_type,
			'pick_up_location'         => $request->pick_up_location,
			'drop_location'            => $request->drop_location,
			'start_latitude'           => $request->start_latitude,
			'start_longitude'          => $request->start_longitude,
			'drop_latitude'            => $request->drop_latitude,
			'drop_longitude'           => $request->drop_longitude,
			'otp'                      => mt_rand(1000, 9999),
			'booking_date'             => @$booking_date,
			'description'              => $description,
			'ride_setting_id'          => $request->ride_setting_id,
			'recepient_name'           => $request->recepient_name,
			'contact_number'           => $request->contact_number,
			'total_distance'           => $total_km,
			'user_id'                  => $user->id,
			'booking_start_time'       => Carbon::now()->format('H:i a'),
			]);
		if(!empty($parcelBooking)){
			$parcelDetail = ParcelDetail::find($parcelBooking->id);
			if($request->hasFile('parcel_image')){
				foreach($request->file('parcel_image') as $image){
					$extension = $image->getClientOriginalExtension();
					$filename = Str::random(10).'.'.$extension;
					Storage::disk('public')->putFileAs('parcel_image', $image,$filename);
					$input['image'] = $filename;
					$input['parcel_id']=$parcelBooking->id;
					ParcelImage::create([
						'image'  => $filename,
						'parcel_id'  => $parcelBooking->id,
						'image_name'  => $image->getClientOriginalName(),
						]);
				}
			}
			if(isset($request->parcel_length[0]) ){
				$parcellengths = explode(',',$request->parcel_length[0]);
				$parceldeeps = explode(',',$request->parcel_deep[0]);
				$parcelheights = explode(',',$request->parcel_height[0]);
				$parcelweights = explode(',',$request->parcel_weight[0]);
				$total_amount = explode(',',$request->total_amount[0]);
				$package_type = explode(',',$request->package_type[0]);
				$totalweight=$totalamount=array();
				foreach($parcellengths as $key => $value){
					ParcelPackage::create([
						'parcelbooking_id'  =>  $parcelBooking->id,
						'parcel_length'     =>  $value,
						'parcel_deep'       =>  $parceldeeps[$key],
						'parcel_height'     =>  $parcelheights[$key],
						'parcel_weight'     =>  $parcelweights[$key],
						'total_amount'      =>  $total_amount[$key],
						'package_type'      =>  $package_type[$key],
						]);
					array_push($totalweight, $parcelweights[$key]);
					array_push($totalamount, $total_amount[$key]);
				}
				$data['weight'] = array_sum($totalweight);
				$data['total_amount'] = array_sum($totalamount);
				$data['distance'] = $total_km;
				ParcelDetail::where('id', $parcelBooking->id)->update([ 'total_amount'=> array_sum($totalamount) ]);
			}
			$text = 'Your OTP is: '.$parcelDetail->otp;
			$emailcontent = array (
				'text'      => $text,
				'title'     => 'Thanks for Use Ruerun for Parcel Booking, Please use Below OTP for Parcel Booking Verification. You will need to complete booking Process enter OTP.',
				'userName'  => $user->first_name
				);
			$details['email'] = $user->email;
			$details['username'] = $user->first_name;
			$details['subject'] = 'Parcel Booked, OTP Confirmation';
			$data['parcelDetail'] = ParcelDetail::with(['parcel_images','parcel_packages','user'])->where('id', $parcelBooking->id)->first();
			if($data['weight'] <= 15){
				$parcel_package_type = "Light Weight";
			}else{
				$parcel_package_type = "Heavy Weight";
			}
			$parcel_package_typeing = ParcelDetail::where('id', $parcelBooking->id)->update(['parcel_package_type'=> $parcel_package_type]);

			dispatch(new sendNotification($details,$emailcontent));
			$data_parcel['parcel_id'] = $parcelBooking->id;
			$data_parcel['parcel_type'] = $parcel_package_type;
			$data_parcel['weight'] = $data['weight'];
			$data_parcel['total_amount'] = $data['total_amount'];
			$data_parcel['distance'] = $data['distance'];
			return response()->json(['status' => 'success','message' => 'Schedule Parcel Booked Successfully','data' => $data_parcel]);
		}else{
			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Get Parcel Booking Estimation
|--------------------------------------------------------------------------
|
*/
public function getParcelEstimate(Request $request){
	$validation_array =[
	'parcel_length'         => 'required',
	'parcel_deep'           => 'required',
	'parcel_height'         => 'required',
	'parcel_weight'         => 'required',
	'package_type'          => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
	}
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$parcel_weight_charge='';
		if($request->package_type === 'Light Package'){
			$parcel_weight_charge = Setting::where('code','light_weight_charge')->select('value')->first();
		}else if($request->package_type === 'Heavy Package'){
			$parcel_weight_charge = Setting::where('code','heavy_weight_charge')->select('value')->first();
		}else{
			$parcel_weight_charge = Setting::where('code','light_weight_charge')->select('value')->first();
		}
		$length_setting = Setting::where('code','parcel_length_charge')->first();
		$deep_setting = Setting::where('code','parcel_deep_charge')->first();
		$height_setting = Setting::where('code','parcel_height_charge')->first();
		$weight_setting = Setting::where('code','parcel_weight')->first();
		$total_length_price = (float)$length_setting->value * (float)$request->parcel_length;
		$total_deep_price = (float)$deep_setting->value * (float)$request->parcel_deep;
		$total_height_price = (float)$height_setting->value * (float)$request->parcel_height;
		$temp_total = $total_length_price + $total_deep_price  + $total_height_price;
		$total_parcel_charge  = (float)$parcel_weight_charge->value + (float)$temp_total;
		return response()->json(['status' => 'success','message' => 'Parcel Package Calculation','estimation_amt' => round($total_parcel_charge)]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Parcel Booking Payment
|--------------------------------------------------------------------------
|
*/
public function parcelPayment(Request $request){
	$validation_array =[
	'parcel_id'           => 'required',
	'payment_type'        => 'required',
	'transaction_id'      => 'nullable',
	'card_id'             => 'required',
        //'total_amount'        => 'required',
//        'driver_id'           => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
	}
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$card_details = CardDetails::where('id',$request->card_id)->first();
		if(empty($card_details)){
			return response()->json(['status'=>'error','message' => 'Card Details Not Found'],200);
		}
		$parcel_booking = ParcelDetail::find($request->parcel_id);

		if($request->tip_amount){
			$total_amount = (float)$request->total_amount + (float)$request->tip_amount;
			$tip = $request->tip_amount;
		}else{
			$total_amount = (float)$request->total_amount;
			$tip = 0;
		}
		if($request->promo_id){
			$promo_id = $request->promo_id;
		}else{
			$promo_id = $parcel_booking->promo_id;
		}
		ParcelDetail::where('id', $request->parcel_id)->update([
            //'user_id'          => $user->id,
            //'driver_id'        => $request->driver_id,
            //'promo_id'         => $promo_id,
            //'total_amount'     => $total_amount,
			'payment_type'     => $request->payment_type,
			'transaction_id'   => $request->transaction_id,
			'card_id'          => $request->card_id,
			'parcel_status'    => 'completed',
			'payment_status'   => 'completed',
			'tip_amount'       => $tip,
			]);

		TransactionDetail::create([
			'amount'            => (int)$parcel_booking->total_amount ,
			'user_id'           => (int)$parcel_booking->user_id,
			'transaction_id'    => $request->transaction_id,
			'parcel_id'         => (int)$parcel_booking->id,
			'promo_id'          => (int)$parcel_booking->promo_id,
			'status'            => 'complete',
			]);

		return response()->json(['status' => 'success','message' => 'Parcel Payment Successfully','data' => ParcelDetail::find($request->parcel_id)]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| get distance between two lat, long (used in completeUserTripBooking() )
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
	if ($unit === "KM") {
		$miles = $miles * 1.609344;
		return round($miles);
	}else {
		return round($miles);
	}
}


/*
|--------------------------------------------------------------------------
| Out Of Town Ride Booking
|--------------------------------------------------------------------------
|
*/
// public function outoftownBooking(Request $request){
//     $validation_array =[
//         'pick_up_location'    => 'required',
//         // 'drop_location'       => 'required',
//         'start_latitude'      => 'required',
//         'start_longitude'     => 'required',
//         // 'drop_latitude'       => 'required',
//         // 'drop_longitude'      => 'required',
//         // 'driver_id'           => 'required',
//         // 'vehicle_id'          => 'required',
//         'booking_date'        => 'required',
//         'booking_start_time'  => 'required',
//         // 'booking_end_time'        => 'required',
//         // 'mailes'              => 'required',
//         // 'seat_selected'       => 'required',
//         // 'seat_available'      => 'required',
//     ];
//     $validation = Validator::make($request->all(),$validation_array);
//     if($validation->fails()){
//         return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
//     }
//     try{
//         $user = JWTAuth::parseToken()->authenticate();
//         if(!$user){
//             return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
//         }
//         $checklineride = OutTwonrideBooking::where('driver_id',$user->id)->where('booking_date','>=',date('Y-m-d'))->count();
//         if($checklineride > 3){
//             return response()->json(['status' => 'error','message' => 'Not more than 4 Out Twon ride can create for a day']);
//         }
//         if($request->promo_id){
//             $promo_id = $request->promo_id;
//         }else{
//             $promo_id = '';
//         }
//         $getservicetype = RideSetting::where('code','out_of_town')->first();
//         $total_distance = $this->distance($request->start_latitude, $request->start_longitude, $request->drop_latitude, $request->drop_longitude);
//         $outoftownBooking = OutTwonrideBooking::create([
//             'booking_type'             => "out_of_town",
//             'pick_up_location'         => $request->pick_up_location,
//             'drop_location'            => @$request->drop_location,
//             'start_latitude'           => $request->start_latitude,
//             'start_longitude'          => $request->start_longitude,
//             'drop_latitude'            => @$request->drop_latitude,
//             'drop_longitude'           => @$request->drop_longitude,
//             // 'otp'                      => mt_rand(1000, 9999),
//             'booking_date'             => $request->booking_date,
//             'booking_start_time'       => $request->booking_start_time,
//             'booking_end_time'         => $request->booking_end_time,
//             'ride_setting_id'          => $getservicetype->id,
//             'driver_id'                => $user->id,
//             // 'user_id'                  => $user->id,
//             'total_distance'           => $total_distance,
//             // 'payment_type'             => $request->payment_type,
//             'total_amount'             => '100',
//             'mailes'                   => @$request->mailes,
//             // 'seat_available'           => $request->seat_available,
//             // 'seat_booked'              => $request->seat_selected,
//         ]);
//         if(!empty($outoftownBooking)){
//             return response()->json(['status' => 'success','message' => 'Out Of Town Booking Done Successfully','data' => $outoftownBooking]);
//         } else {
//             return response()->json(['status' => 'error','message' => 'Something went Wrong']);
//         }
//     } catch (Exception $e) {
//         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
//     }
// }

public function outoftownBooking(Request $request){
	$validation_array =[
	'pick_up_location'    => 'required',
        // 'drop_location'       => 'required',
	'start_latitude'      => 'required',
	'start_longitude'     => 'required',
        // 'drop_latitude'       => 'required',
        // 'drop_longitude'      => 'required',
        // 'driver_id'           => 'required',
        // 'vehicle_id'          => 'required',
	'booking_date'        => 'required',
	'booking_start_time'  => 'required',
        // 'booking_end_time'    => 'required',
        // 'mailes'              => 'required',
        // 'seat_selected'       => 'required',
        // 'seat_available'      => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
	}
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
        // $booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
        // $jk_booking_date = $booking_date." ".$request->booking_start_time;
        // $jk1_booking_date = $booking_date." ".$request->booking_end_time;

        // date_default_timezone_set('Asia/Kolkata');
        // date_default_timezone_set('America/New_York');
        // $objDateTime = new DateTime($jk_booking_date);
        // // $objDateTimeZone = new DateTimeZone('Europe/Paris');
        // $objDateTimeZone = new DateTimeZone('America/New_York');
        // $objDateTime->setTimeZone($objDateTimeZone);
        // $timezone_array = $objDateTime->format('Y-m-d');


        // $date=date_create($jk_booking_date,timezone_open("Asia/Kolkata"));
        // date_timezone_set($date,timezone_open("Europe/Paris"));
        // $date=date_create($jk_booking_date,timezone_open("America/New_York"));
        // date_timezone_set($date,timezone_open("America/New_York"));
        // $booking_start_time =  date_format($date,"H:i A");

        // $date1=date_create($jk1_booking_date,timezone_open("Asia/Kolkata"));
        // date_timezone_set($date1,timezone_open("Europe/Paris"));
        // $date1=date_create($jk1_booking_date,timezone_open("America/New_York"));
        // date_timezone_set($date1,timezone_open("America/New_York"));
        // $booking_end_time =  date_format($date1,"H:i A");

        // dd($timezone_array);
		$checklineride = OutTwonrideBooking::where('driver_id',$user->id)->where('booking_date','>=',$request->booking_date)->count();
		if($checklineride > 3){
			return response()->json(['status' => 'error','message' => 'Not more than 4 Out Town ride can create for a day']);
		}

		$checkOutTown = OutTwonrideBooking::where('driver_id',$user->id)->where('booking_date','=',$request->booking_date)->get();
		if($checkOutTown->count() > 0){
			foreach($checkOutTown as $checkBooking){
				if(!empty($checkBooking) ){
					$check_start_time = explode(' ', $request->booking_start_time);
					$check_end_time = explode(' ', $request->booking_end_time);
					$start_time = explode(' ', $checkBooking->booking_start_time);
					$end_time = explode(' ', $checkBooking->booking_end_time);

					$start_date = Carbon::createFromTimeString( $checkBooking->booking_date.' '.$check_start_time[0]);
					$end_date = Carbon::createFromTimeString( $checkBooking->booking_date.' '.$check_end_time[0]);
					$begin = Carbon::createFromTimeString( $checkBooking->booking_date.' '.$start_time[0]);
					$end   = Carbon::createFromTimeString( $checkBooking->booking_date.' '.$end_time[0]);

					if($start_date->between($begin, $end) || $end_date->between($begin, $end)){
						return response()->json(['status' => 'error','message' => 'Requested Time for driver is booked for Another Ride']);
					} else if ($begin->between($start_date, $end_date) || $end->between($start_date, $end_date)){
						return response()->json(['status' => 'error','message' => 'Requested Time for driver is booked for Another Ride']);
					}
				}
			}
		}


		if($request->promo_id){
			$promo_id = $request->promo_id;
		}else{
			$promo_id = '';
		}
		$getservicetype = RideSetting::where('code','out_of_town')->first();
		$total_distance = $this->distance($request->start_latitude, $request->start_longitude, $request->drop_latitude, $request->drop_longitude);

		$seat='';
		$driverdetail = User::with(['driver_details','driver_model'])->where('id', $user->id)->first();
		if(!empty($driverdetail->driver_model)){
			// $seat = (int)$driverdetail->driver_model->total_seat - 1;
			$seat = (int)$driverdetail->driver_model->total_seat;
		}

		$outoftownBooking = OutTwonrideBooking::create([
			'booking_type'             => "out_of_town",
			'pick_up_location'         => $request->pick_up_location,
			'drop_location'            => @$request->drop_location,
			'start_latitude'           => $request->start_latitude,
			'start_longitude'          => $request->start_longitude,
			'drop_latitude'            => @$request->drop_latitude,
			'drop_longitude'           => @$request->drop_longitude,
            // 'otp'                      => mt_rand(1000, 9999),
			'booking_date'             => @$request->booking_date,
			'booking_start_time'       => @$request->booking_start_time,
			'booking_end_time'         => @$request->booking_end_time,
			'ride_setting_id'          => $getservicetype->id,
			'driver_id'                => $user->id,
            // 'user_id'                  => $user->id,
			'total_distance'           => $total_distance,
            // 'payment_type'             => $request->payment_type,
			'total_amount'             => '100',
			'mailes'                   => @$request->mailes,
			'seat_available'           => @$seat,
            // 'seat_booked'              => $request->seat_selected,
			'start_date'               => @$request->start_date,
			'end_date'                 => @$request->end_date
			]);
		if(!empty($outoftownBooking)){
			return response()->json(['status' => 'success','message' => 'Out Of Town Schedule Done Successfully','data' => $outoftownBooking]);
		} else {
			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Line Ride Booking
|--------------------------------------------------------------------------
|
*/
public function linerideBooking(Request $request){
	$validation_array =[
	'pick_up_location'    => 'required',
	'drop_location'       => 'required',
	'start_latitude'      => 'required',
	'start_longitude'     => 'required',
	'drop_latitude'       => 'required',
	'drop_longitude'      => 'required',
	'driver_id'           => 'required',
	'vehicle_id'          => 'required',
	'payment_type'        => 'required',
	'booking_date'        => 'required',
	'booking_start_time'  => 'required',
	'seat_selected'       => 'required',
	'seat_available'      => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
	}
	try{
		if($request->promo_id){
			$promo_id = $request->promo_id;
		}else{
			$promo_id = '';
		}
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$getservicetype = RideSetting::where('code','line_ride')->first();
		$total_distance = $this->distance($request->start_latitude, $request->start_longitude, $request->drop_latitude, $request->drop_longitude);
		if($request->vehicle_id){
			$getvehicleseat = Vehicle::where('id',$request->vehicle_id)->select('total_seat')->first();
			$totalseat = (int)$getvehicleseat->total_seat - 1;
		}else{
			$totalseat = '0';
		}
		$linerideBooking = LinerideBooking::create([
			'booking_type'             => $request->booking_type,
			'pick_up_location'         => $request->pick_up_location,
			'drop_location'            => $request->drop_location,
			'start_latitude'           => $request->start_latitude,
			'start_longitude'          => $request->start_longitude,
			'drop_latitude'            => $request->drop_latitude,
			'drop_longitude'           => $request->drop_longitude,
			'otp'                      => mt_rand(1000, 9999),
			'booking_date'             => $request->booking_date,
			'booking_start_time'       => $request->booking_start_time,
			'ride_setting_id'          => $getservicetype->id,
			'driver_id'                => $request->driver_id,
			'user_id'                  => $user->id,
			'total_distance'           => $total_distance,
			'payment_type'             => $request->payment_type,
			'total_amount'             => '100',
			'seat_available'           => $request->seat_available,
			'seat_booked'              => $request->seat_selected,
			]);
		if(!empty($linerideBooking)){
			return response()->json(['status' => 'success','message' => 'Shuttle Booking Done Successfully','data' => $linerideBooking]);
		} else {
			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Complete Payment
|--------------------------------------------------------------------------
|
*/
public function completePayment(Request $request){
	$validation_array =[
	'booking_id' => 'nullable',
	'total_amount' => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->errors()->first(),'data' => (object)[]]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$hold_time_rate = Setting::where('code','hold_time_rate')->first();
		$trip_detail=[];
		if($request->tip_amount){
			$trip_detail['tip_amount'] = $request->tip_amount;
		}else{
			$trip_detil['tip_amount'] = '';
		}if($request->toll_amount){
			$trip_detail['toll_amount'] = $request->toll_amount;
		}else{
			$trip_detil['toll_amount'] = '';
		}if($request->airport_charge){
			$trip_detail['airport_charge'] = $request->airport_charge;
		}else{
			$trip_detil['airport_charge'] = '';
		}if($request->total_amount){
			$trip_detail['total_amount'] = $request->total_amount;
		}else{
			$trip_detil['total_amount'] = '';
		}if($request->transaction_id){
			$trip_detail['transaction_id'] = $request->transaction_id;
		}else{
			$trip_detil['transaction_id'] = '';
		}
		$tripdata = array(
			'tip_amount'        => @$trip_detail['tip_amount'],
			'toll_amount'       => @$trip_detail['toll_amount'],
			'airport_charge'    => @$trip_detail['airport_charge'],
			'total_amount'      => @$trip_detail['total_amount'],
			'transaction_id'    => @$trip_detail['transaction_id'],
			);

		if($request->booking_id){
			Booking::where('id', $request->booking_id)->update($tripdata);
			$tripdetail = Booking::find($request->booking_id);
			if(!empty($tripdetail)){
				$service_type='booking';
				if($tripdetail->payment_type === 'wallet'){
					$result = $this->setWalletPayment($tripdetail, $service_type);
				}else{
					$result = $this->setCardPayment($tripdetail, $request->transaction_id, $service_type);
				}
				$trips = Booking::find($request->booking_id);
				$data['total_amount']       = $trips->total_amount;
				$data['base_fare']          = $trips->base_fare;
				$data['hold_time_amount']   = $trips->hold_time_amount;
				$data['tip_amount']         = $trips->tip_amount;
				$data['toll_amount']        = $trips->toll_amount;
				$data['airport_charge']     = $trips->airport_charge;
				$booking_notifiction = Booking::where('id', $request->booking_id)->first();

				$UserNotification = UserNotification::where('booking_id', $request->booking_id)->where('sent_from_user',$booking_notifiction->user_id)->where('sent_to_user',$booking_notifiction->driver_id)->update([
					'sent_from_user' => $booking_notifiction->user_id,
					'sent_to_user' => $booking_notifiction->driver_id,
					'notification_for' => "completed",
					'title' => "Booking Complete",
					'description' => "Your Trip Successfully Completed",
					'admin_flag' => "0",
					]);

                // $UserNotification = UserNotification::create([
                //     'sent_from_user' => $booking_notifiction->user_id,
                //     'sent_to_user' => $booking_notifiction->driver_id,
                //     'booking_id' => $request->booking_id,
                //     'notification_for' => "completed",
                //     'title' => "Booking Complete Payment",
                //     'description' => "Your Trip Successfully Completed",
                //     'admin_flag' => "0",
                // ]);
                //Refer User
                // if(!empty($user->ref_id)){
                //     $percentage = Setting::where('code','referral_rate')->first()->value;
                //     $totalamount = $request->total_amount;
                //     $amount = ($percentage / 100) * $totalamount;
                //     $refer_user = User::where('id',$user->id)->pluck('ref_id');
                //     $wallet_user = User::where('uuid',$refer_user)->first();
                //     ReferWallets::create([
                //         'user_id' => $wallet_user->id,
                //         'refer_id' => $user->id,
                //         'amount' => $amount,
                //         'percentage' => $percentage,
                //         'booking_id' => $request->booking_id,
                //         ]);
                //     $checkwallet = Wallet::where('user_id',$wallet_user->id)->first();
                //     if(!empty($checkwallet)){
                //         $amount = (int)$checkwallet->amount + (int)$amount;
                //         Wallet::where('user_id', (int)$wallet_user->id)->update(['amount' => $amount]);
                //         WalletHistory::create([
                //             'user_id'       => $wallet_user->id,
                //             'amount'        => (int)$amount,
                //             'description'   => '$'.(int)$amount.' added to Your Wallet',
                //             'refer_user_id' => $user->id
                //             ]);
                //     } else{
                //         $userwallet = Wallet::create(['user_id' => $wallet_user->id,'amount'=>$amount]);
                //         WalletHistory::create([
                //             'user_id'       => $wallet_user->id,
                //             'amount'        => (int)$amount,
                //             'description'   => '$'.(int)$amount.' added to Your Wallet',
                //             'refer_user_id' => $user->id
                //             ]);
                //     }
                // }
				$avgrating = RatingReviews::where('from_user_id',$tripdetail->driver_id)->orWhere('to_user_id',$tripdetail->driver_id)->avg('rating');
				$data ['driver_avgrating'] = $avgrating;
				$payment_booking_status = Booking::where('id', $request->booking_id)->first();
				$payment_booking_status->payment_status       = "completed";
				$payment_booking_status->save();

				$Completed_user = User::where('id',$tripdetail->user_id)->first();
				$Ride_user = RideSetting::where('id',$tripdetail->ride_setting_id)->first();


				$emailcontent = array (
					'text' => 'Complete Trip',
					'title' => 'Thanks for join Ruerun for Ride And Complete Trip.',
					'userName' => $Completed_user->first_name
					);
				$details['email'] = $Completed_user->email;
				$details['username'] = $Completed_user->first_name;
				$details['subject'] = 'Complete Trip';
				dispatch(new CompletedForTrip($details,$emailcontent));

				$emailcontent_payment = array (
					'text'             => 'Complete Trip',
					'title'            => 'Thanks for join Ruerun for Ride And Complete Trip.',
					'userName'         => $Completed_user->first_name,
					'pick_up_location' => $tripdetail->pick_up_location,
					'drop_location'    => $tripdetail->drop_location,
					'booking_date'     => $tripdetail->booking_date,
					'ride_name'             => $Ride_user->name,
					'trip_status'      => $tripdetail->trip_status,
					'base_fare'        => $tripdetail->base_fare
					);
				$details_payment['email'] = $Completed_user->email;
				$details_payment['username'] = $Completed_user->first_name;
				$details_payment['subject'] = 'Payment Receipt';
				dispatch(new PaymentReceipt($details_payment,$emailcontent_payment));
				return response()->json(['status' => $result['type'],'message' => $result['message'], 'amount_detail'=>$data ]);
			}else{
				return response()->json(['status' => 'error','message' => 'Booking not found']);
			}
		}else if($request->parcel_id){
			ParcelDetail::where('id', $request->parcel_id)->update($tripdata);
			$tripdetail = ParcelDetail::find($request->parcel_id);
			if(!empty($tripdetail)){
				$service_type='parcel_booking';
				if($tripdetail->payment_type === 'wallet'){
					$result = $this->setWalletPayment($tripdetail, $service_type);
				}else{
					$result = $this->setCardPayment($tripdetail, $request->transaction_id, $service_type);

				}
				$trips = ParcelDetail::find($request->parcel_id);
				$data['total_amount']       = $trips->total_amount;
				$data['base_fare']          = $trips->base_fare;
				$data['hold_time_amount']   = $trips->hold_time_amount;
				$data['tip_amount']         = $trips->tip_amount;
				$data['toll_amount']        = $trips->toll_amount;
				$data['airport_charge']     = $trips->airport_charge;
				$booking_notifiction = ParcelDetail::where('id', $request->parcel_id)->first();

				$UserNotification = UserNotification::where('parcel_id', $request->parcel_id)->where('sent_from_user',$booking_notifiction->user_id)->where('sent_to_user',$booking_notifiction->driver_id)->update([
					'sent_from_user' => $booking_notifiction->user_id,
					'sent_to_user' => $booking_notifiction->driver_id,
					'notification_for' => "completed",
					'title' => "Booking Complete Payment",
					'description' => "Your Trip Successfully Completed",
					'admin_flag' => "0",
					]);
                // $UserNotification = UserNotification::create([
                //     'sent_from_user' => $booking_notifiction->user_id,
                //     'sent_to_user' => $booking_notifiction->driver_id,
                //     'parcel_id' => $request->parcel_id,
                //     'notification_for' => "completed",
                //     'title' => "Parcel Booking Complete Payment",
                //     'description' => "Your Trip Successfully Completed",
                //     'admin_flag' => "0",
                // ]);
                //Refer User
				if(!empty($user->ref_id)){
					$percentage = Setting::where('code','referral_rate')->first()->value;
					$totalamount = $request->total_amount;
					$amount = ($percentage / 100) * $totalamount;
					$refer_user = User::where('id',$user->id)->pluck('ref_id');
					$wallet_user = User::where('uuid',$refer_user)->first();
					ReferWallets::create([
						'user_id' => $wallet_user->id,
						'refer_id' => $user->id,
						'amount' => $amount,
						'percentage' => $percentage,
						'parcel_id' => $request->parcel_id,
						]);
					$checkwallet = Wallet::where('user_id',$wallet_user->id)->first();
					if(!empty($checkwallet)){
						$amount = (int)$checkwallet->amount + (int)$amount;
						Wallet::where('user_id', (int)$wallet_user->id)->update(['amount' => $amount]);
						WalletHistory::create([
							'user_id'       => $wallet_user->id,
							'amount'        => (int)$amount,
							'description'   => '$'.(int)$amount.' added to Your Wallet',
							'refer_user_id' => $user->id
							]);
					} else{
						$userwallet = Wallet::create(['user_id' => $wallet_user->id,'amount'=>$amount]);
						WalletHistory::create([
							'user_id'       => $wallet_user->id,
							'amount'        => (int)$amount,
							'description'   => '$'.(int)$amount.' added to Your Wallet',
							'refer_user_id' => $user->id
							]);
					}
				}
				$avgrating = RatingReviews::where('from_user_id',$tripdetail->driver_id)->orWhere('to_user_id',$tripdetail->driver_id)->avg('rating');
				$data ['driver_avgrating'] = $avgrating;
				$payment_booking_status = ParcelDetail::where('id', $request->parcel_id)->first();
				$payment_booking_status->payment_status       = "completed";
				$payment_booking_status->save();

				return response()->json(['status' => $result['type'],'message' => $result['message'], 'amount_detail'=>$data ]);
			}else{
				return response()->json(['status' => 'error','message' => 'Booking not found']);
			}
		}else if($request->shuttle_id){
			LinerideUserBooking::where('id', $request->shuttle_id)->update($tripdata);
			$tripdetail = LinerideUserBooking::find($request->shuttle_id);
			if(!empty($tripdetail)){
				$service_type = 'shuttle';
				if($tripdetail->payment_type === 'wallet'){
					$result = $this->setWalletPayment($tripdetail, $service_type);
				}else{
					$result = $this->setCardPayment($tripdetail, $request->transaction_id, $service_type);
				}
				$trips = LinerideUserBooking::find($request->shuttle_id);
				$data['total_amount']       = $trips->total_amount;
				$data['base_fare']          = $trips->base_fare;
				$data['hold_time_amount']   = $trips->hold_time_amount;
				$data['tip_amount']         = $trips->tip_amount;
				$data['toll_amount']        = $trips->toll_amount;
				$data['airport_charge']     = $trips->airport_charge;
				$booking_notifiction = LinerideUserBooking::where('id', $request->shuttle_id)->first();
				UserNotification::where('shuttle_id', $request->shuttle_id)->where('sent_from_user',$booking_notifiction->user_id)->where('sent_to_user',$booking_notifiction->driver_id)->update([
					'sent_from_user' => $booking_notifiction->user_id,
					'sent_to_user' => $booking_notifiction->driver_id,
					'notification_for' => "completed",
					'title' => "Shuttle Booking Complete Payment",
					'description' => "Your Shuttle Trip Successfully Completed",
					'admin_flag' => "0",
					]);

                // $UserNotification = UserNotification::create([
                //     'sent_from_user'        => $booking_notifiction->user_id,
                //     'sent_to_user'          => $booking_notifiction->driver_id,
                //     'shuttle_id'            => $request->shuttle_id,
                //     'notification_for'      => "completed",
                //     'title'                 => "Shuttle Booking Complete Payment",
                //     'description'           => "Your Shuttle Trip Successfully Completed",
                //     'admin_flag'            => "0",
                // ]);
                //Refer User
				if(!empty($user->ref_id)){
					$percentage = Setting::where('code','referral_rate')->first()->value;
					$totalamount = $request->total_amount;
					$amount = ($percentage / 100) * $totalamount;
					$refer_user = User::where('id',$user->id)->pluck('ref_id');
					$wallet_user = User::where('uuid',$refer_user)->first();
					ReferWallets::create([
						'user_id' => $wallet_user->id,
						'refer_id' => $user->id,
						'amount' => $amount,
						'percentage' => $percentage,
						'shuttle_id' => $request->shuttle_id,
						]);
					$checkwallet = Wallet::where('user_id', $wallet_user->id)->first();
					if(!empty($checkwallet)){
						$amount = (int)$checkwallet->amount + (int)$amount;
						Wallet::where('user_id', (int)$wallet_user->id)->update(['amount' => $amount]);
						WalletHistory::create([
							'user_id'       => $wallet_user->id,
							'amount'        => (int)$amount,
							'description'   => '$'.(int)$amount.' added to Your Wallet',
							'refer_user_id' => $user->id
							]);
					} else{
						$userwallet = Wallet::create(['user_id' => $wallet_user->id,'amount'=>$amount]);
						WalletHistory::create([
							'user_id'       => $wallet_user->id,
							'amount'        => (int)$amount,
							'description'   => '$'.(int)$amount.' added to Your Wallet',
							'refer_user_id' => $user->id
							]);
					}
				}
				$avgrating = RatingReviews::where('from_user_id',$tripdetail->driver_id)->orWhere('to_user_id',$tripdetail->driver_id)->avg('rating');
				$data ['driver_avgrating'] = $avgrating;
				$payment_booking_status = LinerideUserBooking::where('id', $request->shuttle_id)->first();
				$payment_booking_status->payment_status       = "completed";
				$payment_booking_status->save();

				return response()->json(['status' => $result['type'],'message' => $result['message'], 'amount_detail'=>$data ]);
			}else{
				return response()->json(['status' => 'error','message' => 'Booking not found']);
			}
		}
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
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
				'amount'            => (int)$total_amount ,
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


public function getParcelDriverList(Request $request){
	$validator = [
	'parcel_id'            =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$parceldetails = ParcelDetail::with(['parcel_images','parcel_packages'])->where('id', $request->parcel_id)->first();
		$distance_setting = Setting::where('code','search_driver_area')->first();
		$user_booking = ParcelDetail::with(['driver','user'])->where('id',$request->get('parcel_id'))->first();
		$user_data = $user_booking->user;
		$to_time =  Carbon::now();
		$from_time = \Carbon\Carbon::parse($parceldetails->created_at)->format('Y-m-d H:i');
		$diff = $to_time->diffInMinutes($from_time);

//        if($diff >= 2){
//            $parceldriverdetail = ParcelDriver::with(['driver','user'])->where('parcel_id',$request->parcel_id)->where('status','accepted')->limit(5)->orderBy('driver_amount','ASC')->get();
//            return response()->json(['status' => 'success','message' => 'Driver Found !!', 'data' => $parceldriverdetail]);
//        }

		$parceldriverdetail = ParcelDriver::with(['driver','user'])->where('parcel_id',$request->parcel_id)->where('status','accepted')->limit(5)->orderBy('driver_amount','ASC')->get();
		if($parceldriverdetail->count() > 0){
            //sleep(5);
            //$parceldriverdetail1 = ParcelDriver::with(['driver','user'])->where('parcel_id',$request->parcel_id)->where('status','accepted')->limit(5)->orderBy('driver_amount','ASC')->get();
			return response()->json(['status' => 'success','message' => 'Driver Found !!', 'data' => $parceldriverdetail]);
		}


		if($request->latitude){
			$latitude         = $request->latitude;
		}else{
			$latitude         = $user->latitude;
		}
		if($request->longitude){
			$longitude         = $request->longitude;
		}else{
			$longitude         = $user->longitude;
		}
		if($distance_setting !== null){
			$distance = $distance_setting->value;
		} else {
			$distance = 1;
		}
		if($request->service_id){
			$service = $request->service_id;
		}else{
			$service = RideSetting::where('code','parcel_delivery')->first()->id;
		}
		$getdriversid = '';
		$distance_In_value_setting = Setting::where('code','distance_in')->first();
		$distance_In_setting = $distance_In_value_setting->value;
		if($distance_In_setting === 'km'){
			$resData = $this->getparceldriverkmresult($latitude, $longitude, $distance, $service, $getdriversid);
		}elseif($distance_In_setting === 'miles'){
			$resData = $this->getparceldriverkmresult($latitude, $longitude, $distance, $service, $getdriversid);
		}else{
			$user_booking->driver = new \stdClass();
			$message = 'Driver Data Not Found';
			return response()->json(['status'  => 'error','booking_status' => $user_booking->parcel_status,'message' => $message,'data'    => $user_booking->driver,]);
		}
		if(!empty($resData)){
			$initial=0;
			$user_detail = User::where('id',$user->id)->first();
			if(!empty($user_detail)){
				$user_name = @$user_detail->last_name.' '.@$user_detail->first_name;
			} else {
				$user_name = @$user_detail->company_name;
			}
			foreach ($resData as $res) {
				$initial=$initial++;
				$checkIfNotifiactionAlreadySent = BookingNotifications::where('parcel_id',$request->get('parcel_id'))->where('driver_id',$res['driver_id'])->where('is_send','1')->first();
				if($res !== null){
					$driver = User::where('id',$res['driver_id'])->first();
					$destination_addresses = $parceldetails->drop_location;
					$origin_addresses = $parceldetails->pick_up_location;
					$avgrating = RatingReviews::where('from_user_id',$res['driver_id'])->orWhere('to_user_id',$res['driver_id'])->avg('rating');
					if(empty($avgrating)){
						$avgrating = 0;
					}
					$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
					$distance_arr = json_decode($distance_data);
					$elements = $distance_arr->rows[0]->elements;
					$duration = $elements[0]->duration->text;
					$driver_data['elements'] = $elements;
					$driver_data['duration'] = $duration;
					$driver_data['distance'] = $distance;
					$driver_data['user_name'] = @$user_name;
					$driver_data['avatar'] = $user_detail->avatar;
					$driver_data['ride_setting'] = RideSetting::where('id',$service)->first()->name;
					$driver_data['booking_id'] = $parceldetails->id;
					$driver_data['booking_type'] = $parceldetails->booking_type;
					$driver_data['pick_up_location'] = $parceldetails->pick_up_location;
					$driver_data['drop_location'] = $parceldetails->drop_location;
					$driver_data['start_latitude'] = $parceldetails->start_latitude;
					$driver_data['start_longitude'] = $parceldetails->start_longitude;
					$driver_data['drop_latitude'] = $parceldetails->drop_latitude;
					$driver_data['drop_longitude'] = $parceldetails->drop_longitude;
					$driver_data['date_time'] = @$parceldetails->booking_date .' '. @$parceldetails->booking_start_time;
					$driver_data['driver_review'] = $avgrating;
					$driver_data['message'] =  'New Parcel Booking Arrived from '.@$user_detail->last_name.' '.@$user_detail->first_name;
					$driver_data['type'] = 'new_parcel_booking';
					$driver_data['driver_id'] = $res['driver_id'];
					$driver_data['title']        =  'RueRun';
					$driver_data['sound']        = 'default';
					if($checkIfNotifiactionAlreadySent === null){
						$driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver,$driver_data));
						BookingNotifications::create([
							'driver_id'  => $res['driver_id'],
							'parcel_id' => $request->get('parcel_id'),
							'is_send'    => '1',
							]);
						UserNotification::create([
							'sent_from_user' => $user->id,
							'sent_to_user' => $res['driver_id'],
							'parcel_id' => @$parceldetails->id,
							'notification_for' => 'pending',
							'title' => "New Parcel Booking Pending",
							'description' => "Your Parcel Booking Pending",
							'admin_flag' => "0",
							]);
						ParcelDriver::create([
							'parcel_id' => @$parceldetails->id,
							'driver_id' => @$res['driver_id'],
							'user_id' => @$user->id,
							]);
					}
				}
			}
			$parceldriverdetail = ParcelDriver::with(['driver','user'])->where('parcel_id',$request->parcel_id)->where('status','accepted')->limit(5)->orderBy('driver_amount','ASC')->get();
			if(!empty($parceldriverdetail->driver)){
				$data = $parceldriverdetail->driver;
			}else{
				$data = new \stdClass();
			}
			return response()->json(['status'  => 'success','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Found','data'    => $data]);
		}else{
			$parceldriverdetail = new \stdClass();
			$message = 'Driver Data Not Found';
			return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => $message,'data'    => $parceldriverdetail->driver,]);
		}
	}catch (Exception $e) {
		$parceldriverdetail->driver = new \stdClass();
		$message = 'Driver Data Not Found';
		return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => $message,'data'    => $parceldriverdetail->driver,]);
	}
}


public function getparceldriverkmresult($latitude, $longitude, $distance, $service, $getdriversid){

	$resultData = User::whereHas('driver_details_settings', function($q) use($service){
		$q->where('ride_type','=', $service);
	})->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved','availability_status'=>'on'])->get();
	$driverDetails=[];
	$driverDetailsData1 = [];
	foreach ($resultData as $key => $value) {
		$getdriversidecheckData = ParcelDetail::where('driver_id', $value->id)->whereIn('parcel_status',['on_going','driver_arrived'])->first();
		if(empty($getdriversidecheckData)){
			$driverDetails['driver_id'] = $value->id;
			$driverDetails['email'] = $value->email;
			if(!empty($value->latitude))
			{
				$driver_latitude = $value->latitude;
			}else{
				$driver_latitude = $value->add_latitude;
			}
			if(!empty($value->longitude)){
				$driver_longitude = $value->longitude;
			} else {
				$driver_longitude = $value->add_longitude;
			}

			$driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$latitude,$longitude,'K');
			if($driverDetails['distance'] <= $distance){
				$driverDetailsData1[] = $driverDetails;
			}
		}
	}
	return $driverDetailsData1;
}


public function All_get_nearest_driver(Request $request){
	$validator = [
	'latitude'            =>'required',
	'longitude'     =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		if($request->latitude){
			$latitude         = $request->latitude;
		}else{
			$latitude         = $user->latitude;
		}

		if($request->longitude){
			$longitude         = $request->longitude;
		}else{
			$longitude         = $user->longitude;
		}

		$distance_setting = Setting::where('code','search_driver_area')->where('hidden','0')->first();

		if(!empty($distance_setting)){
			$distance = $distance_setting->value;
		} else {
			$distance = 1;
		}

		$distance_In_value_setting = Setting::where('code','distance_in')->first();
		$distance_In_setting = $distance_In_value_setting->value;
		if($distance_In_setting === 'km'){
			$res = $this->Allgetdriverkmresult($latitude, $longitude, $distance);

			if(count($res) != 0){
				return response()->json(['status'=>'success','message'=> 'You are successfully Driver Distance!','data'=> $res]);
			}else{
				$distance = $distance + $distance_setting->value;
				$res = $this->Allgetdriverkmresult($latitude, $longitude, $distance);
				return response()->json(['status'=>'success','message'=> 'You are successfully Driver Distance!','data'=> $res]);
			}
		}elseif($distance_In_setting === 'miles'){
			$res = $this->Allgetdriverkmresult($latitude, $longitude, $distance);
			if(count($res) != 0){
				return response()->json(['status'=>'success','message'=> 'You are successfully Driver Distance!','data'=> $res]);
			}else{
				$distance = $distance + $distance_setting->value;
				$res = $this->Allgetdriverkmresult($latitude, $longitude, $distance);
				return response()->json(['status'=>'success','message'=> 'You are successfully Driver Distance!','data'=> $res]);
			}
		}else {
			return response()->json(['status'=> 'error','message'=>'Distance In Not Found']);
		}
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Get Driver KM
|--------------------------------------------------------------------------
|
*/
public function Allgetdriverkmresult($latitude, $longitude, $distance){
	$result = User::where('latitude','!=', $latitude)->where('longitude','!=', $longitude)->selectRaw('*, ( 6367 * acos( cos( radians( ? ) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( ? ) ) + sin( radians( ? ) ) * sin( radians( latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
	->where('users.status','active')
	->where('users.doc_status','approved')
	->where('users.availability_status','on')
	->where('users.user_type','driver')
	->having('distance', '<=', $distance)
	->orderBy('distance','ASC')
	->get();
	return $result;
}

/*
|--------------------------------------------------------------------------
| Get Driver ML
|--------------------------------------------------------------------------
|
*/
public function Allgetdrivermlresult($latitude, $longitude, $distance, $service, $getdriversid, $vehicle_id){
	$result = User::where('latitude','!=', $latitude)->where('longitude','!=', $longitude)->selectRaw('*, round( 3959 * acos( cos( radians( ? ) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians( ? ) ) + sin( radians( ? ) ) * sin( radians( latitude ) ) ) ) AS distance', [$latitude, $longitude, $latitude])
	->where('users.status','active')
	->where('users.doc_status','approved')
	->where('users.availability_status','on')
	->where('users.user_type','driver')
	->having('distance', '<=', $distance)
	->orderBy('distance','ASC')
	->get();
	return $result;
}

public function getparceldriverdetails(Request $request){
	$validator = [
	'parcel_id' =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$parceldetail = ParcelDriver::with(['driver'])->where('parcel_id',$request->parcel_id)
		->where('status','accepted')
		->limit(5)->orderBy('driver_amount','ASC')->get();
		return response()->json(['status'=>'success','message'=> 'You are successfully Get Driver Data!','data'=> $parceldetail]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

public function confirmParcelDriver(Request $request){
	$validator = [
	'parcel_id'            =>'required',
	'driver_id'            =>'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}

		ParcelDetail::where('id', $request->parcel_id)->update([
			'driver_id'=>$request->driver_id, 'parcel_status'=>'accepted'
			]);

		ParcelDriver::where('parcel_id', $request->parcel_id)->where('driver_id', $request->driver_id)
		->update(['user_confirm'=>'confirm']);

		$checkparceldriver = ParcelDriver::where('parcel_id', $request->parcel_id)->where('driver_id','!=', $request->driver_id)->get();

		if($checkparceldriver->count() > 0){
			foreach ($checkparceldriver as $key => $driver){
				ParcelDriver::where('parcel_id', $request->parcel_id)->where('driver_id', $driver->driver_id)
				->update(['user_confirm'=>'rejected']);
			}
		}
		$parceldetails = ParcelDetail::where('id', $request->parcel_id)->first();

            // Notification For Driver
		$driver_data['message']       = 'Your Parcel Booking Request Confirmed By '.$user->first_name;
		$driver_data['type']          = 'confirmed';
		$driver_data['driver_id']     = $parceldetails->driver_id;
		$driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($parceldetails,$driver_data));

		return response()->json(['status'=>'success','message'=> 'You are successfully Selected Driver!']);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}


    /*
    |--------------------------------------------------------------------------
    | Create User Shuttle Trip
    |--------------------------------------------------------------------------
    |
    */
    public function shuttleRideBooking(Request $request){
    	$validation_array =[
    	'pick_up_location'    => 'required',
    	'drop_location'       => 'required',
    	'start_latitude'      => 'required',
    	'start_longitude'     => 'required',
    	'drop_latitude'       => 'required',
    	'drop_longitude'      => 'required',
    	'service_id'          => 'required',
    	'radius'              => 'required',
    	];
    	$validation = Validator::make($request->all(),$validation_array);
    	if($validation->fails()){
    		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
    	}
    	try{

    		$user = JWTAuth::parseToken()->authenticate();
    		if(!$user){
    			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    		}
    		if($request->extra_notes){
    			$extra_notes = $request->extra_notes;
    		}else{
    			$extra_notes = '';
    		}
    		if($request->total_luggage){
    			$total_luggage = $request->total_luggage;
    		}else{
    			$total_luggage = '';
    		}
    		if($request->booking_date){
    			$booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
    		}else{
    			$booking_date = Carbon::now()->format('Y-m-d');
    		}

    		if($request->booking_start_time){
    			$booking_time = $request->booking_start_time;
    		}else{
    			$booking_time = Carbon::now()->format('H:i A');
    		}

    		$total_distance = $this->distance($request->start_latitude, $request->start_longitude, $request->drop_latitude, $request->drop_longitude);
    		if($request->vehicle_id){
    			$getvehicleseat = Vehicle::where('id',$request->vehicle_id)->select('total_seat')->first();
    			$totalseat = (int)$getvehicleseat->total_seat - 1;
    		}else{
    			$totalseat = '0';
    		}

    		$destination_addresses = $request->drop_location;
    		$origin_addresses = $request->pick_up_location;
    		$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
    		$distance_arr = json_decode($distance_data);
    		$elements = $distance_arr->rows[0]->elements;
    		$distance='';
    		if($elements[0]->status != 'NOT_FOUND'){
    			$distance = $elements[0]->distance->text;
    		}else{
    			$distance = '';
    		}

            // check particular shuttle booking for user
    		$checkBooking = LinerideUserBooking::where('user_id', @$user->id)
    		->where('pick_up_location', @$request->pick_up_location)
    		->where('drop_location', @$request->drop_location)
    		->where('booking_start_time', @$booking_time)
    		->where('booking_end_time', @$request->booking_end_time)
    		->where('booking_date', @$booking_date)
    		->whereNotIn('trip_status', ["cancelled","pending","completed"])
    		->first();
    		if(!empty($checkBooking)){
    			return response()->json(['status' => 'error','message' => 'You have already booked for this Locations and Time Intervals']);
    		}

    		$trips = LinerideUserBooking::create([
    			'taxi_hailing'             => $request->taxi_hailing,
    			'booking_type'             => $request->booking_type,
    			'pick_up_location'         => $request->pick_up_location,
    			'drop_location'            => $request->drop_location,
    			'start_latitude'           => $request->start_latitude,
    			'start_longitude'          => $request->start_longitude,
    			'drop_latitude'            => $request->drop_latitude,
    			'drop_longitude'           => $request->drop_longitude,
    			'otp'                      => mt_rand(1000, 9999),
    			'booking_date'             => $booking_date,
    			'booking_start_time'       => $booking_time,
    			'booking_end_time'         => $request->booking_end_time,
    			'extra_notes'              => $extra_notes,
    			'ride_setting_id'          => $request->service_id,
    			'user_id'                  => $user->id,
    			'seat_booked'              => $request->seats,
    			'total_luggage'            => $total_luggage,
    			'vehicle_id'               => $request->vehicle_id,
    			'radius'                   => $request->radius,
    			'total_distance'           => $distance,
    			]);
    		if(!empty($trips)){
    			$tripdetail = LinerideUserBooking::find($trips->id);
    			return response()->json(['status' => 'success','message' => 'User Shuttle Booking Successfully','data' => $tripdetail]);
    		}else{
    			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    		}
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }


    public function getShuttleDriverList(Request $request){
    	$validator = [
    	'shuttle_id'            =>'required',
    	];
    	$validation = Validator::make($request->all(),$validator);
    	if($validation->fails()){
    		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
    	}
    	try {
    		$user = JWTAuth::parseToken()->authenticate();
    		if(!$user){
    			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    		}
    		$distance_setting = Setting::where('code','search_driver_area')->first();
    		$user_booking = LinerideUserBooking::with(['driver'])->where('id',$request->get('shuttle_id'))->first();

            //  Check Any accepted User Booking
    		$checkshuttledriver = ShuttleDriver::with(['user_shuttle_details','driver','driver_shuttle_details'])->where('shuttle_id', $request->shuttle_id)
    		->where('status','accepted')->get();
    		if(sizeof($checkshuttledriver)){
    			return response()->json(['status' => 'success','message' => 'Driver Found !!', 'data' => $checkshuttledriver]);
    		}
            //  Check Any accepted User Booking


    		if($request->service_id){
    			$service = $request->service_id;
    		}else{
    			$service = RideSetting::where('code','line_ride')->first()->id;
    		}

    		if($request->latitude){
    			$latitude         = $request->latitude;
    		}else{
    			$latitude         = $user->latitude;
    		}
    		if($request->longitude){
    			$longitude         = $request->longitude;
    		}else{
    			$longitude         = $user->longitude;
    		}
    		if(!empty($user_booking)){
    			$distance = $user_booking->radius;
    		}else if($distance_setting !== null){
    			$distance = $distance_setting->value;
    		} else {
    			$distance = 1;
    		}
    		$getdriversid = '';
    		$distance_In_value_setting = Setting::where('code','distance_in')->first();
    		$distance_In_setting = $distance_In_value_setting->value;
    		if($distance_In_setting === 'km'){
    			$resData = $this->getshuttledriverkmresult($latitude, $longitude, $distance, $service, $getdriversid);

    		}elseif($distance_In_setting === 'miles'){
    			$resData = $this->getshuttledriverkmresult($latitude, $longitude, $distance, $service, $getdriversid);
    		}else{
    			$user_booking->driver = new \stdClass();
    			$message = 'Driver Data Not Found';
    			return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver,]);
    		}

    		if(!empty($resData)){
    			$initial=0;
                //return response()->json(['status'  => 'success','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Found','data'    => (array)$resData,]);


                ////////////////////////////////////////////////////////////////////////////////////
    			$booking_detail = LinerideUserBooking::where('id',$request->shuttle_id)->first();
    			$user_detail = User::where('id',$user->id)->first();
    			if(!empty($user_detail)){
    				$user_name = @$user_detail->last_name.' '.@$user_detail->first_name;
    			} else {
    				$user_name = @$user_detail->company_name;
    			}
    			$ride_setting_detail = RideSetting::where('id',$booking_detail->ride_setting_id)->first();
    			$destination_addresses = $booking_detail->drop_location;
    			$origin_addresses = $booking_detail->pick_up_location;
    			if(!empty($booking_detail)){
    				$distance = $booking_detail->total_distance;
    			} else {
    				$distance = 0;
    			}
    			foreach ($resData as $res) {
    				$initial=$initial++;
    				$checkIfNotifiactionAlreadySent = BookingNotifications::where('shuttle_id',$request->get('shuttle_id'))
    				->where('driver_id',$res['driver_id'])->where('is_send','1')->first();

    				if($res !== null){
    					$driver = User::where('id',$res['driver_id'])->first();
    					$vehicle = VehicleType::where('id',$driver->vehicle_id)->first();

    					$avgrating = RatingReviews::where('from_user_id',$res['driver_id'])->orWhere('to_user_id',$res['driver_id'])->avg('rating');
    					if(empty($avgrating)){
    						$avgrating = 0;
    					}

    					$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
    					$distance_arr = json_decode($distance_data);
    					$elements = $distance_arr->rows[0]->elements;
    					$duration = $elements[0]->duration->text;
    					$driver_data['elements'] = $elements;
    					$driver_data['duration'] = $duration;
    					$driver_data['distance'] = $distance;
    					$driver_data['user_name'] = @$user_name;
    					$driver_data['avatar'] = $user_detail->avatar;
    					$driver_data['ride_setting'] = $ride_setting_detail->name;
    					$driver_data['shuttle_id'] = $booking_detail->id;
    					$driver_data['booking_type'] = $booking_detail->booking_type;
    					$driver_data['pick_up_location'] = $booking_detail->pick_up_location;
    					$driver_data['drop_location'] = $booking_detail->drop_location;
    					$driver_data['start_latitude'] = $booking_detail->start_latitude;
    					$driver_data['start_longitude'] = $booking_detail->start_longitude;
    					$driver_data['drop_latitude'] = $booking_detail->drop_latitude;
    					$driver_data['drop_longitude'] = $booking_detail->drop_longitude;
    					$driver_data['car_name'] = $vehicle->name;
    					$driver_data['date_time'] = @$booking_detail->booking_date .' '. @$booking_detail->booking_start_time;
    					$driver_data['driver_review'] = $avgrating;
    					$driver_data['message'] =  'New Shuttle Booking Arrived from '.@$user_detail->last_name.' '.@$user_detail->first_name;
    					$driver_data['type'] = 'new_shuttle_booking';
    					$driver_data['driver_id'] = $res['driver_id'];
    					$driver_data['title']        =  'RueRun';
    					$driver_data['sound']        = 'default';

    					if($checkIfNotifiactionAlreadySent === null){
    						$driver_data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver,$driver_data));

    						BookingNotifications::create([
    							'driver_id'  => $res['driver_id'],
    							'shuttle_id' => $request->get('shuttle_id'),
    							'is_send'    => '1',
    							]);
    						UserNotification::create([
    							'sent_from_user' => $user->id,
    							'sent_to_user' => $res['driver_id'],
    							'shuttle_id' => @$driver_data['shuttle_id'],
    							'notification_for' => 'pending',
    							'title' => "New Shuttle Booking Pending",
    							'description' => "Your Shuttle Booking Pending",
    							'admin_flag' => "0",
    							]);
    						ShuttleDriver::create([
    							'shuttle_id'            => @$request->get('shuttle_id'),
    							'driver_id'             => @$res['driver_id'],
    							'user_id'               => @$user->id,
    							'shuttle_driver_id'     => @$res['shuttle_driver_id'],
    							]);
    					}
    				}
    			}
    			if(!empty($user_booking->driver)){
    				$data = $user_booking->driver;
    				if(!empty($data->first_name)){
    					$user_name = @$user_detail->last_name.' '.@$user_detail->first_name;
    				} else {
    					$user_name = @$user_detail->company_name;
    				}
    				$pickup_total_km = $this->pickupdistance($user->latitude, $user->longitude, $data->latitude, $data->longitude);
    				$data['pickup_total_km'] = number_format((float)$pickup_total_km, 2, '.', '');
    				$booking_detail = LinerideUserBooking::where('id',$request->shuttle_id)->first();
    				$data['booking_date'] = $booking_detail->booking_date;
                    //User Address
    				$lat= $user->latitude;
    				$lng= $user->longitude;
    				$user_address = $this->getaddress($lat,$lng);
    				if($user_address){
    					$origin_addresses = $user_address;
    				}
                    //Driver Address
    				$lat= $data->latitude;
    				$lng= $data->longitude;
    				$driver_address = $this->getaddress($lat,$lng);
    				if($driver_address){
    					$destination_addresses = $driver_address;
    				}
    				$destination_addresses = $destination_addresses;
    				$origin_addresses = $origin_addresses;
    				$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key=AIzaSyA7M8IrIHnp_j4AIpVWtRE6bFcTlh7SyAo');
    				$distance_arr = json_decode($distance_data);
    				$elements = $distance_arr->rows[0]->elements;
    				$distance = $elements[0]->distance->text;
    				$duration = $elements[0]->duration->text;

    				$avgrating = RatingReviews::where('from_user_id',$data)->orWhere('to_user_id',$data)->avg('rating');
    				if(empty($avgrating)){
    					$avgrating = 0;
    				}

    				$data['driver_review'] = $avgrating;
    				$data['arrival_time']  = $duration;
    				$data['booking_date']  = $user_booking->booking_date .' '. $user_booking->booking_start_time;
    			}else{
    				$data = new \stdClass();
    			}

    			return response()->json(['status'  => 'success','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Found ','data'    => [] ]);
    		}else{
    			$user_booking->driver = new \stdClass();
    			$message = 'Driver Data Not Found';
    			return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => array() ]);
                // return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver ]);
    		}
    	}catch (Exception $e) {
    		$user_booking->driver = new \stdClass();
    		$message = 'Driver Data Not Found';
    		return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => array()]);
            // return response()->json(['status'  => 'error','booking_status' => $user_booking->trip_status,'message' => 'Driver Data Not Found','data'    => $user_booking->driver]);
    	}
    }


    public function getshuttledriverkmresult($latitude, $longitude, $distance, $service, $getdriversid){
    	$resultData = User::whereHas('driver_details_settings', function($q) use($service){
    		$q->where('ride_type','=', $service);
    	})->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved','availability_status'=>'on'])->get();
    	$driverDetails=[];
    	$driverDetailsData1 = [];
    	foreach ($resultData as $key => $value) {
    		$getdriversidecheckData = LinerideBooking::where('driver_id', $value->id)
    		->whereNotIn('trip_status',['completed','cancelled'])
                            //->where('booking_date', date('Y-m-d'))
    		->first();

    		if(!empty($getdriversidecheckData)){
    			$driverDetails['driver_id'] = $value->id;
    			$driverDetails['email'] = $value->email;
    			$driverDetails['shuttle_driver_id'] = $getdriversidecheckData->id;
    			if(!empty($value->latitude))
    			{
    				$driver_latitude = $value->latitude;
    			}else{
    				$driver_latitude = $value->add_latitude;
    			}
    			if(!empty($value->longitude)){
    				$driver_longitude = $value->longitude;
    			} else {
    				$driver_longitude = $value->add_longitude;
    			}

    			$driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$latitude,$longitude,'K');
    			if($driverDetails['distance'] <= $distance){
    				$driverDetailsData1[] = $driverDetails;
    			}

                //$driverDetailsData1[] = $driverDetails;
    		}
    	}
    	return $driverDetailsData1;
    }

    /*
   |--------------------------------------------------------------------------
   | Book Shuttle Driver
   |--------------------------------------------------------------------------
   |
   */
   public function bookShuttleDriver(Request $request){
   	$validator = [
   	'shuttle_id'           =>'required',
   	'driver_id'            =>'required',
   	'payment_type'         =>'required',
            // 'card_id'              =>'required',
   	];
   	$validation = Validator::make($request->all(),$validator);
   	if($validation->fails()){
   		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
   	}
   	try {
   		$user = JWTAuth::parseToken()->authenticate();
   		if(!$user){
   			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
   		}

   		$seat_left = $seat_booked = '';
   		$get_shuttle_driver = ShuttleDriver::where('user_id', $user->id)
   		->where('shuttle_id', $request->shuttle_id)
   		->where('status', 'accepted')->first();
   		$user_seat = LinerideUserBooking::where('id', $request->shuttle_id)->first()->seat_booked;
   		if(!empty($get_shuttle_driver)){
   			$driver_ride = LinerideBooking::where('id', $get_shuttle_driver->shuttle_driver_id)->first();
   			$driver_ride_seat = $driver_ride->seat_available;
   			if((int)$driver_ride_seat >= (int)$user_seat){
   				$seat_left = (int)$driver_ride_seat - (int)$user_seat;
   				$seat_booked = (int)$user_seat;
   			}else{
   				$seat_left = 0;
   				$seat_booked = $driver_ride->seat_booked;
   			}
   		}

   		LinerideUserBooking::where('id', $request->shuttle_id)->where('user_id', $user->id)->update([
   			'driver_id'         => $request->driver_id,
   			'total_amount'      => $request->total_amount,
   			'payment_type'      => $request->payment_type,
   			'card_id'           => $request->card_id,
   			'shuttle_driver_id' => $get_shuttle_driver->shuttle_driver_id,
   			'trip_status'       => 'accepted',
   			]);

   		ShuttleDriver::where('user_id', $user->id)->where('shuttle_id', $request->shuttle_id)
   		->where('driver_id', $request->driver_id)
   		->update(['user_confirm' => 'confirm']);

   		LinerideBooking::where('id', $get_shuttle_driver->shuttle_driver_id)->update(['seat_available' =>  @$seat_left, 'seat_booked' =>  @$seat_booked ]);

            // Notification For Driver
//            $driver_data['message']       = 'Your Parcel Booking Request Confirmed By '.$user->first_name;
//            $driver_data['type']          = 'confirmed';
//            $driver_data['driver_id']     = $parceldetails->driver_id;
//            $driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($parceldetails,$driver_data));

   		return response()->json(['status'=>'success','message'=> 'You are Successfully Selected Driver!']);
   	}catch (Exception $e) {
   		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
   	}
   }
   
   public function getouttowndriverkmresult($start_l, $start_o, $droup_l, $droup_o, $dateing, $service, $getdriversid, $vehicle_id,$pick_up_location_user,$drop_location_user, $booking_seat, $booking_id){
   	$resultData = User::whereHas('driver_details_settings', function($q) use($service){
   		$q->where('ride_type','=', $service);
   	})->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved','availability_status'=>'on'])->where('vehicle_id',$vehicle_id)->get();
   	$driverDetails=[];
   	$driverDetailsData1 = [];
   	$booking_data = Booking::find($booking_id);
   	foreach ($resultData as $key => $value) {
   		$getdriversidecheckDataAll = \DB::table('out_of_town_booking')->where('booking_date',$dateing)->where('driver_id',$value->id)->where('deleted_at',null)->get();
   		if($getdriversidecheckDataAll->count() > 0){
   			foreach ($getdriversidecheckDataAll as $getdriversidecheckData){
   				if(isset($booking_seat)  && ((int)$getdriversidecheckData->seat_available >= (int)$booking_seat)){
					$start_time = date("H:i", strtotime($booking_data->booking_start_time)); //user start time
                    $end_time = date("H:i", strtotime($booking_data->booking_end_time)); //user end time
                    $check_start_time = date("H:i", strtotime($getdriversidecheckData->booking_start_time));  //driver start time
                    $check_end_time = date("H:i", strtotime($getdriversidecheckData->booking_end_time)); //driver start time
                    $start_date = Carbon::createFromTimeString($booking_data->bstart_date.' '.$start_time);
                    $end_date = Carbon::createFromTimeString($booking_data->bend_date.' '.$end_time);
                    $begin = Carbon::createFromTimeString($getdriversidecheckData->start_date.' '.$check_start_time)->subMinute(30);
                    $end = Carbon::createFromTimeString($getdriversidecheckData->end_date.' '.$check_end_time)->addMinutes(30);
                    if (($start_date <= $end) && ($end_date >= $begin)){
                    // if (($start_date >= $begin) && ($end_date <= $end)){
                    	$driverDetails['driver_id'] = $value->id;
                    	$driverDetails['email'] = $value->email;
                    	$driverDetails['availability_status'] = $value->availability_status;
                    	$driverDetails['out_town_driver_id'] = $getdriversidecheckData->id;

                    	if(!empty($getdriversidecheckData->start_latitude))
                    	{
                    		$driver_latitude = $getdriversidecheckData->start_latitude;
                    	}
                    	if(!empty($getdriversidecheckData->start_longitude)){
                    		$driver_longitude = $getdriversidecheckData->start_longitude;
                    	}
                    	$origin_addresses = $pick_up_location_user;
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
                    		$drop_origin_addresses = $drop_location_user;
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
                    			// $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
                    			$miles = round((float)($getdriversidecheckData->mailes), 1);
                    			$startmiles = (float)$miles - (float)20;
                    			$endmiles = (float)$miles + (float)20;
                    			if((float)($user_distance/1.609344) <= (float)$endmiles){
                    				$driverDetailsData1[] = $driverDetails;
                    			}
                    		}else if(@$drop_distance <= (float)(20) && @$distance <= (float)(20)) {
                                    // $startmiles = (float)$drop_distance - (float)20;
                                    // $endmiles = (float)$drop_distance + (float)20;
                                    // if(((float)$drop_distance >= (float)$startmiles) && ((float)$drop_distance <= (float)$endmiles)){
                    			$driverDetailsData1[] = $driverDetails;
                                    // }
                    		}
                                // else if(($pick_up_location_user ==  $getdriversidecheckData->pick_up_location) &&
                                //     ($booking_data->drop_location ==  $getdriversidecheckData->drop_location) && $drop_distance) {
                                //     $startmiles = (float)$drop_distance - (float)20;
                                //     $endmiles = (float)$drop_distance + (float)20;
                                //     if(((float)$drop_distance >= (float)$startmiles) && ((float)$drop_distance <= (float)$endmiles)){
                                //         $driverDetailsData1[] = $driverDetails;
                                //     }
                                // }
//                                else if($distance && ((float)(20) >= (float)$distance)) {
//                                    $driverDetailsData1[] = $driverDetails;
//                                }
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
                    			// $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
                    			$miles = round((float)($getdriversidecheckData->mailes), 1);
                    			$startmiles = (float)$miles - (float)20;
                    			$endmiles = (float)$miles + (float)20;
                    			if((float)($user_distance / 1.609344) <= (float)$endmiles){
                    				if((int)$getdriversidecheckData->seat_available >= (int)$booking_seat){
                    					$driverDetailsData1[] = $driverDetails;
                    				}
                    			}
                    		}else if(@$drop_distance <= (float)(20) && @$distance <= (float)(20)) {
                                    // $startmiles = (float)$drop_distance - (float)20;
                                    // $endmiles = (float)$drop_distance + (float)20;
                                    // if(((float)$drop_distance >= (float)$startmiles) && ((float)$drop_distance <= (float)$endmiles)){
                    			if((int)$getdriversidecheckData->seat_available >= (int)$booking_seat){
                    				$driverDetailsData1[] = $driverDetails;
                    			}
                                    // }
                    		}
                                // else if(($pick_up_location_user ==  $getdriversidecheckData->pick_up_location) &&
                                //     ($booking_data->drop_location ==  $getdriversidecheckData->drop_location) && $drop_distance) {
                                //     $startmiles = (float)$drop_distance - (float)20;
                                //     $endmiles = (float)$drop_distance + (float)20;
                                //     if(((float)$drop_distance >= (float)$startmiles) && ((float)$drop_distance <= (float)$endmiles)){
                                //         $driverDetailsData1[] = $driverDetails;
                                //     }
                                // }
//                                else if($distance && ((float)(20) >= (float)$distance)) {
//                                    $driverDetailsData1[] = $driverDetails;
//                                }
                    	}
                    }
                }
            }
        }
    }
        // dd($driverDetailsData1);
    return $driverDetailsData1;
}
    // public function getouttowndriverkmresult($start_l, $start_o, $droup_l, $droup_o, $dateing, $service, $getdriversid, $vehicle_id,$pick_up_location_user,$drop_location_user, $booking_seat, $booking_id){
    //     $resultData = User::whereHas('driver_details_settings', function($q) use($service){
    //         $q->where('ride_type','=', $service);
    //     })->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved'])->where('vehicle_id',$vehicle_id)->get();
    //     $driverDetails=[];
    //     $driverDetailsData1 = [];

    //     $booking_data = Booking::find($booking_id);
    //     foreach ($resultData as $key => $value) {
    //         $getdriversidecheckDataAll = OutTwonrideBooking::where('driver_id', $value->id)
    //             ->where('booking_date','=',$dateing)
    //             ->get();

    //         if($getdriversidecheckDataAll->count() > 0){
    //             foreach ($getdriversidecheckDataAll as $getdriversidecheckData){
    //                 if(isset($booking_seat)  && ((int)$getdriversidecheckData->seat_available >= (int)$booking_seat)){
    //                     $start_time = explode(' ', $booking_data->booking_start_time);
    //                     $end_time = explode(' ', $booking_data->booking_end_time);
    //                     $check_start_time = explode(' ', $getdriversidecheckData->booking_start_time);
    //                     $check_end_time = explode(' ', $getdriversidecheckData->booking_end_time);

    //                     $start_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$start_time[0]);
    //                     $end_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$end_time[0]);
    //                     $begin = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_start_time[0]);
    //                     $end   = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_end_time[0]);

    //                     if($start_date->between($begin, $end) || $end_date->between($begin, $end)){
    //                         $driverDetails['driver_id'] = $value->id;
    //                         $driverDetails['email'] = $value->email;
    //                         $driverDetails['availability_status'] = $value->availability_status;
    //                         $driverDetails['out_town_driver_id'] = $getdriversidecheckData->id;

    //                         if(!empty($getdriversidecheckData->start_latitude))
    //                         {
    //                             $driver_latitude = $getdriversidecheckData->start_latitude;
    //                         }
    //                         if(!empty($getdriversidecheckData->start_longitude)){
    //                             $driver_longitude = $getdriversidecheckData->start_longitude;
    //                         }

    //                         $origin_addresses = $pick_up_location_user;
    //                         $destination_addresses = $getdriversidecheckData->pick_up_location;
    //                         $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
    //                         $distance_arr = json_decode($distance_data);
    //                         $elements = $distance_arr->rows[0]->elements;
    //                         if(!empty($elements[0]->distance)){
    //                             $distance = $elements[0]->distance->text;
    //                             $distance = explode(' ', $distance);
    //                             $jk =$distance[0];
    //                             $distance = number_format((float)str_replace(',', '', $jk), 2, '.', '');
    //                             $distance = (float)$distance * 0.621371;
    //                         }else{
    //                             $distance = '';
    //                         }

    //                         if(( $getdriversidecheckData->seat_booked == '' || ( $getdriversidecheckData->seat_booked == '0')) && $booking_data->seats == 'vip' ){
    //                             if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
    //                                 $booking_origin_address = $booking_data->pick_up_location;
    //                                 $booking_end_address = $booking_data->drop_location;
    //                                 $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
    //                                 $userdistance_arr = json_decode($user_distance_data);
    //                                 $elements = $userdistance_arr->rows[0]->elements;
    //                                 $user_distance = $elements[0]->distance->text;
    //                                 $user_distance = explode(' ', $user_distance);
    //                                 $user_distance = round($user_distance[0], 1);
    //                                 $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
    //                                 $startmiles = (float)$miles - (float)20;
    //                                 $endmiles = (float)$miles + (float)20;
    //                                 if((float)$user_distance <= (float)$endmiles){
    //                                     $driverDetailsData1[] = $driverDetails;
    //                                 }
    //                             }
    //                             else if($distance && ((float)(20) >= (float)$distance)) {
    //                                 $driverDetailsData1[] = $driverDetails;
    //                             }
    //                         }else if( $booking_data->taxi_hailing == 'sharing' ){
    //                             if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
    //                                 $booking_origin_address = $booking_data->pick_up_location;
    //                                 $booking_end_address = $booking_data->drop_location;
    //                                 $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
    //                                 $userdistance_arr = json_decode($user_distance_data);
    //                                 $elements = $userdistance_arr->rows[0]->elements;
    //                                 $user_distance = $elements[0]->distance->text;
    //                                 $user_distance = explode(' ', $user_distance);
    //                                 $user_distance = round($user_distance[0], 1);
    //                                 $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
    //                                 $startmiles = (float)$miles - (float)20;
    //                                 $endmiles = (float)$miles + (float)20;
    //                                 if((float)$user_distance <= (float)$endmiles){
    //                                     $driverDetailsData1[] = $driverDetails;
    //                                 }
    //                             }
    //                             else if($distance && ((float)(20) >= (float)$distance)) {
    //                                 $driverDetailsData1[] = $driverDetails;
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }
    //     }   
    //     return $driverDetailsData1;
    // }

//     public function getouttowndriverkmresult($start_l, $start_o, $droup_l, $droup_o, $dateing, $service, $getdriversid, $vehicle_id,$pick_up_location_user,$drop_location_user, $booking_seat, $booking_id){
//         $resultData = User::whereHas('driver_details_settings', function($q) use($service){
//             $q->where('ride_type','=', $service);
//         })->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved'])->where('vehicle_id',$vehicle_id)->get();
//         $driverDetails=[];
//         $driverDetailsData1 = [];

//         $booking_data = Booking::find($booking_id);
//         foreach ($resultData as $key => $value) {
//             $getdriversidecheckDataAll = OutTwonrideBooking::where('driver_id', $value->id)
//                 ->where('booking_date','=',$dateing)
//                 ->get();

//             if($getdriversidecheckDataAll->count() > 0){
//                 foreach ($getdriversidecheckDataAll as $getdriversidecheckData){
//                     if(isset($booking_seat)  && ((int)$getdriversidecheckData->seat_available >= (int)$booking_seat)){
//                         $start_time = explode(' ', $booking_data->booking_start_time);
//                         $end_time = explode(' ', $booking_data->booking_end_time);
//                         $check_start_time = explode(' ', $getdriversidecheckData->booking_start_time);
//                         $check_end_time = explode(' ', $getdriversidecheckData->booking_end_time);

//                         $start_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$start_time[0]);
//                         $end_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$end_time[0]);
//                         $begin = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_start_time[0]);
//                         $end   = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_end_time[0]);

//                         if($start_date->between($begin, $end) || $end_date->between($begin, $end)){
//                             $driverDetails['driver_id'] = $value->id;
//                             $driverDetails['email'] = $value->email;
//                             $driverDetails['availability_status'] = $value->availability_status;
//                             $driverDetails['out_town_driver_id'] = $getdriversidecheckData->id;
//                             if(!empty($getdriversidecheckData->start_latitude))
//                             {
//                                 $driver_latitude = $getdriversidecheckData->start_latitude;
//                             }
//                             if(!empty($getdriversidecheckData->start_longitude)){
//                                 $driver_longitude = $getdriversidecheckData->start_longitude;
//                             }

//                             $origin_addresses = $pick_up_location_user;
//                             $destination_addresses = $getdriversidecheckData->pick_up_location;
//                             $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
//                             $distance_arr = json_decode($distance_data);
//                             $elements = $distance_arr->rows[0]->elements;
//                             if(!empty($elements[0]->distance)){

//                                 $distance = $elements[0]->distance->text;
//                                 $distance = explode(' ', $distance);
//                                 $jk =$distance[0];
//                                 $distance = number_format((float)str_replace(',', '', $jk), 2, '.', '');
//                                 $distance = (float)$distance * 0.621371;

//                                 // $distance = $elements[0]->distance->text;
//                                 // $distance = explode(' ', $distance);
//                                 // $distance = round($distance[0], 2);
//                                 // $distance = (float)$distance * 0.621371;


// //                                $distance = $elements[0]->distance->text;
// //                                $distance = explode(' ', $distance);
// //                                $distance = round($distance[0], 2);
//                             }else{
//                                 $distance = '';
//                             }

//                             if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
//                                 $booking_origin_address = $booking_data->pick_up_location;
//                                 $booking_end_address = $booking_data->drop_location;
//                                 $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
//                                 $userdistance_arr = json_decode($user_distance_data);
//                                 $elements = $userdistance_arr->rows[0]->elements;
//                                 $user_distance = $elements[0]->distance->text;
//                                 $user_distance = explode(' ', $user_distance);
//                                 $user_distance = round($user_distance[0], 1);
//                                 $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
//                                 $startmiles = (float)$miles - (float)20;
//                                 $endmiles = (float)$miles + (float)20;
//                                 if((float)$user_distance <= (float)$endmiles){
//                                     $driverDetailsData1[] = $driverDetails;
//                                 }
//                             }
//                             else if($distance && ((float)(20) >= (float)$distance)) {
//                                 $driverDetailsData1[] = $driverDetails;
//                             }

// //                        if(!empty($getdriversidecheckData->mailes)){
// //                            $distance = explode(' ', $distance);
// //                            $distance = round($distance[0], 2);
// //
// //                            $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 2);
// //                            if((float)$miles >= (float)$distance){
// //                                $driverDetailsData1[] = $driverDetails;
// //                            }
// //                        } else {
// //                            // $driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$start_l,$start_o,'K');
// //                            $driverDetailsData1[] = $driverDetails;
// //                        }
//                         }
//                     }
//                 }
//             }
//         }
//         return $driverDetailsData1;
//     }
// public function getouttowndriverkmresult($start_l, $start_o, $droup_l, $droup_o, $dateing, $service, $getdriversid, $vehicle_id,$pick_up_location_user,$drop_location_user, $booking_seat, $booking_id){
//         // $pick_up_location_user = $user_check_booking->pick_up_location;
//         //         $drop_location_user = $user_check_booking->drop_location;
//         $resultData = User::whereHas('driver_details_settings', function($q) use($service){
//             $q->where('ride_type','=', $service);
//         })->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved'])->where('vehicle_id',$vehicle_id)->get();
//         $driverDetails=[];
//         $driverDetailsData1 = [];

//         $booking_data = Booking::find($booking_id);
//         foreach ($resultData as $key => $value) {
//             $getdriversidecheckData = OutTwonrideBooking::where('driver_id', $value->id)
//                 ->where('booking_date','=',$dateing)
//                 ->first();
//             // echo $getdriversidecheckData;
//             if(!empty($getdriversidecheckData)){
//                 if(isset($booking_seat) && ($booking_seat = 'vip') && ((int)$getdriversidecheckData->seat_available >= (int)$booking_seat)){
//                     $start_time = explode(' ', $booking_data->booking_start_time);
//                     $end_time = explode(' ', $booking_data->booking_end_time);
//                     $check_start_time = explode(' ', $getdriversidecheckData->booking_start_time);
//                     $check_end_time = explode(' ', $getdriversidecheckData->booking_end_time);

//                     $start_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$start_time[0]);
//                     $end_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$end_time[0]);
//                     $begin = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_start_time[0]);
//                     $end   = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_end_time[0]);

//                     if($start_date->between($begin, $end) || $end_date->between($begin, $end)){
//                         $driverDetails['driver_id'] = $value->id;
//                         $driverDetails['email'] = $value->email;
//                         $driverDetails['availability_status'] = $value->availability_status;
//                         $driverDetails['out_town_driver_id'] = $getdriversidecheckData->id;
//                         if(!empty($getdriversidecheckData->start_latitude))
//                         {
//                             $driver_latitude = $getdriversidecheckData->start_latitude;
//                         }
//                         if(!empty($getdriversidecheckData->start_longitude)){
//                             $driver_longitude = $getdriversidecheckData->start_longitude;
//                         }

//                         $origin_addresses = $pick_up_location_user;
//                         $destination_addresses = $getdriversidecheckData->pick_up_location;
//                         $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
//                         // echo $distance_data;
//                         // echo "<pre>";
//                         $distance_arr = json_decode($distance_data);
//                         $elements = $distance_arr->rows[0]->elements;
//                         if(!empty($elements[0]->distance)){
//                             $distance = $elements[0]->distance->text;
//                             // echo $distance;
//                             $distance = explode(' ', $distance);
//                             $distance = round($distance[0], 2);
//                             // dd($distance);
//                             $distance = (float)$distance * 0.621371;
//                             // dd($distance);
//                             // $miles = round(($distance* 0.8684));
//                             // $startmiles = (float)$miles - (float)20;
//                             // $endmiles = (float)$miles + (float)20;
//                         }else{
//                             $distance = '';
//                         }

//                             // echo $distance;
//                         if(!empty($getdriversidecheckData->mailes) && $distance && ((float)(20) >= (float)$distance)){
//                             // dd("in");
//                             $booking_origin_address = $booking_data->pick_up_location;
//                             $booking_end_address = $booking_data->drop_location;
//                             $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
//                             $userdistance_arr = json_decode($user_distance_data);
//                             $elements = $userdistance_arr->rows[0]->elements;
//                             $user_distance = $elements[0]->distance->text;
//                             $user_distance = explode(' ', $user_distance);
//                             $user_distance = round($user_distance[0], 1);
//                             $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
//                             $startmiles = (float)$miles - (float)20;
//                             $endmiles = (float)$miles + (float)20;
//                             if((float)$user_distance <= (float)$endmiles){
//                                 $driverDetailsData1[] = $driverDetails;
//                             }
//                         }
//                         else if((float)(20) >= (float)$distance) {
//                         // else if($pick_up_location_user ==  $getdriversidecheckData->pick_up_location) {
//                             $driverDetailsData1[] = $driverDetails;
//                         }


// //                        if((float)(20) >= (float)$distance) {
// //                            $driverDetailsData1[] = $driverDetails;
// //                        }


// //                        if(!empty($getdriversidecheckData->mailes)){
// //                            $distance = explode(' ', $distance);
// //                            $distance = round($distance[0], 2);
// //
// //                            $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 2);
// //                            if((float)$miles >= (float)$distance){
// //                                $driverDetailsData1[] = $driverDetails;
// //                            }
// //                        } else {
// //                            // $driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$start_l,$start_o,'K');
// //                            $driverDetailsData1[] = $driverDetails;
// //                        }
//                     }
//                 }
//             }
//         }
//         // dd($driverDetailsData1);
//         return $driverDetailsData1;
//     }
//     public function getouttowndriverkmresult($start_l, $start_o, $droup_l, $droup_o, $dateing, $service, $getdriversid, $vehicle_id,$pick_up_location_user,$drop_location_user, $booking_seat, $booking_id){
//         // $pick_up_location_user = $user_check_booking->pick_up_location;
//         //         $drop_location_user = $user_check_booking->drop_location;
//         $resultData = User::whereHas('driver_details_settings', function($q) use($service){
//             $q->where('ride_type','=', $service);
//         })->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved'])->where('vehicle_id',$vehicle_id)->get();
//         $driverDetails=[];
//         $driverDetailsData1 = [];

//         $booking_data = Booking::find($booking_id);
//         foreach ($resultData as $key => $value) {
//             $getdriversidecheckData = OutTwonrideBooking::where('driver_id', $value->id)
//                 ->where('booking_date','=',$dateing)
//                 ->first();

//             if(!empty($getdriversidecheckData)){
//                 if(isset($booking_seat) && ($booking_seat != 'vip') && ((int)$getdriversidecheckData->seat_available >= (int)$booking_seat)){
//                     $start_time = explode(' ', $booking_data->booking_start_time);
//                     $end_time = explode(' ', $booking_data->booking_end_time);
//                     $check_start_time = explode(' ', $getdriversidecheckData->booking_start_time);
//                     $check_end_time = explode(' ', $getdriversidecheckData->booking_end_time);

//                     $start_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$start_time[0]);
//                     $end_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$end_time[0]);
//                     $begin = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_start_time[0]);
//                     $end   = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_end_time[0]);

//                     if($start_date->between($begin, $end) || $end_date->between($begin, $end)){
//                         $driverDetails['driver_id'] = $value->id;
//                         $driverDetails['email'] = $value->email;
//                         $driverDetails['availability_status'] = $value->availability_status;
//                         $driverDetails['out_town_driver_id'] = $getdriversidecheckData->id;
//                         if(!empty($getdriversidecheckData->start_latitude))
//                         {
//                             $driver_latitude = $getdriversidecheckData->start_latitude;
//                         }
//                         if(!empty($getdriversidecheckData->start_longitude)){
//                             $driver_longitude = $getdriversidecheckData->start_longitude;
//                         }

//                         $origin_addresses = $pick_up_location_user;
//                         $destination_addresses = $getdriversidecheckData->pick_up_location;
//                         $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
//                         $distance_arr = json_decode($distance_data);
//                         $elements = $distance_arr->rows[0]->elements;
//                         $distance = $elements[0]->distance->text;
//                         $distance = explode(' ', $distance);
//                         $distance = round($distance[0], 2);

//                         if(!empty($getdriversidecheckData->mailes) && ((float)(20) >= (float)$distance)){
//                             $booking_origin_address = $booking_data->pick_up_location;
//                             $booking_end_address = $booking_data->drop_location;
//                             $user_distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($booking_origin_address).'&destinations='.urlencode($booking_end_address).'&key='.env('GOOGLE_MAP_API_KEY'));
//                             $userdistance_arr = json_decode($user_distance_data);
//                             $elements = $userdistance_arr->rows[0]->elements;
//                             $user_distance = $elements[0]->distance->text;
//                             $user_distance = explode(' ', $user_distance);
//                             $user_distance = round($user_distance[0], 1);
//                             $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 1);
//                             $startmiles = (float)$miles - (float)20;
//                             $endmiles = (float)$miles + (float)20;
//                             if((float)$user_distance >= (float)$startmiles &&  (float)$user_distance <= (float)$endmiles){
//                                 $driverDetailsData1[] = $driverDetails;
//                             }
//                         } else if((float)(20) >= (float)$distance) {
//                             $driverDetailsData1[] = $driverDetails;
//                         }


// //                        if(!empty($getdriversidecheckData->mailes)){
// //                            $distance = explode(' ', $distance);
// //                            $distance = round($distance[0], 2);
// //
// //                            $miles = round((float)($getdriversidecheckData->mailes * 1.609344), 2);
// //                            if((float)$miles >= (float)$distance){
// //                                $driverDetailsData1[] = $driverDetails;
// //                            }
// //                        } else {
// //                            // $driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$start_l,$start_o,'K');
// //                            $driverDetailsData1[] = $driverDetails;
// //                        }
//                     }
//                 }
//             }
//         }
//         return $driverDetailsData1;
//     }
    // public function getouttowndriverkmresult($start_l, $start_o, $droup_l, $droup_o, $dateing, $service, $getdriversid, $vehicle_id,$pick_up_location_user,$drop_location_user, $booking_seat, $booking_id){
    //     // $pick_up_location_user = $user_check_booking->pick_up_location;
    //     //         $drop_location_user = $user_check_booking->drop_location;
    //     $resultData = User::whereHas('driver_details_settings', function($q) use($service){
    //         $q->where('ride_type','=', $service);
    //     })->where(['user_type'=>'driver','status'=>'active','doc_status'=>'approved','vehicle_doc_status'=>'approved'])->where('vehicle_id',$vehicle_id)->get();
    //     $driverDetails=[];
    //     $driverDetailsData1 = [];

    //     $booking_data = Booking::find($booking_id);
    //     foreach ($resultData as $key => $value) {
    //         $getdriversidecheckData = OutTwonrideBooking::where('driver_id', $value->id)
    //             ->where('booking_date','=',$dateing)
    //             ->first();

    //         if(!empty($getdriversidecheckData)){
    //             if(isset($booking_seat) && ($booking_seat != 'vip') && ((int)$getdriversidecheckData->seat_available >= (int)$booking_seat)){
    //                 $start_time = explode(' ', $booking_data->booking_start_time);
    //                 $end_time = explode(' ', $booking_data->booking_end_time);
    //                 $check_start_time = explode(' ', $getdriversidecheckData->booking_start_time);
    //                 $check_end_time = explode(' ', $getdriversidecheckData->booking_end_time);

    //                 $start_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$start_time[0]);
    //                 $end_date = Carbon::createFromTimeString( $booking_data->booking_date.' '.$end_time[0]);
    //                 $begin = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_start_time[0]);
    //                 $end   = Carbon::createFromTimeString( $getdriversidecheckData->booking_date.' '.$check_end_time[0]);

    //                 if($start_date->between($begin, $end) || $end_date->between($begin, $end)){
    //                     $driverDetails['driver_id'] = $value->id;
    //                     $driverDetails['email'] = $value->email;
    //                     $driverDetails['availability_status'] = $value->availability_status;
    //                     $driverDetails['out_town_driver_id'] = $getdriversidecheckData->id;
    //                     if(!empty($getdriversidecheckData->start_latitude))
    //                     {
    //                         $driver_latitude = $getdriversidecheckData->start_latitude;
    //                     }
    //                     if(!empty($getdriversidecheckData->start_longitude)){
    //                         $driver_longitude = $getdriversidecheckData->start_longitude;
    //                     }

    //                     $origin_addresses = $pick_up_location_user;
    //                     $destination_addresses = $drop_location_user;
    //                     $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
    //                     $distance_arr = json_decode($distance_data);
    //                     $elements = $distance_arr->rows[0]->elements;
    //                     $distance = $elements[0]->distance->text;
    //                     if(!empty($getdriversidecheckData->mailes)){
    //                         $distance = explode(' ', $distance);
    //                         $distance = round($distance[0], 2);
    //                         $miles = round((float)$getdriversidecheckData->mailes, 2);
    //                         if((float)$miles >= (float)$distance){
    //                             $driverDetailsData1[] = $driverDetails;
    //                         }
    //                     } else {
    //                         // $driverDetails['distance'] = $this->distanceByLatLong($driver_latitude,$driver_longitude,$start_l,$start_o,'K');
    //                         $driverDetailsData1[] = $driverDetails;
    //                     }
    //                 }
    //             }
    //         }
    //     }
    //     return $driverDetailsData1;
    // }
public function bookingDetails(Request $request){
	$validator = [
	'booking_id'  => 'required',
	];
	$validation = Validator::make($request->all(),$validator);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first()]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$user_booking = Booking::with(['driver'])->where('id',$request->get('booking_id'))->first();
		if(empty($user_booking->driver)){
			$data = new \stdClass();
		}else{
			$data = $user_booking->driver;
			if(!empty($data->first_name)){
				$user_name = @$user_detail->last_name.' '.@$user_detail->first_name;
			} else {
				$user_name = @$user_detail->company_name;
			}
			$pickup_total_km = $this->pickupdistance($user->latitude, $user->longitude, $data->latitude, $data->longitude);
			$data['pickup_total_km'] = number_format((float)$pickup_total_km, 2, '.', '');
			$booking_detail = Booking::where('id',$request->booking_id)->first();
			$data['booking_date'] = $booking_detail->booking_date;
                //User Address
			$lat= $user->latitude;
			$lng= $user->longitude;
			$user_address = $this->getaddress($lat,$lng);
			if($user_address){
				$origin_addresses = $user_address;
			}
                //Driver Address
			$lat= $data->latitude;
			$lng= $data->longitude;
			$driver_address = $this->getaddress($lat,$lng);
			if($driver_address){
				$destination_addresses = $driver_address;
			}
			$destination_addresses = $destination_addresses;
			$origin_addresses = $origin_addresses;
			$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
			$distance_arr = json_decode($distance_data);
			$elements = $distance_arr->rows[0]->elements;
			$distance = $elements[0]->distance->text;
			$duration = $elements[0]->duration->text;

			$avgrating = RatingReviews::where('from_user_id',$data)->orWhere('to_user_id',$data)->avg('rating');
			if(empty($avgrating)){
				$avgrating = 0;
			}

			$data['driver_review'] = $avgrating;
			$data['arrival_time'] = $duration;
			$data['booking_date'] = $user_booking->booking_date .' '. $user_booking->booking_start_time.' '. @$user_booking->booking_end_time;
			$data['pick_up_location'] = $booking_detail->pick_up_location;
			$data['drop_location'] = $booking_detail->drop_location;
			$data['start_latitude'] = $booking_detail->start_latitude;
			$data['start_longitude'] = $booking_detail->start_longitude;
			$data['drop_latitude'] = $booking_detail->drop_latitude;
			$data['drop_longitude'] = $booking_detail->drop_longitude;
			$data['base_fare'] = $booking_detail->base_fare;
			$data['service_id'] = $booking_detail->ride_setting_id;
		}
		return response()->json(['status'  => 'success','booking_status' => $user_booking->trip_status,'message' => 'Booking details getting successfully','data'    => $data]);
	}catch(Exception $e){
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}
}
