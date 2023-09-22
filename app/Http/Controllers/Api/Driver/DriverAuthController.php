<?php
namespace App\Http\Controllers\Api\Driver;
use App\Models\LinerideBooking;
use App\Models\LinerideUserBooking;
use App\Models\ParcelDetail;
use App\Models\ParcelDriver;
use App\Models\ShuttleDriver;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage, Illuminate\Support\Facades\DB, Tymon\JWTAuth\Facades\JWTAuth;
use Event;
use PushNotification;
use App\Models\User;
use App\Models\DriverRiderSetting;
use App\Models\DriverDetails;
use App\Models\Otp;
use App\Models\PasswordReset;
use App\Models\Setting;
use Carbon\Carbon;
use App\Models\CmsPage, App\Models\DriverDocuments, App\Models\Wallet;
use App\Models\Conversions;
use App\Models\RatingReviews;
use App\Models\Promocodes;
use App\Models\Vehicle;
use App\Models\TransactionDetail;
use App\Models\Support;
use App\Models\WalletHistory;
use App\Models\DriverVehicleDocument;
use App\Models\Booking;
use App\Models\EmergencyRequest;
use App\Models\EmergencyType;
use App\Models\OutTwonrideBooking;
use App\Models\Notifications;
use App\Models\PopupNotification, App\Models\RideSetting, App\Models\BookingNotifications, App\Models\UserNotification;
use App\Mail\EmergencyMail;
use Illuminate\Support\Facades\Mail;

class DriverAuthController extends Controller{
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
| Update Profile
|--------------------------------------------------------------------------
|
*/
public function uploadCarDetails(Request $request){
	try{

	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
|
*/
public function updateProfile(Request $request){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}


		$user_data = User::where('id',$user->id)->first();
		if($user_data){
			if($request->hasFile('avatar')){
				$file = $request->file('avatar');
				$extension = $file->getClientOriginalExtension();
				$filename = Str::random(10).'.'.$extension;
				Storage::disk('public')->putFileAs('avatar', $file,$filename);
			}else if($user_data->avatar){
				$filename = $user_data->avatar;
			}else{
				$filename = '';
			}

			$checkcontactexist = User::where('contact_number', request('contact_number'))->first();
			if(!empty($checkcontactexist) && ($checkcontactexist->id !== $user->id)){
				return response()->json(['status' => 'error','message' => 'Contact No already has been taken.']);
			}

			if(request('contact_number')){
				$contact_number = request('contact_number');
			}else{
				$contact_number = $user_data->contact_number;
			}
			if($user_data->driver_signup_as === 'company'){
				$company_name      = @$request->first_name;
			}else{
				$company_name      = @$user_data->company_name;
			}

			$user_data->first_name      = @$request->first_name;
			$user_data->last_name       = @$request->last_name;
			$user_data->company_name    = @$company_name;
			$user_data->address         = request('address');
			$user_data->country         = request('country');
			$user_data->city_id         = request('city_id');
			$user_data->state_id        = request('state_id');
			$user_data->avatar          = $filename;
			$user_data->contact_number  = $contact_number;
			$user_data->add_latitude    = request('add_latitude');
			$user_data->add_longitude   = request('add_longitude');
			$user_data->country_code    = request('country_code');
			$user_data->save();
			$checkdriverdetails=DriverDetails::where('driver_id', $user->id)->first();
			if(!empty($checkdriverdetails)){
			}else{
				DriverDetails::create(['driver_id'       => $user->id,'vehicle_model' => request('vehicle_model'),'vehicle_plate' => request('vehicle_plate'),]);
			}
			return response()->json(['status' => 'success','message' => 'Driver Profile Update Successfully','data' => $user_data]);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Get Driver Profile
|--------------------------------------------------------------------------
|
*/
public function getDriverProfile(Request $request){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$user_data = User::with(['driver_details','state','city'])->where('id',$user->id)->first();
		return response()->json(['status' => 'success','message' => 'Get Driver Details','data' => $user_data]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Driver ride setting
|--------------------------------------------------------------------------
|
*/
public function rideSetting(Request $request){
	$validation_array =[
	'type'       => 'required',
	'weight_limit'     => 'required',
	'min_rate'      => 'required',
	'distance_travel'  => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first(),'data' => (object)[]]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$user_data = DriverRiderSetting::where('driver_id',$user->id)->first();
		if(!empty($user_data)){
			$user_data->driver_id = $user->id;
			$user_data->type = $request->type;
			$user_data->weight_limit = $request->weight_limit;
			$user_data->min_rate = $request->min_rate;
			$user_data->distance_travel = $request->distance_rate;
			$user_data->save();
		}else{
			$user_data = DriverRiderSetting::create([
				'driver_id' => $user->id,
				'type' => $request->type,
				'weight_limit' => $request->weight_limit,
				'min_rate' => $request->min_rate,
				'distance_travel' => $request->distance_rate,
				]);
		}
		return response()->json(['status' => 'success','message' => 'Ride Setting Saved Successfully','data' => $user_data]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Create Driver Conversions
|--------------------------------------------------------------------------
|
*/
public function CreateDriverConversions(Request $request){
	$validation_array =[
	'sent_from'       => 'required',
	'sent_to'     => 'required',
	'message'      => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first(),'data' => (object)[]]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$data['sent_from']=request('sent_from');
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
| Create Driver Rating Reviews
|--------------------------------------------------------------------------
*/
public function CreateDriverRatingReviews(Request $request){
	$validation_array =[
	'rating'          => 'required',
	'to_user_id'      => 'required',
        // 'booking_id'      => 'required',
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
		$data['from_user_id']    =   $user->id;
		$data['to_user_id']      =   request('to_user_id');
		$data['rating']          =   request('rating');
		if($request->booking_id){
			$data['booking_id']      =   request('booking_id');
		}else if($request->parcel_id){
			$data['parcel_id']      =   request('parcel_id');
		}else if($request->shuttle_id){
			$data['shuttle_id']      =   request('shuttle_id');
		}
		$data['booking_id']      =   request('booking_id');
		$data['is_read_user']    =   "read";
		$data['comment']         =   request('comment');
		$data['behaviors_id']    =   request('behaviors_id');
		$userratingreviews = RatingReviews::Create($data);
		return response()->json(['status' => 'success','message' => 'You are successfully User Rating Reviews!','data' => $data]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Driver Rating Reviews Status UnRead And Read
|--------------------------------------------------------------------------
|
*/
public function DriverRatingReviewsStatus(Request $request){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$data = RatingReviews::find($request->id);
		if($data){
			$data->is_read_driver='read';
			$data->save();
			return response()->json(['status' => 'success','message' => 'You are successfully User Rating Reviews!','data' => $data]);
		}
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Driver Create upload documents
|--------------------------------------------------------------------------
|
*/
public function CreateuploadDriverDocument(Request $request){
	$validation_array =[
	'ssn_no'                => 'nullable',
	'licence_doc'           => 'nullable',
	'drivingreport_doc'     => 'nullable',
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
		$checkdriver = $user->vehicle_id;
		if(empty(Vehicle::find($user->vehicle_id)) ){
			return response()->json(['status' => 'error','message' => 'Driver Vehicle Not Found']);
		}

		$checkdriver_licence = DriverDocuments::where('driver_id', $user->id)
		->where('doc_type', 'licence')->first();
		$checkdriver_drivingreport = DriverDocuments::where('driver_id', $user->id)
		->where('doc_type', 'driving_report')->first();

		if($checkdriver){
			$ssn_no='';

			if(!empty($request->licence_doc)){
				foreach ($request->licence_doc as $key => $docs) {
					$file = $docs;
					$extension = $file->getClientOriginalExtension();
					$filename = Str::random(10).'.'.$extension;
					Storage::disk('public')->putFileAs('driver_documents', $file,$filename);

					if(!empty($checkdriver_licence)){
						DriverDocuments::where('id', $checkdriver_licence->id)->update([
							'doc_image'      => $filename,
							'ssn_no'         => $request->ssn_no,
							'doc_name'       => $docs->getClientOriginalName(),
							]);
					}else{
						DriverDocuments::create([
							'driver_id'      => $user->id,
							'doc_image'      => $filename,
							'vehicle_id'     => $checkdriver,
							'doc_type'       => 'licence',
							'ssn_no'         => $request->ssn_no,
							'doc_name'       => $docs->getClientOriginalName(),
							]);
					}
				}
			}else if($request->ssn_no && !empty($checkdriver_licence)){
				DriverDocuments::where('id', $checkdriver_licence->id)->update([
					'ssn_no'         => $request->ssn_no ,
					]);
			}

			if(!empty($request->drivingreport_doc)){
				foreach ($request->drivingreport_doc as $key => $docs) {
					$file = $docs;
					$extension = $file->getClientOriginalExtension();
					$filename = Str::random(10).'.'.$extension;
					Storage::disk('public')->putFileAs('driver_documents', $file,$filename);

					if(!empty($checkdriver_drivingreport)){
						DriverDocuments::where('id', $checkdriver_drivingreport->id)->update([
							'doc_image'      => $filename,
							'ssn_no'         => $request->ssn_no,
							'doc_name'       => $docs->getClientOriginalName(),
							]);
					}else{
						DriverDocuments::create([
							'driver_id'  => $user->id,
							'doc_image'  => $filename,
							'vehicle_id' => $checkdriver,
							'doc_type'   => 'driving_report',
							'ssn_no'         => $request->ssn_no,
							'doc_name'       => $docs->getClientOriginalName(),
							]);
					}
				}
			}else if($request->ssn_no && !empty($checkdriver_drivingreport)){
				DriverDocuments::where('id', $checkdriver_drivingreport->id)->update([
					'ssn_no'         => $request->ssn_no ,
					]);
			}


			User::where('id', $user->id)->update(['driver_doc' => '1']);
			$alldocs = DriverDocuments::where('driver_id',$user->id)->get();
			return response()->json(['status' => 'success','message' => 'Driver Documents Uploaded Successfully','data'=>$alldocs]);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Vehicle Create upload documents
|--------------------------------------------------------------------------
|
*/
public function CreateuploadVehicleDocument(Request $request){
	$validation_array =[
	'vehcile_registration_doc'   => 'nullable',
	'vehcile_registration_doc'   => 'nullable',
	'vehcile_odometer_doc'       => 'nullable'
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
		$checkdriver = $user->vehicle_id;
		if(empty(Vehicle::find($user->vehicle_id)) ){
			return response()->json(['status' => 'error','message' => 'Vehicle Not Found']);
		}
		if($checkdriver){
			$checkvehicle_reg = DriverVehicleDocument::where('driver_id', $user->id)
			->where('doc_type', 'registration')->first();
			$checkvehicle_ins = DriverVehicleDocument::where('driver_id', $user->id)
			->where('doc_type', 'insurance')->first();
			$checkvehicle_odo = DriverVehicleDocument::where('driver_id', $user->id)
			->where('doc_type', 'odometer')->first();

			if(!empty($request->vehcile_registration_doc)){
				foreach ($request->vehcile_registration_doc as $key => $docs) {
					$file = $docs;
					$extension = $file->getClientOriginalExtension();
					$filename = Str::random(10).'.'.$extension;
					Storage::disk('public')->putFileAs('vehicle_documents', $file,$filename);

					if(!empty($checkvehicle_reg)){
						DriverVehicleDocument::where('id', $checkvehicle_reg->id)->update([
							'document_doc'          => $filename,
							'document_name'         => $docs->getClientOriginalName(),
							]);
					}else{
						DriverVehicleDocument::create([
							'driver_id'             => $user->id,
							'document_doc'          => $filename,
							'vehicle_id'            => $checkdriver,
							'doc_type'              => 'registration',
							'document_name'         => $docs->getClientOriginalName(),
							]);
					}
				}
			}
			if(!empty($request->vehcile_insurance_doc)){
				foreach ($request->vehcile_insurance_doc as $key => $docs) {
					$file = $docs;
					$extension = $file->getClientOriginalExtension();
					$filename = Str::random(10).'.'.$extension;
					Storage::disk('public')->putFileAs('vehicle_documents', $file,$filename);

					if(!empty($checkvehicle_ins)){
						DriverVehicleDocument::where('id', $checkvehicle_ins->id)->update([
							'document_doc'          => $filename,
							'document_name'         => $docs->getClientOriginalName(),
							]);
					}else{
						DriverVehicleDocument::create([
							'driver_id'             => $user->id,
							'document_doc'          => $filename,
							'vehicle_id'            => $checkdriver,
							'doc_type'              => 'insurance',
							'document_name'         => $docs->getClientOriginalName(),
							]);
					}
				}
			}
			if(!empty($request->vehcile_odometer_doc)){
				if(!empty($checkvehicle_odo)){
					DriverVehicleDocument::where('driver_id', $user->id)
					->where('doc_type', 'odometer')->delete();
				}
				foreach ($request->vehcile_odometer_doc as $key => $docs) {
					$file = $docs;
					$extension = $file->getClientOriginalExtension();
					$filename = Str::random(10).'.'.$extension;
					Storage::disk('public')->putFileAs('vehicle_documents', $file,$filename);

					DriverVehicleDocument::create([
						'driver_id'             => $user->id,
						'document_doc'          => $filename,
						'vehicle_id'            => $checkdriver,
						'doc_type'              => 'odometer',
						'document_name'         => $docs->getClientOriginalName(),
						]);
				}
			}
			User::where('id', $user->id)->update(['car_doc' => '1']);
			$alldocs = DriverVehicleDocument::where('driver_id',$user->id)->get();
			return response()->json(['status' => 'success','message' => 'Vehicle Documents Uploaded Successfully','data'=>$alldocs]);
		}else{
			return response()->json(['status' => 'error','message' => 'Users Vehicle Not Found']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Update Driver Availability
|--------------------------------------------------------------------------
|
*/
public function updateDriverAvailability(Request $request){
	$validation_array =[
	'status' => 'required'
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
		User::where('id', $user->id)->update(['availability_status' => $request->status]);
		$userstatus = User::find($user->id);
		return response()->json(['status' => 'success','message' => 'Driver Status Updated Successfully','user_status'=>$userstatus->availability_status]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Get Driver Availability
|--------------------------------------------------------------------------
|
*/
public function getDriverAvailability(Request $request){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$availability = User::where('id', $user->id)->first();
		$data['availability_status'] = $availability->availability_status;
		return response()->json(['status' => 'success','message' => 'Driver Status Updated Successfully','data'=>$data]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Driver Trip Driver Arrived, Completed, Accepted
|--------------------------------------------------------------------------
|
*/
public function driverStartTrip(Request $request){
	$validation_array =[
	'otp'           => 'nullable',
	'booking_id'    => 'nullable'
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
			if(!empty($request->otp)){
				$tripdetail = Booking::where('id', $trip_id)->where('otp',$request->otp)->first();    
			} else {
				$tripdetail = Booking::where('id', $trip_id)->first();    
			}
			if(!empty($tripdetail)){
				if($tripdetail->trip_status === 'on_going'){
					return response()->json(['status' => 'error','message' => 'Your Ride already started']);
				}
				Booking::where('id', $tripdetail->id)->update([
					'trip_status'  =>  'on_going',
					'start_time'   =>  Carbon::now()->format('H:i'),
					]);
				$user_data = User::where('id',$tripdetail->user_id)->first();
                // Notification For User
				$data['message']   =  $user->first_name.' '.$user->last_name.' has picked you up from your pick up location';
				$data['type']      =  'trip_ongoing';
				$data['user_id']   =   $tripdetail->user_id;
				$data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_data,$data));
				PopupNotification::create([
					'from_user_id' => $user->id,
					'to_user_id' => $tripdetail->user_id,
					'title' => 'Ride Start Now',
					'description' => 'Ride Start Now',
					'date' => Carbon::now()->format('Y-m-d'),
					'time' => Carbon::now()->format('H:i'),
					'booking_id' => $tripdetail->id,
					]);
				UserNotification::where('booking_id', $trip_id)->where('sent_to_user',$tripdetail->driver_id)->update([
					'sent_from_user' =>$tripdetail->user_id,
					'sent_to_user' => $tripdetail->driver_id,
					'notification_for' => "on_going",
					'title' => "Booking On Going",
					'description' => "Your Booking On Going Successfully",
					'admin_flag' => "0",
					]);
				return response()->json(['status' => 'success','message' => 'Your Ride start Now']);
			}else{
				return response()->json(['status' => 'error','message' => 'Your otp for this trip not matched']);
			}
		}else if($request->parcel_id){
			$tripdetail = ParcelDetail::where('id', $request->parcel_id)->where('otp',$request->otp)->first();
			if(!empty($tripdetail)){
				if($tripdetail->parcel_status === 'on_going'){
					return response()->json(['status' => 'error','message' => 'Your Parcel Ride already started']);
				}
				ParcelDetail::where('id', $tripdetail->id)->update([
					'parcel_status'  =>  'on_going',
					'start_time'     =>  Carbon::now()->format('H:i'),
					]);
                // Notification For User
				$data['message']   =  'Your Parcel Ride start Now';
				$data['type']      =  'trip_ongoing';
				$data['user_id']   =   $tripdetail->user_id;
				$data['notification'] = Event::dispatch('send-notification-assigned-user',array($tripdetail,$data));
				PopupNotification::create([
					'from_user_id' => $user->id,
					'to_user_id' => $tripdetail->user_id,
					'title' => 'Parcel Ride Start Now',
					'description' => 'Parcel Ride Start Now',
					'date' => Carbon::now()->format('Y-m-d H:i'),
					'time' => Carbon::now()->format('H:i'),
					'parcel_id' => $tripdetail->id,
					]);
				return response()->json(['status' => 'success','message' => 'Your Parcel Ride start Now']);
			}else{
				return response()->json(['status' => 'error','message' => 'Your otp for this trip not matched']);
			}
		}else if($request->shuttle_id){
			$tripdetail = LinerideBooking::where('id', $request->shuttle_id)->where('trip_status', 'on_going')->first();

			if(!empty($tripdetail)){
				if($request->user_id){
					LinerideUserBooking::where('shuttle_driver_id', $request->shuttle_id)
					->where('user_id', $request->user_id)
					->update(['trip_status'=> 'on_going', 'start_time'=>Carbon::now()->format('H:i A')]);
					return response()->json(['status' => 'success','message' => 'Shuttle Ride for User start Now']);
				}else{
					return response()->json(['status' => 'error','message' => 'Please Select User']);
				}
			}else{
				LinerideBooking::where('id', $request->shuttle_id)
				->update(['trip_status'=> 'on_going', 'start_time'=>Carbon::now()->format('H:i A')]);
				return response()->json(['status' => 'success','message' => 'Driver shuttle Ride started']);
			}
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Driver Booking Status
|--------------------------------------------------------------------------
|
*/
// public function driverBookingStatus(Request $request){
//     $validation_array =[
//         'booking_id'       => 'required',
//         'status'        => 'required',
//         'out_town_driver_id'        => 'nullable'
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
//         $trip_id = $request->booking_id;
//         $tripdetail = Booking::where('id', $trip_id)->first();
//         $to_time =  strtotime("now");
//         $from_time = strtotime($tripdetail->created_at);
//         $diff = round(abs($to_time - $from_time) / 60,2);
//         if($diff >= 2){
//             $data = new \stdClass();
//             return response()->json(['status' => 'error','message' => 'Notification time out !!']);
//         }
//         if(!empty($tripdetail)){
//             if($request->status === 'completed'){
//                 Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status, 'end_time'=>Carbon::now()->format('H:i')]);
//                 // Notification For Driver
//                 $driver_data['message']       = 'Ride '.ucfirst($request->status);
//                 $driver_data['type']          = $request->status;
//                 $driver_data['driver_id']     = $tripdetail->driver_id;
//                 $driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($tripdetail,$driver_data));
//             }elseif($request->status === 'accepted'){
//                 $updateBookingData = Booking::where('id',$tripdetail->id)->first();
//                 if(!empty($updateBookingData) && ($updateBookingData->driver_id == null)){
//                     $updateBookingData->trip_status = $request->status;
//                     $updateBookingData->driver_id = $user->id;
//                     $updateBookingData->save();
//                     $booking_data = Booking::where('id', $tripdetail->id)->first();
//                     $user_data = User::where('id', $booking_data->user_id)->first();
//                     $driver_one_data = User::where('id', $booking_data->driver_id)->first();

//                     if($booking_data->ride_setting_id == '2'){
// //                        $outTownBooking = OutTwonrideBooking::where('driver_id', $updateBookingData->driver_id)
// //                        ->where('booking_date', $updateBookingData->booking_date)->first();
//                         $outTownBooking = OutTwonrideBooking::where('id',$request->out_town_driver_id)->first();

//                         if(!empty($outTownBooking) && isset($updateBookingData->seats) && ($updateBookingData->seats != 'vip')){
//                             $seat_left = (int)$outTownBooking->seat_available - (int)$updateBookingData->seats;
//                             if((int)$outTownBooking->seat_booked > 0){
//                                 $seat_booked = (int)$outTownBooking->seat_booked + (int)$updateBookingData->seats;
//                             }else{
//                                 $seat_booked = 0;
//                             }
//                             $seat_left = (int)$outTownBooking->seat_available - (int)$updateBookingData->seats;
//                             $outTownBooking->seat_available = @$seat_left;
//                             $outTownBooking->seat_booked = @$seat_booked;
//                             $outTownBooking->save();
//                         }
//                         if($request->out_town_driver_id){
//                             $booking_data->out_town_id = @$request->out_town_driver_id;
//                             $booking_data->save();
//                         }
//                     }


//                     UserNotification::where('booking_id', $tripdetail->id)->update([
//                         'sent_from_user' => $updateBookingData->user_id,
//                         'sent_to_user' => $updateBookingData->driver_id,
//                         'notification_for' => "accepted",
//                         'title' => "Booking Accepted",
//                         'description' => "Your Booking Accepted Successfully",
//                         'admin_flag' => "0",
//                     ]);

//                     $driver_data['message']       = $driver_one_data->first_name. ' has '.ucfirst($request->status). ' your Ride ';
//                     $driver_data['booking_type']  = $updateBookingData->booking_type;
//                     $driver_data['type']          = $request->status;
//                     $driver_data['booking_id']    = (string)$booking_data->id;
//                     $driver_data['driver_id']     =  $booking_data->driver_id;
//                     $driver_data['title']         =  'RueRun';
//                     $driver_data['sound']         = 'default';
//                     $driver_data['notification']  = Event::dispatch('send-notification-assigned-user',array($user_data,$driver_data));
//                 }else{
//                     return response()->json(['status' => 'error','message' => 'This booking is already accepted by another Driver.']);
//                 }
//             }else{
//                 Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status]);
//             }
//             if($request->status === 'driver_arrived'){
//                 $msg='Driver has been Arrived';
//             }else{
//                 $msg='Your Trip '.ucfirst($request->status).' Successfully';
//             }
//             PopupNotification::create([
//                 'from_user_id' => $user->id,
//                 'to_user_id' => $tripdetail->user_id,
//                 'title' => $msg,
//                 'description' => $msg,
//                 'date' => Carbon::now()->format('Y-m-d'),
//                 'time' => Carbon::now()->format('H:i'),
//                 'booking_id' => $tripdetail->id,
//             ]);
//             if(!empty($user_data)){
//                 $MsgData = $user_data;
//             }else{
//                 $data = new \stdClass();
//                 $MsgData = $data;
//             }
//             return response()->json(['status' => 'success','message' =>$msg , 'data'=>Booking::find($trip_id), 'userdata' =>$MsgData]);
//         }else{
//             return response()->json(['status' => 'error','message' => 'Something went Wrong']);
//         }
//     } catch (Exception $e) {
//         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
//     }
// }
// public function driverBookingStatus(Request $request){
//     $validation_array =[
//         'booking_id'       => 'required',
//         'status'        => 'required',
//         'out_town_driver_id'        => 'nullable'
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
//         $trip_id = $request->booking_id;
//         $tripdetail = Booking::where('id', $trip_id)->first();
//         $to_time =  strtotime("now");
//         $from_time = strtotime($tripdetail->created_at);
//         // $diff = round(abs($to_time - $from_time) / 60,2);
//         $diff = round(abs($to_time - $from_time) / 60,60);
//         if($diff >= 60){
//             $data = new \stdClass();
//             return response()->json(['status' => 'error','message' => 'Notification time out !!']);
//         }
//         if(!empty($tripdetail)){
//             if($request->status === 'completed'){
//                 Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status, 'end_time'=>Carbon::now()->format('H:i')]);
//                 // Notification For Driver
//                 $driver_data['message']       = 'Ride '.ucfirst($request->status);
//                 $driver_data['type']          = $request->status;
//                 $driver_data['driver_id']     = $tripdetail->driver_id;
//                 $driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($tripdetail,$driver_data));
//             }elseif($request->status === 'accepted'){
//                 $updateBookingData = Booking::where('id',$tripdetail->id)->first();
//                 if(!empty($updateBookingData) && ($updateBookingData->driver_id == null)){
//                     $updateBookingData->trip_status = $request->status;
//                     $updateBookingData->driver_id = $user->id;
//                     $updateBookingData->save();
//                     $booking_data = Booking::where('id', $tripdetail->id)->first();
//                     $user_data = User::where('id', $booking_data->user_id)->first();
//                     $driver_one_data = User::where('id', $booking_data->driver_id)->first();

//                     if($booking_data->ride_setting_id == '2'){
//                         $outTownBooking = OutTwonrideBooking::where('driver_id', $booking_data->driver_id)
//                         ->where('booking_date', $updateBookingData->booking_date)->first();

//                         if(!empty($outTownBooking) && isset($updateBookingData->seats) && ($updateBookingData->seats != 'vip')){

//                             $seat_left = (int)$outTownBooking->seat_available - (int)$updateBookingData->seats;
//                             if((int)$outTownBooking->seat_booked > 0){
//                                 $seat_booked = (int)$outTownBooking->seat_booked + (int)$updateBookingData->seats;
//                             }else{
//                                 $seat_booked = 0;
//                             }
//                             $seat_left = (int)$outTownBooking->seat_available - (int)$updateBookingData->seats;
//                             $outTownBooking->seat_available = @$seat_left;
//                             $outTownBooking->seat_booked = @$seat_booked;
//                             $outTownBooking->save();
//                         }else if($updateBookingData->seats == 'vip'){
//                             $outTownBooking->seat_booked = @$outTownBooking->seat_available;
//                             $outTownBooking->seat_available = '0';
//                             $outTownBooking->save();
//                         }

//                         if($request->out_town_driver_id){
//                             $updateBookingData->out_town_id = @$request->out_town_driver_id;
//                             $updateBookingData->save();
//                         }
//                     }

//                     // UserNotification::where('booking_id', $tripdetail->id)->update([
//                     //     'sent_from_user' => $updateBookingData->user_id,
//                     //     'sent_to_user' => $updateBookingData->driver_id,
//                     //     'notification_for' => "accepted",
//                     //     'title' => "Booking Accepted",
//                     //     'description' => "Your Booking Accepted Successfully",
//                     //     'admin_flag' => "0",
//                     // ]);
//                     UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$updateBookingData->driver_id)->update([
//                         'sent_from_user' => $updateBookingData->user_id,
//                         'sent_to_user' => $updateBookingData->driver_id,
//                         'notification_for' => "accepted",
//                         'title' => "Booking Accepted",
//                         'description' => "Your Booking Accepted Successfully",
//                         'admin_flag' => "0",
//                     ]);

//                     $driver_data['message']       = $driver_one_data->first_name. ' has '.ucfirst($request->status). ' your Ride ';
//                     $driver_data['booking_type']  = $updateBookingData->booking_type;
//                     $driver_data['type']          = $request->status;
//                     $driver_data['booking_id']    = (string)$booking_data->id;
//                     $driver_data['driver_id']     =  $booking_data->driver_id;
//                     $driver_data['title']         =  'RueRun';
//                     $driver_data['sound']         = 'default';
//                     $driver_data['notification']  = Event::dispatch('send-notification-assigned-user',array($user_data,$driver_data));
//                 }else{
//                     return response()->json(['status' => 'error','message' => 'This booking is already accepted by another Driver.']);
//                 }
//             }else{
//                 Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status]);
//             }
//             if($request->status === 'driver_arrived'){
//                 $msg='Driver has been Arrived';
//             }else{
//                 $msg='Your Trip '.ucfirst($request->status).' Successfully';
//             }
//             PopupNotification::create([
//                 'from_user_id' => $user->id,
//                 'to_user_id' => $tripdetail->user_id,
//                 'title' => $msg,
//                 'description' => $msg,
//                 'date' => Carbon::now()->format('Y-m-d'),
//                 'time' => Carbon::now()->format('H:i'),
//                 'booking_id' => $tripdetail->id,
//             ]);
//             if(!empty($user_data)){
//                 $MsgData = $user_data;
//             }else{
//                 $data = new \stdClass();
//                 $MsgData = $data;
//             }
//             return response()->json(['status' => 'success','message' =>$msg , 'data'=>Booking::find($trip_id), 'userdata' =>$MsgData]);
//         }else{
//             return response()->json(['status' => 'error','message' => 'Something went Wrong']);
//         }
//     } catch (Exception $e) {
//         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
//     }
// }

// public function driverBookingStatus(Request $request){
//     $validation_array =[
//         'booking_id'       => 'required',
//         'status'        => 'required',
//         'out_town_driver_id'        => 'nullable'
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
//         $trip_id = $request->booking_id;
//         $tripdetail = Booking::where('id', $trip_id)->first();
//         $to_time =  strtotime("now");
//         $from_time = strtotime($tripdetail->created_at);
//         $diff = round(abs($to_time - $from_time) / 60,60);
//         if($diff >= 60){
//             $data = new \stdClass();
//             return response()->json(['status' => 'error','message' => 'Notification time out !!']);
//         }
//         if(!empty($tripdetail)){
//             if($request->status === 'completed'){
//                 Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status, 'end_time'=>Carbon::now()->format('H:i')]);

//                 UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$tripdetail->driver_id)->update([
//                         'sent_from_user' => $tripdetail->user_id,
//                         'sent_to_user' => $tripdetail->driver_id,
//                         'notification_for' => "completed",
//                         'title' => "Booking Completed",
//                         'description' => "Your Booking completed Successfully",
//                         'admin_flag' => "0",
//                     ]);
//                 // Notification For Driver
//                 $driver_data['message']       = 'Ride '.ucfirst($request->status);
//                 $driver_data['type']          = $request->status;
//                 $driver_data['driver_id']     = $tripdetail->driver_id;
//                 $driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($tripdetail,$driver_data));
//             }elseif($request->status === 'accepted'){
//                 $updateBookingData = Booking::where('id',$tripdetail->id)->first();
//                 if(!empty($updateBookingData) && ($updateBookingData->driver_id == null)){
//                     $updateBookingData->trip_status = $request->status;
//                     $updateBookingData->driver_id = $user->id;
//                     $updateBookingData->save();
//                     $booking_data = Booking::where('id', $tripdetail->id)->first();
//                     $user_data = User::where('id', $booking_data->user_id)->first();
//                     $driver_one_data = User::where('id', $booking_data->driver_id)->first();

//                     if($booking_data->ride_setting_id == '2'){
//                         // $outTownBooking = OutTwonrideBooking::where('driver_id', $booking_data->driver_id)
//                         // ->where('booking_date', $updateBookingData->booking_date)->first();
//                         $outTownBooking = OutTwonrideBooking::where('id',$request->out_town_driver_id)->first();

//                         if(!empty($outTownBooking) && isset($updateBookingData->seats) && ($updateBookingData->seats != 'vip')){

//                             $seat_left = (int)$outTownBooking->seat_available - (int)$updateBookingData->seats;
//                             if((int)$outTownBooking->seat_booked > 0){
//                                 $seat_booked = (int)$outTownBooking->seat_booked + (int)$updateBookingData->seats;
//                             }else{
//                                 $seat_booked = 0;
//                             }
//                             $seat_left = (int)$outTownBooking->seat_available - (int)$updateBookingData->seats;
//                             $outTownBooking->seat_available = @$seat_left;
//                             $outTownBooking->seat_booked = @$seat_booked;
//                             $outTownBooking->save();
//                         }else if($updateBookingData->seats == 'vip'){
//                             $outTownBooking->seat_booked = @$outTownBooking->seat_available;
//                             $outTownBooking->seat_available = '0';
//                             $outTownBooking->save();
//                         }

//                         if($request->out_town_driver_id){
//                             $updateBookingData->out_town_id = @$request->out_town_driver_id;
//                             $updateBookingData->save();
//                         }
//                     }

//                     UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$updateBookingData->driver_id)->update([
//                         'sent_from_user' => $updateBookingData->user_id,
//                         'sent_to_user' => $updateBookingData->driver_id,
//                         'notification_for' => "accepted",
//                         'title' => "Booking Accepted",
//                         'description' => "Your Booking Accepted Successfully",
//                         'admin_flag' => "0",
//                     ]);

//                     $driver_data['message']       = $driver_one_data->first_name. ' has '.ucfirst($request->status). ' your Ride ';
//                     $driver_data['booking_type']  = $updateBookingData->booking_type;
//                     $driver_data['type']          = $request->status;
//                     $driver_data['booking_id']    = (string)$booking_data->id;
//                     $driver_data['driver_id']     =  $booking_data->driver_id;
//                     $driver_data['title']         =  'RueRun';
//                     $driver_data['sound']         = 'default';
//                     $driver_data['notification']  = Event::dispatch('send-notification-assigned-user',array($user_data,$driver_data));
//                 }else{
//                     return response()->json(['status' => 'error','message' => 'This booking is already accepted by another Driver.']);
//                 }
//             }elseif($request->status === 'reject'){
//                 $user_noyofiction = UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$user->id)->first();
//                 $user_nofiction_dele = UserNotification::where('id', $user_noyofiction->id)->first();
//                 $user_nofiction_dele->delete();
//             }else{
//                 Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status]);
//             }
//             if($request->status === 'driver_arrived'){
//                 $msg='Driver has been Arrived';
//             }else{
//                 $msg='Your Trip '.ucfirst($request->status).' Successfully';
//             }
//             PopupNotification::create([
//                 'from_user_id' => $user->id,
//                 'to_user_id' => $tripdetail->user_id,
//                 'title' => $msg,
//                 'description' => $msg,
//                 'date' => Carbon::now()->format('Y-m-d'),
//                 'time' => Carbon::now()->format('H:i'),
//                 'booking_id' => $tripdetail->id,
//             ]);
//             if(!empty($user_data)){
//                 $MsgData = $user_data;
//             }else{
//                 $data = new \stdClass();
//                 $MsgData = $data;
//             }
//             return response()->json(['status' => 'success','message' =>$msg , 'data'=>Booking::find($trip_id), 'userdata' =>$MsgData]);
//         }else{
//             return response()->json(['status' => 'error','message' => 'Something went Wrong']);
//         }
//     } catch (Exception $e) {
//         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
//     }
// }
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
public function driverBookingStatus(Request $request){
	$validation_array =[
	'booking_id'       => 'required',
	'status'        => 'required',
	'out_town_driver_id'        => 'nullable'
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
		$trip_id = $request->booking_id;
		$tripdetail = Booking::where('id', $trip_id)->first();
        // $to_time =  strtotime("now");
        // $from_time = strtotime($tripdetail->created_at);
        // $diff = round(abs($to_time - $from_time) / 60,60);
        // if($diff >= 60){
        //     $data = new \stdClass();
        //     return response()->json(['status' => 'error','message' => 'Notification time out !!']);
        // }
		if(!empty($tripdetail)){
			if($request->status === 'completed'){
				Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status, 'end_time'=>Carbon::now()->format('H:i')]);

				$UserNotification_checking = UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$user->driver_id)->first();
				UserNotification::where('id', $UserNotification_checking->id)->update([
					'sent_from_user' => $tripdetail->user_id,
					'sent_to_user' => $tripdetail->driver_id,
					'notification_for' => "completed",
					'title' => "Booking Completed",
					'description' => "Your Booking completed Successfully",
					'admin_flag' => "0",
					]);
                // Notification For Driver
				/*$driver_data = User::where('id', $tripdetail->driver_id)->first();
				$driver_data['message']       = 'Ride '.ucfirst($request->status);
				$driver_data['type']          = $request->status;
				$driver_data['driver_id']     = $tripdetail->driver_id;
				$driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($driver_data,$tripdetail));*/
				
			}elseif($request->status === 'accepted'){
				$updateBookingData = Booking::where('id',$tripdetail->id)->first();
				if(!empty($updateBookingData) && ($updateBookingData->driver_id == null)){
                    // dd($user->latitude,$user->longitude);
					$checking = User::where('id',$updateBookingData->user_id)->first();
                    // dd($checking->latitude,$checking->longitude);


                    $lat_user= $updateBookingData->start_latitude; //latitude
                    $lng_user= $updateBookingData->start_longitude; //longitude
                    $user_address = $this->getaddress($lat_user,$lng_user);

                    // if(!empty($request->out_town_driver_id)){
                    //     $Driver_outTownBooking = OutTwonrideBooking::where('id',$request->out_town_driver_id)->first();
                    //     $lat_driver= $Driver_outTownBooking->start_latitude; //latitude
                    //     $lng_driver= $Driver_outTownBooking->start_longitude; //longitude
                    //     $driver_address = $this->getaddress($lat_driver,$lng_driver);
                    // } else {
                    //     $lat_driver= $user->latitude; //latitude
                    //     $lng_driver= $user->longitude; //longitude
                    //     $driver_address = $this->getaddress($lat_driver,$lng_driver);
                    // }

                    if($updateBookingData->ride_setting_id == '1'){
                    	$lat_driver= $user->latitude;
                    	$lng_driver= $user->longitude;
                    	$driver_address = $this->getaddress($lat_driver,$lng_driver);
                    }

                    
                    if($updateBookingData->ride_setting_id == '2'){
                    	$Check_Driver_Notifiction = UserNotification::where('booking_id',$request->booking_id)->where('sent_to_user',$user->id)->first();
                    	$outTownBooking = OutTwonrideBooking::where('id',$Check_Driver_Notifiction->out_of_town)->first();
                    	if(empty($outTownBooking)){
                    		return response()->json(['status' => 'error','message' => 'Sorry! Driver Schedule is no longer available. Please request or create another ride request.']);
                    	}
                    	$Driver_outTownBooking = OutTwonrideBooking::where('id',$request->out_town_driver_id)->first();
                    	$lat_driver= $Driver_outTownBooking->start_latitude; 
                    	$lng_driver= $Driver_outTownBooking->start_longitude; 
                    	$driver_address = $this->getaddress($lat_driver,$lng_driver);
                    }
                    $distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($user_address).'&destinations='.urlencode($driver_address).'&key='.env('GOOGLE_MAP_API_KEY'));
                    $distance_arr = json_decode($distance_data);
                    $elements = $distance_arr->rows[0]->elements;
                    $duration = $elements[0]->duration->text;
                    $current_date_time = date('Y-m-d H:i:s');
                    $newTime = date("Y-m-d H:i:s",strtotime($duration, strtotime($current_date_time)));
                    $updateBookingData->trip_status = $request->status;
                    $updateBookingData->driver_id = $user->id;
                    $updateBookingData->driver_arrived_time = @$newTime;;
                    $updateBookingData->save();
                    $booking_data = Booking::where('id', $tripdetail->id)->first();
                    $user_data = User::where('id', $booking_data->user_id)->first();
                    $driver_one_data = User::where('id', $booking_data->driver_id)->first();

                    if($booking_data->ride_setting_id == '2'){
                    	$outTownBooking = OutTwonrideBooking::where('id',$request->out_town_driver_id)->first();

                    	if(!empty($outTownBooking) && isset($updateBookingData->seats) && ($updateBookingData->seats != 'vip')){

                    		$seat_left = (int)$outTownBooking->seat_available - (int)$updateBookingData->seats;
                    		$seat_booked = (int)$outTownBooking->seat_booked + (int)$updateBookingData->seats;
                    		$seat_left = (int)$outTownBooking->seat_available - (int)$updateBookingData->seats;
                    		$outTownBooking->seat_available = @$seat_left;
                    		$outTownBooking->seat_booked = @$seat_booked;
                    		$outTownBooking->save();
                    	}else if($updateBookingData->seats == 'vip'){
                    		$outTownBooking->seat_booked = @$outTownBooking->seat_available;
                    		$outTownBooking->seat_available = '0';
                    		$outTownBooking->save();
                    	}
                    	if($request->out_town_driver_id){
                    		$updateBookingData->out_town_id = @$request->out_town_driver_id;
                    		$updateBookingData->save();
                    	}
                    }
                    $UserNotification_checking1 = UserNotification::where('booking_id', $updateBookingData->id)->whereNotIn('sent_to_user',[$updateBookingData->driver_id])->get();                    
                    foreach ($UserNotification_checking1 as $value) {
                    	$userbehavior = UserNotification::where('id',$value->id)->first();
                    	$userbehavior->delete();
                    }
                    if($updateBookingData->ride_setting_id == '2'){
                    	$check_outtown = UserNotification::where('sent_to_user',$user->id)->where('out_of_town',$request->out_town_driver_id)->where('booking_id',$request->booking_id)->first();
                    	// $cheking = Booking::where('id',$request->booking_id)->where('trip_status','pending')->first();
                    	if(!empty($check_outtown)){
                    		$UserNotification_checking = UserNotification::where('booking_id',$request->booking_id)->where('sent_to_user',$updateBookingData->driver_id)->first();
                    		// foreach ($UserNotification_checking as $value) {
                    		UserNotification::where('id', @$UserNotification_checking->id)->update([
                    			'sent_from_user' => $updateBookingData->user_id,
                    			'sent_to_user' => $updateBookingData->driver_id,
                    			'notification_for' => "accepted",
                    			'title' => "Booking Accepted",
                    			'description' => "Your Booking Accepted Successfully",
                    			'admin_flag' => "0",
                    			]);
                    		// }                        
                    	}
                    }

                    if($updateBookingData->ride_setting_id == '1'){
                    	$UserNotification_checking = UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$updateBookingData->driver_id)->first();
                    	UserNotification::where('id', @$UserNotification_checking->id)->update([
                    		'sent_from_user' => $updateBookingData->user_id,
                    		'sent_to_user' => $updateBookingData->driver_id,
                    		'notification_for' => "accepted",
                    		'title' => "Booking Accepted",
                    		'description' => "Your Booking Accepted Successfully",
                    		'admin_flag' => "0",
                    		]);
                    }
                    $user_datas = User::where('id',$tripdetail->user_id)->first();
                    $user_booking = Booking::with(['driver'])->where('id',$request->booking_id)->first();
                    if($user_datas->device_type == 'android'){
                    	if(empty($user_booking->driver)){
                    		$booking_driver_data = new \stdClass();
                    	}else{
                    		$booking_driver_data = $user_booking->driver;
                    		if(!empty($booking_driver_data->first_name)){
                    			$user_name = @$booking_driver_data->last_name.' '.@$booking_driver_data->first_name;
                    		} else {
                    			$user_name = @$booking_driver_data->company_name;
                    		}
                    		$pickup_total_km = $this->pickupdistance($user_datas->latitude, $user_datas->longitude, $booking_driver_data->latitude, $booking_driver_data->longitude);
                    		$booking_driver_data['pickup_total_km'] = number_format((float)$pickup_total_km, 2, '.', '');
                			//User Address
                    		$lat= $user_datas->latitude;
                    		$lng= $user_datas->longitude;
                    		$user_address = $this->getaddress($user_datas->latitude,$user_datas->longitude);
                    		if($user_address){
                    			$origin_addresses = $user_address;
                    		}
                			//Driver Address
                    		$lat= $booking_driver_data->latitude;
                    		$lng= $booking_driver_data->longitude;
                    		$driver_address = $this->getaddress($booking_driver_data->latitude,$booking_driver_data->longitude);
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

                    		$avgrating = RatingReviews::where('from_user_id',$user_booking->driver->id)->orWhere('to_user_id',$user_booking->driver->id)->avg('rating');
                    		if(empty($avgrating)){
                    			$avgrating = 0;
                    		}

                    		$booking_driver_data['driver_review'] = $avgrating;
                    		$booking_driver_data['booking_id'] = $request->booking_id;

                    		$booking_driver_data['arrival_time'] = $duration;
                    		$booking_driver_data['booking_date'] = $user_booking->booking_date .' '. $user_booking->booking_start_time.' '.@$user_booking->booking_end_time;
                    		$booking_driver_data['booking_start_time'] =$user_booking->booking_start_time;
                    		$booking_driver_data['booking_end_time'] =@$user_booking->booking_end_time;
                    	}
                    	$datas['booking_data'] = $booking_driver_data;
                    }else{
                    	if(empty($user_booking->driver)){
                    		$booking_driver_data = new \stdClass();
                    	}else{
                    		$booking_driver_data['booking_id'] = $request->booking_id;
                    		$booking_driver_data['drop_Address'] = $user_booking->drop_location;
                    		$booking_driver_data['first_name'] = $user_booking->driver->first_name;
                    		$booking_driver_data['last_name'] = $user_booking->driver->last_name;
                    		$booking_driver_data['booking_date'] = $user_booking->booking_date .' '. $user_booking->booking_start_time.' '.@$user_booking->booking_end_time;
                    		$booking_driver_data['booking_start_time'] =$user_booking->booking_start_time;
                    		$booking_driver_data['booking_end_time'] =@$user_booking->booking_end_time;
                    		$booking_driver_data['driver_vehicle'] = $user_booking->driver->driver_vehicle;
                    		$booking_driver_data['vehicle_model'] = $user_booking->driver->driver_model;
	                    	//User Address
                    		$user_address = $this->getaddress($user_datas->latitude,$user_datas->longitude);
                    		if($user_address){
                    			$origin_addresses = $user_address;
                    		}
	            			//Driver Address
                    		$driver_address = $this->getaddress($user_booking->driver->latitude,$user_booking->driver->longitude);
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
                    		$booking_driver_data['arrival_time'] = $duration;
                    	}
                    	$datas['booking_data'] = $booking_driver_data;
                    }
                    $datas['message']   = $user->first_name.' '.$user->last_name.' has accepted your ride request';
                    $datas['type']      =  'request_accepted';
                    $datas['booking_type']  = $updateBookingData->booking_type;
                    $datas['user_id']   =   $tripdetail->user_id;
                    $datas['notification'] = Event::dispatch('send-notification-assigned-user',array($user_datas,$datas));
                }else{
                	return response()->json(['status' => 'error','message' => 'This booking is already accepted by another Driver.']);
                }
            }elseif($request->status === 'reject'){
            	$user_noyofiction = UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$user->id)->first();
            	$user_nofiction_dele = UserNotification::where('id', $user_noyofiction->id)->first();
            	$user_nofiction_dele->delete();
            }else{
            	$updateBookingData = Booking::where('id', $tripdetail->id)->first();
            	$UserNotification_checking = UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$updateBookingData->driver_id)->first();
            	UserNotification::where('id', $UserNotification_checking->id)->update([
            		'sent_from_user' => $updateBookingData->user_id,
            		'sent_to_user' => $updateBookingData->driver_id,
            		'notification_for' => "cancel",
            		'title' => "Booking cancelled",
            		'description' => "Your Booking cancelled Successfully",
            		'admin_flag' => "0",
            		]);
            	Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status]);

            }
            if($request->status === 'driver_arrived'){
            	$msg='Driver has been Arrived';
            }else{
            	$msg='Your Trip '.ucfirst($request->status).' Successfully';
            }
            PopupNotification::create([
            	'from_user_id' => $user->id,
            	'to_user_id' => $tripdetail->user_id,
            	'title' => $msg,
            	'description' => $msg,
            	'date' => Carbon::now()->format('Y-m-d'),
            	'time' => Carbon::now()->format('H:i'),
            	'booking_id' => $tripdetail->id,
            	]);
            if(!empty($user_data)){
            	$MsgData = $user_data;
            }else{
            	$data = new \stdClass();
            	$MsgData = $data;
            }
            return response()->json(['status' => 'success','message' =>$msg , 'data'=>Booking::find($trip_id), 'userdata' =>$MsgData]);
        }else{
        	return response()->json(['status' => 'error','message' => 'Something went Wrong']);
        }
    } catch (Exception $e) {
    	return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    }
}

function getaddress($lat,$lng){
	$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.($lat).','.($lng).'&key='.env('GOOGLE_MAP_API_KEY');
	$json = @file_get_contents($url);
	$data=json_decode($json);
	$status = $data->status;
        // dd($data->results[0]->formatted_address);
	if($status=="OK")
		return $data->results[0]->formatted_address;
	else
		return false;
}

/*
|--------------------------------------------------------------------------
| Driver Trip Update Status
|--------------------------------------------------------------------------
|
*/
public function userGettotalfare($vehicle_id='', $distance='', $promo_id=''){
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
			$fare = (float)$fare - (((float)($fare)*(float)$promo_code->amount)/100) ;
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
public function driverTripUpdateStatus(Request $request){

	$validation_array =[
	'booking_id'    => 'nullable',
	'status'        => 'required'
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
			$tripdetail = Booking::where('id', $trip_id)->first();
			if(!empty($tripdetail)){
				if($request->status === 'completed'){
					$status = "completed";
					$title = "Booking Completed";
					$description = "Your Booking Completed Successfully";
					/* new calculation */
					$total_kms = round($this->distance($tripdetail->start_latitude, $tripdetail->start_longitude, $request->drop_lat, $request->drop_long));
					if($total_kms == "0.0"){
						$total_kms = 1;
					} else {
						$total_kms = $total_kms;
					}
					$total_amounts = $this->userGettotalfare($tripdetail->vehicle_id, $total_kms, $tripdetail->promo_id);
					/* new calculation */
					if(!empty($request->drop_long)|| !empty($request->drop_lat)){
						$drop_long = $request->drop_long;
						$drop_lat = $request->drop_lat;
					}else{
						$drop_long = $tripdetail->drop_longitude;
						$drop_lat = $tripdetail->drop_latitude;
					}
					Booking::where('id', $tripdetail->id)->update([
						'trip_status'=>$request->status, 
						'end_time'=>Carbon::now()->format('H:i'),
						'drop_long'=> $drop_long,
						'drop_lat' => $drop_lat,
						'base_fare'         => @$total_amounts['total_fare'],
						'total_km'          => @$total_kms,
						'admin_commision'   => @$total_amounts['admin_commision'],
						'total_amount'      => @$total_amounts['total_fare'],
						]);

                    // Notification For Driver
					/*$driver_data = User::where('id',$tripdetail->driver_id)->first();
					$data['message']   =  'Trip '.ucfirst($request->status);
					$data['type']      =  $request->status;
					$data['driver_id']   =   $tripdetail->driver_id;
					$data['notification'] = Event::dispatch('send-notification-assigned-driver',array($driver_data,$data));*/
				}else{
					$status = $request->status;
					$title = "Booking".$request->status;
					$description = "Your Booking".$request->status."Successfully";
					Booking::where('id', $tripdetail->id)->update(['trip_status'=>$request->status]);
				}
                // Notification For User
				$user_data = User::where('id',$tripdetail->user_id)->first();
				if($request->status == 'pick_up'){
					$data['message']= $user->first_name.' '.$user->last_name.' is arriving to your location for pickup';
					$data['type']= 'trip_ongoing';
				}else{
					$data['message']= 'Trip '.ucfirst($request->status);
					$data['type']= $request->status;
				}
				$user_data['driver_id']     = $tripdetail->driver_id;
				$data['user_id']   =   $tripdetail->user_id;
				$data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_data,$data));

				if($request->status === 'driver_arrived'){
					$msg = 'Driver has been Arrived';
				}else{
					$msg = 'Your Trip '.ucfirst($request->status).' Successfully';
				}
				PopupNotification::create([
					'from_user_id' => $user->id,
					'to_user_id' => $tripdetail->user_id,
					'title' => $msg,
					'description' => $msg,
					'date' => Carbon::now()->format('Y-m-d'),
					'time' => Carbon::now()->format('H:i'),
					'booking_id' => $tripdetail->id,
					]);
                // $UserNotification_checking = UserNotification::where('sent_to_user',$tripdetail->driver_id)->first();
				$UserNotification_checking = UserNotification::where('booking_id', $tripdetail->id)->where('sent_to_user',$tripdetail->driver_id)->first();
				UserNotification::where('id', @$UserNotification_checking->id)->update([
					'sent_from_user' => $tripdetail->user_id,
					'sent_to_user' => $tripdetail->driver_id,
					'notification_for' => $status,
					'title' => $title,
					'description' => $description,
					'admin_flag' => "0",
					]);
				$outoftown = UserNotification::where('booking_id',$tripdetail->id)->where('sent_to_user',$user->id)->where('out_of_town','!=',null)->pluck('out_of_town');
				if(count($outoftown)>0){
					$driverSchedule = OutTwonrideBooking::where('id',$outoftown)->where('driver_id',$user->id)->first();
					$checkBooking = UserNotification::where('out_of_town',$outoftown)->where('sent_to_user',$user->id)->whereIn('notification_for',['completed','cancel','emergency'])->get();
					if($driverSchedule->seat_available == '0' && count($checkBooking)>0){
						OutTwonrideBooking::where('id',$outoftown)->where('driver_id',$user->id)->delete();
					}
				}
				return response()->json(['status' => 'success','message' =>$msg , 'data'=>Booking::find($trip_id)]);
			}else{
				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
			}
		}else if($request->parcel_id){
			$trip_id = $request->parcel_id;
			$tripdetail = ParcelDetail::where('id', $trip_id)->first();
			if(!empty($tripdetail)){
				if($request->status === 'completed'){
					ParcelDetail::where('id', $tripdetail->id)->update(['parcel_status'=>$request->status, 'end_time'=>Carbon::now()->format('H:i')]);
                    // Notification For Driver
					$driver_data['message']       = 'Parcel Ride '.ucfirst($request->status);
					$driver_data['type']          = $request->status;
					$driver_data['driver_id']     = $tripdetail->driver_id;
					$driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($tripdetail,$driver_data));
				}else{
					ParcelDetail::where('id', $tripdetail->id)->update(['parcel_status'=>$request->status]);
				}
                // Notification For User
				$data['message']       = 'Your Parcel Trip '.ucfirst($request->status);
				$data['type']          = $request->status;
				$data['driver_id']     = $tripdetail->driver_id;
				$data['notification']  = Event::dispatch('send-notification-assigned-user',array($tripdetail,$data));
				if($request->status === 'driver_arrived'){
					$msg = 'Driver has been Arrived';
				}else{
					$msg = 'Your Parcel Trip '.ucfirst($request->status).' Successfully';
				}
				PopupNotification::create([
					'from_user_id' => $user->id,
					'to_user_id' => $tripdetail->user_id,
					'title' => $msg,
					'description' => $msg,
					'date' => Carbon::now()->format('Y-m-d H:i'),
					'time' => Carbon::now()->format('H:i'),
					'parcel_id' => $tripdetail->id,
					]);
				return response()->json(['status' => 'success','message' =>$msg , 'data'=>ParcelDetail::find($trip_id)]);
			}else{
				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
			}
		}else if($request->shuttle_id){
			$message='';

			if($request->status === 'pick_up'){
				LinerideUserBooking::where('user_id', $request->user_id)
				->where('shuttle_driver_id', $request->shuttle_id)
				->where('id', $request->shuttle_user_id)
				->update([
					'trip_status'   =>$request->status,
					]);
				$lineridebookingDetail = LinerideUserBooking::with(['user','driver','driver_shuttle'])->where('id', $request->shuttle_user_id)->first();
				$message = 'User for Shuttle Ride Picked Successfully';
			}else if($request->status === 'completed'){
				if($request->shuttle_user_id){
					LinerideUserBooking::where('user_id', $request->user_id)
					->where('shuttle_driver_id', $request->shuttle_id)
					->where('id', $request->shuttle_user_id)
					->update([
						'trip_status'   =>'completed',
						'end_time'      => Carbon::now()->format('H:i A')
						]);
					$lineridebookingDetail = LinerideUserBooking::where('user_id', $request->user_id)->where('id', $request->shuttle_user_id)->where('shuttle_driver_id', $request->shuttle_id)->first();

					$message = 'User Shuttle Ride Completed Successfully';
				}else if($request->shuttle_id){
					LinerideBooking::where('id', $request->shuttle_id)->update([
						'trip_status'   =>'completed',
						'end_time'      => Carbon::now()->format('H:i A')
						]);

					LinerideUserBooking::where('driver_id', $user->id)
					->where('shuttle_driver_id', $request->shuttle_id)
					->update([
						'trip_status'   =>'completed'
						]);

					$lineridebookingDetail = LinerideBooking::where('id', $request->shuttle_id)->first();
					$message = 'Driver Shuttle Ride Completed Successfully';
				}
			}else{
				if($request->shuttle_user_id){
					LinerideUserBooking::where('user_id', $request->shuttle_user_id)
					->where('shuttle_driver_id', $request->shuttle_id)
					->update([
						'trip_status'   =>'completed',
						'end_time'      => Carbon::now()->format('H:i A')
						]);
					$lineridebookingDetail = LinerideUserBooking::where('user_id', $request->shuttle_user_id)->where('shuttle_driver_id', $request->shuttle_id)->first();
					$message = 'User Shuttle Ride Completed Successfully';
				}else if($request->shuttle_id){
					LinerideBooking::where('id', $request->shuttle_id)->update([
						'trip_status'   =>'completed',
						'end_time'      => Carbon::now()->format('H:i A')
						]);

					LinerideUserBooking::where('driver_id', $user->id)
					->where('shuttle_driver_id', $request->shuttle_id)
					->update([
						'trip_status'   =>'completed'
						]);
					$message = 'Driver Shuttle Ride Completed Successfully';
				}
				$lineridebookingDetail = LinerideUserBooking::where('user_id', $request->shuttle_user_id)->where('shuttle_driver_id', $request->shuttle_id)->first();
			}

			if(!empty($lineridebookingDetail)){
				return response()->json(['status' => 'success','message' => $message,'data' => $lineridebookingDetail]);
			} else {
				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
			}

		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Driver Stop Hold Time
|--------------------------------------------------------------------------
|
*/
public function stopHoldTime(Request $request){
	$validation_array =[
	'booking_id'       => 'required',
	'hold_time'     => 'required'
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'fail','message' => $validation->messages()->first()],200);
	}
	try {
		$trip_id = $request->booking_id;
		$tripdetail = Booking::where('id', $trip_id)->first();
		if(!empty($tripdetail)){
			Booking::where('id', $tripdetail->id)->update(['hold_time'=>$request->hold_time]);
			return response()->json(['status' => 'success','message' => 'Stop Time Saved', 'data'=>Booking::find($trip_id)]);
		}else{
			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| User Create Supports
|--------------------------------------------------------------------------
|
*/
public function CreateDriverSupports(Request $request){
	$validation_array =[
	'email'      => 'required',
	'support_cat_id'      => 'required',
	'description'      => 'required',
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
		$data['driver_id']=$user->id;
		$data['email']=request('email');
		$data['support_cat_id']=request('support_cat_id');
		$data['description']=request('description');
		$userratingreviews = Support::Create($data);
		return response()->json(['status' => 'success','message' => 'You are successfully User Rating Reviews!','data' => $data]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Calculate Hold Time
|--------------------------------------------------------------------------
|
*/
public function calculateHoldTime(Request $request){
	$validation_array =[
	'booking_id' => 'required',
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
		$hold_time_rate = Setting::where('code','hold_time_rate')->first();
		$tripdetail = Booking::find($request->booking_id);
		if(!empty($tripdetail)){
			if($tripdetail->hold_time && $tripdetail->total_amount && $hold_time_rate->value){
            // Convert Second Into Minutes
				$minutes = gmdate('i.s', (int)$tripdetail->hold_time);
            // Take Product Of minutes with hold time rate
				$a=array((float)$minutes,(float)$hold_time_rate->value);
				$calc_holdtime = array_product($a);
				$total = (float)$calc_holdtime + (float)$tripdetail->total_amount;
				$data['calculate_amount'] = $calc_holdtime;
				$data['hold_time_rate']   = $hold_time_rate->value;
				$data['total_hold_time']  = $minutes;
				$data['total_calc_amount']= round($total);
				Booking::where('id', $request->booking_id)->update([ 'hold_time_amount' => round($calc_holdtime)]);
			}
			return response()->json(['status' => 'success','message' => 'Booking Details','data'=>$data, 'trip_details' => Booking::find($request->booking_id) ]);
		}else{
			return response()->json(['status' => 'error','message' => 'Trp not found']);
		}
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Self Drive Booking (Trip Create by Driver)
|--------------------------------------------------------------------------
|
*/
public function createDriverTrip(Request $request){
	$validation_array =[
	'pick_up_location'    => 'required',
	'drop_location'       => 'required',
	'start_latitude'      => 'required',
	'start_longitude'     => 'required',
	'drop_latitude'       => 'required',
	'drop_longitude'      => 'required',
	'extra_notes'         => 'required',
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
		$booking_date = Carbon::now()->format('d-m-Y');
		$booking_start_time = Carbon::now()->format('H:i A');
		$trips = Booking::create([
			'driver_id'                => $user->id,
			'booking_type'             => 'immediate',
			'pick_up_location'         => $request->pick_up_location,
			'drop_location'            => $request->drop_location,
			'start_latitude'           => $request->start_latitude,
			'start_longitude'          => $request->start_longitude,
			'drop_latitude'            => $request->drop_latitude,
			'drop_longitude'           => $request->drop_longitude,
			'otp'                      => mt_rand(1000, 9999),
			'booking_date'             => $booking_date,
			'booking_start_time'       => $booking_start_time,
			'extra_notes'              => $request->extra_notes,
			'start_time'               => $booking_start_time,
			'payment_type'             => 'cash',
			]);
		if(!empty($trips)){
			$tripdetail = Booking::find($trips->id);
			return response()->json(['status' => 'success','message' => 'Immediate Trip Booked Successfully','data' => $tripdetail]);
		}else{
			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Self Drive Booking (Trip Payment to Driver , cash payment)
|--------------------------------------------------------------------------
|
*/
public function driverTripPayment(Request $request){
	$validation_array =[
	'booking_id'    => 'required',
	'amount'     => 'required',
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
		$total_amount = $request->amount;
		if($request->tip_amount){
			$tip_amount =  (int)$request->tip_amount;
		}else{
			$tip_amount = '';
		}
		Booking::where('id', $request->booking_id)->update([
			'total_amount' => $request->amount,
			'tip_amount'   => $tip_amount,
			'trip_status'  => 'completed',
			]);
		$total = (int)$total_amount + (int)$tip_amount;
		$trips = Booking::find($request->booking_id);
        // Notification For Driver
		$driver_data['message']       = 'Ride Completed';
		$driver_data['type']          = 'completed';
		$driver_data['driver_id']     = $trips->driver_id;
		$driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($trips,$driver_data));
		return response()->json(['status' => 'success','message' => 'Trip Payment Completed Successfully','data' => $trips, 'total'=>$total]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
|   Create Driver Emergency Request
|--------------------------------------------------------------------------
|
*/
public function driverEmergencyRequest(Request $request){
	$validation_array =[
	'type_id'      => 'required',
	'booking_id'      => 'nullable',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->errors()->first(),'data'=> (object)[]]);
	}
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		if($request->booking_id){
			Booking::where('id',$request->booking_id)->update([
				'trip_status'       => "emergency",
				'payment_status'    => "cancelled",
				]);
			$emergency_booking = Booking::where('id',$request->booking_id)->first();
			$UserNotification_checking = UserNotification::where('booking_id', $emergency_booking->id)->where('sent_to_user',$emergency_booking->driver_id)->first();
			UserNotification::where('id', $UserNotification_checking->id)->update([
				'sent_from_user' =>$emergency_booking->user_id,
				'sent_to_user' => $emergency_booking->driver_id,
				'notification_for' => "emergency",
				'title' => "Booking emergency",
				'description' => "Your Booking emergency Successfully",
				'admin_flag' => "0",
				]);


		} else if($request->parcel_id){
			ParcelDetail::where('id',$request->parcel_id)->update([
				'trip_status'       => "cancelled",
				]);
			$emergency_booking = ParcelDetail::where('id',$request->parcel_id)->first();
		} else if($request->shuttle_id){
			LinerideBooking::where('id',$request->shuttle_id)->update([
				'trip_status'       => "cancelled",
				]);
			LinerideUserBooking::where('shuttle_driver_id',$request->shuttle_id)->update([
				'trip_status'       => "cancelled",
				]);
			$emergency_booking = LinerideBooking::where('id',$request->shuttle_id)->first();
		}

		$data = EmergencyRequest::create([
			'type_id'     => $request->get('type_id'),
			'driver_id'   => $user->id,
			'user_id'     => @$emergency_booking->user_id,
			'booking_id'  => @$request->booking_id,
			'parcel_id'  =>  @$request->parcel_id,
			'extra_notes' => @$request->extra_notes,
			]);
		$request = EmergencyRequest::with('emergency_type')->where('id', $data->id)->first();
		$request_Driver = User::where('id',$request->driver_id)->first();
		$request_type = EmergencyType::find($request->type_id)->first();
		$email = $request_Driver->email;
		$mailData['send_to'] = $request_Driver->email;
		$mailData['subject'] = 'Emergency Inquiry';
		$mailData['body'] = $request_type->type_name;
		Mail::to($email)->send(new EmergencyMail($mailData['subject'], $mailData['send_to'], $mailData['body']));
		return response()->json(['status' => 'success','message' => 'You have  successfully submitted Emergency Request!','data' => $request]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
|   Get Driver Document
|--------------------------------------------------------------------------
|
*/
public function getDriverDocument(){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$driverdocs = DriverDocuments::where('driver_id', $user->id)->select('driver_documents.*','users.doc_status')->join('users','users.id','=','driver_documents.driver_id')->get();
		$vehicledocs = DriverVehicleDocument::where('driver_id', $user->id)->select('driver_vehicle_documents.*','users.vehicle_doc_status')->join('users','users.id','=','driver_vehicle_documents.driver_id')->get();
		$data['driver_docs'] = $driverdocs;
		$data['vehicle_docs'] = $vehicledocs;
		return response()->json(['status' => 'success','message' => 'All Driver Documents','data' => $data]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Get Driver Ride Setting
|--------------------------------------------------------------------------
|
*/
public function getDriverRideSetting(){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$driverridesetting = DriverDetails::where('driver_id', $user->id)->pluck('ride_type')->toArray();
		$ridesetting = RideSetting::whereIn('id',$driverridesetting)->get();
		return response()->json(['status' => 'success','message' =>'All Ride Setting','data'=>$ridesetting]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Complete Driver Trip Booking
|--------------------------------------------------------------------------
|
*/
public function completeDriverTripBooking(Request $request){
    // $validation_array =[
    //     'booking_id'          => 'required',
    // ];
    // $validation = Validator::make($request->all(),$validation_array);
    // if($validation->fails()){
    //     return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
    // }
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		if($request->booking_id){
			$tripdetail = Booking::where('id', $request->booking_id)->first();
			if($tripdetail->payment_status == "completed"){
				$payment_status = "1";
			} else{
				$payment_status = "0";
			}
			if(empty($tripdetail->tip_amount)){
				$tipe = "0";
			} else {
				$tipe = $tripdetail->tip_amount;
			}
			$userdetail = User::where('id', $tripdetail->user_id)->first();
			$vehicle = Vehicle::find($user->vehicle_id);
			$total_km = $this->distance($tripdetail->start_latitude, $tripdetail->start_longitude, $tripdetail->drop_lat, $tripdetail->drop_long);
			if($total_km == "0.0"){
				$total_km = 1;
			} else {
				$total_km = $total_km;
			}
			$total_amount = $this->gettotalfare($vehicle->id, $total_km, $tripdetail->promo_id);
			$total_fare = $total_amount['total_fare'];
			$details['total_fare'] = $total_fare;
			$details['total_km'] = $total_km;
			$details['tipe'] = $tipe;
			$details['pick_up_location'] = $tripdetail->pick_up_location;
			$details['drop_location'] = $tripdetail->drop_location;
			$details['user_name'] = $userdetail->first_name;
			$details['avatar'] = $userdetail->avatar;
			$details['user_id'] = $userdetail->id;
			$details['payment_status'] = $payment_status;
			return response()->json(['status' => 'success','message' => 'Record Successfully','data' => $details]);
		} else if($request->parcel_id){
			$tripdetail = ParcelDetail::where('id', $request->parcel_id)->first();
			if($tripdetail->payment_status == "completed"){
				$payment_status = "1";
			} else{
				$payment_status = "0";
			}
			if(empty($tripdetail->tip_amount)){
				$tipe = "0";
			} else {
				$tipe = $tripdetail->tip_amount;
			}
			$userdetail = User::where('id', $tripdetail->user_id)->first();
			$vehicle = Vehicle::find($user->vehicle_id);
			$total_km = $this->distance($tripdetail->start_latitude, $tripdetail->start_longitude, $tripdetail->drop_latitude, $tripdetail->drop_longitude);
			$total_amount = $this->gettotalfare($vehicle->id, $total_km, $tripdetail->promo_id);
			$total_fare = $total_amount['total_fare'];
			$details['total_fare'] = $total_fare;
			$details['total_km'] = $total_km;
			$details['tipe'] = $tipe;
			$details['pick_up_location'] = $tripdetail->pick_up_location;
			$details['drop_location'] = $tripdetail->drop_location;
			$details['user_name'] = $userdetail->first_name;
			$details['avatar'] = $userdetail->avatar;
			$details['user_id'] = $userdetail->id;
			$details['payment_status'] = $payment_status;
			return response()->json(['status' => 'success','message' => 'Record Successfully','data' => $details]);
		}if($request->shuttle_id){
			$tripdetail = LinerideUserBooking::where('shuttle_driver_id', $request->shuttle_id)
			->where('user_id', $request->user_id)
			->where('id', $request->shuttle_user_id)
			->first();
			if($tripdetail->payment_status == "completed"){
				$payment_status = "1";
			} else{
				$payment_status = "0";
			}
			if(empty($tripdetail->tip_amount)){
				$tipe = "0";
			} else {
				$tipe = $tripdetail->tip_amount;
			}
			$userdetail = User::where('id', $tripdetail->user_id)->first();
			$vehicle = Vehicle::find($user->vehicle_id);
			$total_km = $this->distance($tripdetail->start_latitude, $tripdetail->start_longitude, $tripdetail->drop_latitude, $tripdetail->drop_longitude);
			$total_amount = $this->gettotalfare($vehicle->id, $total_km, $tripdetail->promo_id);
			$total_fare = $total_amount['total_fare'];
			$details['total_fare'] = $total_fare;
			$details['total_km'] = $total_km;
			$details['tipe'] = $tipe;
			$details['pick_up_location'] = $tripdetail->pick_up_location;
			$details['drop_location'] = $tripdetail->drop_location;
			$details['user_name'] = $userdetail->first_name;
			$details['avatar'] = $userdetail->avatar;
			$details['user_id'] = $userdetail->id;
			$details['payment_status'] = $payment_status;
			return response()->json(['status' => 'success','message' => 'Record Successfully','data' => $details]);
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
	$unit = strtoupper($unit->value);
	if ($unit === "KM") {
        // $miles = $miles;
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
			$fare = (float)$fare - (((float)($fare)*(float)$promo_code->amount)/100) ;
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

public function driverParcelBookingStatus(Request $request){
	$validation_array =[
	'parcel_id'             => 'required',
	'status'                => 'required',
	'driver_amount'         => 'nullable',
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
		if($request->status === 'accepted'){
			$parcelbookingstatus = ParcelDriver::where('driver_id', $user->id)->where('parcel_id', $request->parcel_id)
			->update([
				'driver_amount'=>$request->driver_amount,
				'status'=>$request->status,
				]);
			$parceldetails = ParcelDetail::where('id', $request->parcel_id)->first();
			UserNotification::where('parcel_id', $request->parcel_id)->update([
				'sent_from_user' => $user->id,
				'sent_to_user' => $parceldetails->user_id,
				'notification_for' => "accepted",
				'title' => "Parcel Booking Accepted",
				'description' => "Your Parcel Booking Accepted Successfully",
				'admin_flag' => "0",
				]);
                // UserNotification::create([
                //     'sent_from_user' => $user->id,
                //     'sent_to_user' => $parceldetails->user_id,
                //     'parcel_id' => $request->parcel_id,
                //     'notification_for' => "accepted",
                //     'title' => "Parcel Booking Accepted",
                //     'description' => "Your Parcel Booking Accepted Successfully",
                //     'admin_flag' => "0",
                // ]);
			$user_data = User::where('id',$parceldetails->user_id)->first();

			$driver_data['message']       =  $user->first_name. ' has '.ucfirst($request->status). ' your Parcel Booking ';
			$driver_data['booking_type']  =  'schedule';
			$driver_data['type']          =  $request->status;
			$driver_data['parcel_id']     =  (string)$request->parcel_id;
			$driver_data['driver_id']     =  $user->id;
			$driver_data['title']         =  'RueRun';
			$driver_data['sound']         =  'default';
			$driver_data['request_type']  =  'parcel_booking';

			$driver_data['pick_up_location']    =  $parceldetails->pick_up_location;
			$driver_data['drop_location']       =  $parceldetails->drop_location;
			$driver_data['start_latitude']      =  $parceldetails->start_latitude;
			$driver_data['start_longitude']     =  $parceldetails->start_longitude;
			$driver_data['drop_latitude']       =  $parceldetails->drop_latitude;
			$driver_data['drop_longitude']      =  $parceldetails->drop_longitude;

			$driver_data['notification']  =  Event::dispatch('send-notification-assigned-user',array($user_data,$driver_data));

		}else if($request->status === 'rejected'){
			ParcelDriver::where('driver_id', $user->id)->where('parcel_id', $request->parcel_id)
			->update([
				'status'=>$request->status,'driver_amount'=>$request->driver_amount,
				]);
			return response()->json(['status' => 'success','message' => 'Driver Rejected Parcel Booking Successfully']);
		}
		$details = ParcelDriver::with(['driver','parcel_details'])->where('driver_id', $user->id)->where('parcel_id', $request->parcel_id)->first();

		return response()->json(['status' => 'success','message' => 'Driver Accepted Parcel Booking Successfully','data' => $details]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

    /*
    |--------------------------------------------------------------------------
    | Create New Driver Shuttle Trip
    |--------------------------------------------------------------------------
    |
    */
    public function driverShuttleRideBooking(Request $request){
    	$validation_array =[
    	'pick_up_location'    => 'required',
    	'drop_location'       => 'required',
    	'start_latitude'      => 'required',
    	'start_longitude'     => 'required',
    	'drop_latitude'       => 'required',
    	'drop_longitude'      => 'required',
    	'booking_date'        => 'required',
    	'start_time'          => 'required',
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
    		$checklineride = LinerideBooking::where('driver_id',$user->id)->where('booking_date',date("Y-m-d"))->whereNotIn('trip_status', ['completed'])->count();
    		if($checklineride > 3)
    		{
    			return response()->json(['status' => 'error','message' => 'Not more than 4 shuttle ride can create for a day']);
    		}
    		$getservicetype = RideSetting::where('code','line_ride')->first();
    		$total_distance = $this->distance($request->start_latitude, $request->start_longitude, $request->drop_latitude, $request->drop_longitude);
    		if($request->vehicle_id){
    			$getvehicleseat = Vehicle::where('id',$request->vehicle_id)->select('total_seat')->first();
    			$totalseat = (int)$getvehicleseat->total_seat - 1;
    		}else{
    			$totalseat = '0';
    		}

    		$seat='';
    		$driverdetail = User::with(['driver_details','driver_model'])->where('id', $user->id)->first();
    		if(!empty($driverdetail->driver_model)){
    			$seat = (int)$driverdetail->driver_model->total_seat - 1;
    		}

    		$destination_addresses = $request->drop_location;
    		$origin_addresses = $request->pick_up_location;
    		$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&origins='.urlencode($origin_addresses).'&destinations='.urlencode($destination_addresses).'&key='.env('GOOGLE_MAP_API_KEY'));
    		$distance_arr = json_decode($distance_data);
    		$elements = $distance_arr->rows[0]->elements;
    		$duration = $elements[0]->duration->text;
    		$driver_data['elements'] = $elements;
    		$driver_data['duration'] = $duration;
    		$distance = $elements[0]->distance->text;

    		$linerideBooking = LinerideBooking::create([
    			'pick_up_location'         => $request->pick_up_location,
    			'drop_location'            => $request->drop_location,
    			'start_latitude'           => $request->start_latitude,
    			'start_longitude'          => $request->start_longitude,
    			'drop_latitude'            => $request->drop_latitude,
    			'drop_longitude'           => $request->drop_longitude,
    			'otp'                      => mt_rand(1000, 9999),
    			'booking_date'             => $request->booking_date,
    			'start_time'               => $request->start_time,
    			'ride_setting_id'          => $getservicetype->id,
    			'driver_id'                => $user->id,
    			'total_distance'           => $distance,
    			'total_amount'             => $request->total_amount,
    			'seat_available'           => @$seat,
    			]);
    		if(!empty($linerideBooking)){
    			return response()->json(['status' => 'success','message' => 'Driver Shuttle Booking Done Successfully','data' => $linerideBooking]);
    		} else {
    			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    		}
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }


    /*
    |--------------------------------------------------------------------------
    | Driver All Shuttle Listing
    |--------------------------------------------------------------------------
    |
    */
    public function driverAllShuttleListing(Request $request){
    	$validation_array =[
    	'user_id'    => 'nullable',
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

    		$drivershuttlebooking = LinerideBooking::with(['driver'])->where('driver_id', $user->id)
    		->where('booking_date','>=',$request->curr_time)
    		->whereIn('trip_status', ['pending','on_going','driver_arrived','accepted'])
    		->orderBy('id', 'DESC')
    		->get();

    		if(!empty($drivershuttlebooking)){
    			return response()->json(['status' => 'success','message' => 'Driver Shuttle Booking Listing','data' => $drivershuttlebooking]);
    		} else {
    			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    		}
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }

    /*
    |--------------------------------------------------------------------------
    | Driver All Shuttle Booking Delete
    |--------------------------------------------------------------------------
    |
    */
    // public function driverShuttleRideBookingDelete(Request $request){
    //     $validation_array =[
    //         'shuttle_id'          => 'nullable',
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
    //         if($request->shuttle_id){
    //             $lineridebookingDelete = LinerideBooking::where('id', $request->shuttle_id)->first();
    //             $lineridebookingDelete->delete();
    //             $user_notiDatele = UserNotification::where('shuttle_id',$request->shuttle_id)->first();
    //             $user_notiDatele->delete();
    //             if(!empty($lineridebookingDelete)){
    //                 return response()->json(['status' => 'success','message' => 'Driver Shuttle Booking Delete Successfully']);
    //             } else {
    //                 return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    //             }
    //         }elseif ($request->parcel_id){
    //             $lineridebookingDelete = ParcelDetail::where('id', $request->parcel_id)->first();
    //             $lineridebookingDelete->delete();
    //             $user_notiDatele = UserNotification::where('parcel_id',$request->parcel_id)->first();
    //             $user_notiDatele->delete();
    //             if(!empty($lineridebookingDelete)){
    //                 return response()->json(['status' => 'success','message' => 'Driver Parcel Booking Delete Successfully']);
    //             } else {
    //                 return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    //             }
    //         }elseif ($request->booking_id){
    //             $lineridebookingDelete = Booking::where('id', $request->booking_id)->first();
    //             $lineridebookingDelete->delete();
    //             $user_notiDatele = UserNotification::where('booking_id',$request->booking_id)->first();
    //             $user_notiDatele->delete();
    //             if(!empty($lineridebookingDelete)){
    //                 return response()->json(['status' => 'success','message' => 'Driver Booking Delete Successfully']);
    //             } else {
    //                 return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    //             }
    //         }

    //     } catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    //     }
    // }
    public function driverShuttleRideBookingDelete(Request $request){
    	$validation_array =[
    	'shuttle_id'          => 'nullable',
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
    		if($request->shuttle_id){
    			$lineridebookingDelete = LinerideBooking::where('id', $request->shuttle_id)->first();
    			$lineridebookingDelete->delete();
    			$user_notiDatele = UserNotification::where('shuttle_id',$request->shuttle_id)->where('sent_to_user',$user->id)->first();
    			$user_notiDatele->delete();
    			if(!empty($lineridebookingDelete)){
    				return response()->json(['status' => 'success','message' => 'Driver Shuttle Booking Delete Successfully']);
    			} else {
    				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    			}
    		}elseif ($request->parcel_id){
    			$lineridebookingDelete = ParcelDetail::where('id', $request->parcel_id)->first();
    			$lineridebookingDelete->delete();
    			$user_notiDatele = UserNotification::where('parcel_id',$request->parcel_id)->where('sent_to_user',$user->id)->first();
    			$user_notiDatele->delete();
    			if(!empty($lineridebookingDelete)){
    				return response()->json(['status' => 'success','message' => 'Driver Parcel Booking Delete Successfully']);
    			} else {
    				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    			}
    		}elseif ($request->booking_id){
    			$lineridebookingDelete = Booking::where('id', $request->booking_id)->first();
    			$user_notiDatele = UserNotification::where('booking_id',$request->booking_id)->where('sent_to_user',$user->id)->first();
    			$lineridebookingDelete->delete();
    			$user_notiDatele->delete();
    			if(!empty($lineridebookingDelete)){
    				return response()->json(['status' => 'success','message' => 'Driver Booking Delete Successfully']);
    			} else {
    				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    			}
    		}

    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }

    /*
    |--------------------------------------------------------------------------
    | Driver Get Shuttle
    |--------------------------------------------------------------------------
    |
    */
    public function getdetailshuttle(Request $request){
    	$validation_array =[
    	'shuttle_id'          => 'required',
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
    		$lineridebookingDetail = LinerideBooking::where('id', $request->shuttle_id)->first();
    		if(!empty($lineridebookingDetail)){
    			return response()->json(['status' => 'success','message' => 'Driver Shuttle Booking Delete Successfully','data' => $lineridebookingDetail]);
    		} else {
    			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    		}
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }


    public function driverShuttleBookingStatus(Request $request){
    	$validation_array =[
    	'shuttle_user_id'       => 'required',
    	'status'                => 'required',
    	'driver_amount'         => 'nullable',
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
    		if($request->status === 'accepted'){

    			$shuttle_driver_id = LinerideBooking::where('driver_id', $user->id)->orderBy('id', 'DESC')->first()->id;
//                LinerideUserBooking::where('id', $request->shuttle_user_id)->update([
//                    'total_amount'          =>  $request->driver_amount,
//                    'trip_status'           =>  $request->status,
//                    'driver_id'             =>  $user->id,
//                    'shuttle_driver_id'     =>  $shuttle_driver_id,
//                ]);

    			$parceldetails = LinerideUserBooking::where('id', $request->shuttle_user_id)->first();
    			UserNotification::where('shuttle_id', $request->shuttle_user_id)->update([
    				'sent_from_user' => $parceldetails->user_id,
    				'sent_to_user' => $user->id,
    				'notification_for' => "accepted",
    				'title' => "Shuttle Booking Accepted",
    				'description' => "Your Shuttle Booking Accepted Successfully",
    				'admin_flag' => "0",
    				]);
                // UserNotification::create([
                //     'sent_from_user' => $user->id,
                //     'sent_to_user' => $parceldetails->user_id,
                //     'shuttle_id' => $request->shuttle_user_id,
                //     'notification_for' => "accepted",
                //     'title' => "Shuttle Booking Accepted",
                //     'description' => "Your Shuttle Booking Accepted Successfully",
                //     'admin_flag' => "0",
                // ]);
    			$user_data = User::where('id',$parceldetails->user_id)->first();

    			$time_difference = $this->calculate_time($parceldetails->user_id, $user->id);

    			$parcelbookingstatus = ShuttleDriver::where('driver_id', $user->id)->where('shuttle_id', $request->shuttle_user_id)
    			->update([
    				'driver_amount'     =>  $request->driver_amount,
    				'status'            =>  $request->status,
    				'arrival_time'      =>  $time_difference['arrival_time'],
    				'departure_time'    =>  $time_difference['departure_time'],
    				]);

    			$driver_data['message']       =  $user->first_name. ' has '.ucfirst($request->status). ' your Shuttle Booking ';
    			$driver_data['booking_type']  =  'immediate';
    			$driver_data['type']          =  $request->status;
    			$driver_data['shuttle_id']    =  (string)$request->shuttle_user_id;
    			$driver_data['driver_id']     =  $user->id;
    			$driver_data['title']         =  'RueRun';
    			$driver_data['sound']         =  'default';
    			$driver_data['request_type']  =  'shuttle_booking';

    			$driver_data['pick_up_location']    =  $parceldetails->pick_up_location;
    			$driver_data['drop_location']       =  $parceldetails->drop_location;
    			$driver_data['start_latitude']      =  $parceldetails->start_latitude;
    			$driver_data['start_longitude']     =  $parceldetails->start_longitude;
    			$driver_data['drop_latitude']       =  $parceldetails->drop_latitude;
    			$driver_data['drop_longitude']      =  $parceldetails->drop_longitude;

    			$driver_data['notification']  =  Event::dispatch('send-notification-assigned-user',array($user_data,$driver_data));

    		}else if($request->status === 'rejected'){
    			ShuttleDriver::where('driver_id', $user->id)->where('shuttle_id', $request->shuttle_user_id)
    			->update([
    				'status'=>$request->status,'driver_amount'=>$request->driver_amount,
    				]);
    			return response()->json(['status' => 'success','message' => 'Driver Rejected Shuttle Booking Successfully']);
    		}
    		$details = LinerideUserBooking::with(['driver','user'])->where('driver_id', $user->id)->where('id', $request->shuttle_user_id)->first();

    		return response()->json(['status' => 'success','message' => 'Driver Accepted Shuttle Booking Successfully','data' => $details]);
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }

    /*
    |--------------------------------------------------------------------------
    | Driver End Shuttle Ride Booking
    |--------------------------------------------------------------------------
    |
    */
    public function driverShuttleBookingComplete(Request $request){
    	$validation_array =[
    	'shuttle_id'          => 'required',
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
    		$message='';

    		if($request->shuttle_user_id){
    			LinerideUserBooking::where('user_id', $request->shuttle_user_id)
    			->where('shuttle_driver_id', $request->shuttle_id)
    			->update([
    				'trip_status'   =>'completed',
    				'end_time'      => Carbon::now()->format('H:i A')
    				]);
    			$lineridebookingDetail = LinerideUserBooking::where('user_id', $request->shuttle_user_id)->where('shuttle_driver_id', $request->shuttle_id)->first();
    			$message = 'User Shuttle Ride Completed Successfully';
    		}else if($request->shuttle_id){
    			LinerideBooking::where('id', $request->shuttle_id)->update([
    				'trip_status'   =>'completed',
    				'end_time'      => Carbon::now()->format('H:i A')
    				]);

    			LinerideUserBooking::where('driver_id', $user->id)
    			->where('shuttle_driver_id', $request->shuttle_id)
    			->update([
    				'trip_status'   =>'completed'
    				]);

    			$lineridebookingDetail = LinerideBooking::where('id', $request->shuttle_id)->first();
    			$message = 'Driver Shuttle Ride Completed Successfully';
    		}

    		if(!empty($lineridebookingDetail)){
    			return response()->json(['status' => 'success','message' => $message,'data' => $lineridebookingDetail]);
    		} else {
    			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    		}

    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }

    /*
    |--------------------------------------------------------------------------
    | Driver Edit Shuttle Ride Booking
    |--------------------------------------------------------------------------
    |
    */
    public function Editgetshuttle(Request $request){
    	$validation_array =[
    	'shuttle_id'        => 'required',
    	'pick_up_location'  => 'required',
    	'drop_location'     => 'required',
    	'start_latitude'    => 'required',
    	'start_longitude'   => 'required',
    	'drop_latitude'     => 'required',
    	'drop_longitude'    => 'required',
    	'booking_date'      => 'required',
    	'start_time'        => 'required',
    	'total_amount'      => 'required',
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
    		$EditLinerideBooking = LinerideBooking::where('id',$request->shuttle_id)->first();

    		$seat='';
    		if($EditLinerideBooking->seat_available){
    			$seat = $EditLinerideBooking->seat_available;
    		}else{
    			$driverdetail = User::with(['driver_details','driver_model'])->where('id', $user->id)->first();
    			if(!empty($driverdetail->driver_model)){
    				$seat = (int)$driverdetail->driver_model->total_seat - 1;
    			}
    		}

    		if($EditLinerideBooking){
    			$EditLinerideBooking->pick_up_location  = @$request->pick_up_location;
    			$EditLinerideBooking->drop_location     = @$request->drop_location;
    			$EditLinerideBooking->start_latitude    = @$request->start_latitude;
    			$EditLinerideBooking->start_longitude   = @$request->start_longitude;
    			$EditLinerideBooking->drop_latitude     = @$request->drop_latitude;
    			$EditLinerideBooking->drop_longitude    = @$request->drop_longitude;
    			$EditLinerideBooking->booking_date      = @$request->booking_date;
    			$EditLinerideBooking->start_time        = @$request->start_time;
    			$EditLinerideBooking->total_amount      = @$request->total_amount;
    			$EditLinerideBooking->seat_available    = @$seat;
    			$EditLinerideBooking->save();
    			return response()->json(['status' => 'success','message' => 'Driver Line Ride Update Successfully','data' => $EditLinerideBooking]);
    		}
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }

    public function calculate_time($user_id, $driver_id){
    	$user = User::find($user_id);
    	$driver = User::find($driver_id);

    	if($user->latitude){
    		$user_lat = $user->latitude;
    	}else{
    		$user_lat = $user->add_latitude;
    	}

    	if($user->longitude){
    		$user_long = $user->longitude;
    	}else{
    		$user_long = $user->add_longitude;
    	}

    	if($driver->latitude){
    		$driver_lat = $driver->latitude;
    	}else{
    		$driver_lat = $driver->add_latitude;
    	}

    	if($driver->longitude){
    		$driver_long = $user->longitude;
    	}else{
    		$driver_long = $user->add_longitude;
    	}

    	$distance_data = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?&units=imperial&origins='.$user_lat.','.$user_long.'&destinations='.$driver_lat.','.$driver_long.'&key='.env('GOOGLE_MAP_API_KEY'));
    	$distance_arr = json_decode($distance_data);
    	$elements = $distance_arr->rows[0]->elements;
    	$duration = $elements[0]->duration->text;

    	$time_diff = explode(' ', $duration);
    	$time_difference = [];
    	$arrival_time = Carbon::now()->addMinutes((int)$time_diff[0])->format('H:i A');
    	$departure_time = Carbon::now()->addMinutes((int)$time_diff[0]+2)->format('H:i A');
    	$time_difference['arrival_time'] = $arrival_time;
    	$time_difference['departure_time'] = $departure_time;

    	return $time_difference;
    }

    /*
    |--------------------------------------------------------------------------
    | Driver Shuttle User Detail
    |--------------------------------------------------------------------------
    |
    */
    public function driverShuttleUserDetail(Request $request){
    	$validation_array =[
    	'shuttle_driver_id' => 'nullable',
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

    		if($request->shuttle_driver_id){
    			$lineridebookingDetail = LinerideUserBooking::with(['user','driver_shuttle'])
    			->where('shuttle_driver_id', $request->shuttle_driver_id)
    			->where('booking_date', Carbon::now()->format('Y-m-d'))
    			->whereNotIn('trip_status', ['completed','cancelled'])
    			->get();
    			$driver_data = User::where('id', $user->id)->first();
    			if(sizeof($lineridebookingDetail)){
    				return response()->json(['status' => 'success','message' => 'Driver Shuttle User Detail Successfully','data' => $lineridebookingDetail, 'driver_data' => $driver_data ]);
    			} else {
    				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    			}
    		}else if($request->out_town_id){
    			$userListingDetail = Booking::with(['user', 'outTownDetails'])
    			->where('out_town_id', $request->out_town_id)
                    // ->where('booking_date', Carbon::now()->format('Y-m-d'))
    			->whereNotIn('trip_status', ['completed','cancelled'])
    			->get();
    			$driver_data = User::where('id', $user->id)->first();
    			if(sizeof($userListingDetail)){
    				return response()->json(['status' => 'success','message' => 'Out Town User Listing Successfully','data' => $userListingDetail, 'driver_data' => $driver_data ]);
    			} else {
    				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
    			}
    		}


    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }

    /*
    |--------------------------------------------------------------------------
    | Driver My Schedule List
    |--------------------------------------------------------------------------
    |
    */
    public function myScheduleList(Request $request){

    	try{
    		$user = JWTAuth::parseToken()->authenticate();
    		if(!$user){
    			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    		}
    		$data = [];

    		$in_town_list = Booking::where('driver_id', $user->id)->where('booking_date','>=',date('Y-m-d'))->where('ride_setting_id', '1')->whereIn('trip_status', ['pending','on_going','driver_arrived','accepted','departed'])->orderBy('id', 'DESC')->get();
    		foreach ($in_town_list as $key => $value) {
    			$value['booking_date'] = date("m-d-Y", strtotime($value['booking_date'])); 
    		}
    		$out_town_list = Booking::where('driver_id', $user->id)->where('booking_date','>=',date('Y-m-d'))->where('ride_setting_id', '2')->whereIn('trip_status', ['pending','on_going','driver_arrived','completed','accepted','departed'])->orderBy('id', 'DESC')->get();
    		foreach ($out_town_list as $key => $value) {
    			$value['booking_date'] = date("m-d-Y", strtotime($value['booking_date'])); 
    		}
    		$parcel_list = ParcelDetail::where('driver_id', $user->id)->where('booking_date','>=',date('Y-m-d'))->whereIn('parcel_status', ['pending','on_going','driver_arrived','completed','accepted','departed'])->orderBy('id', 'DESC')->get();
    		foreach ($parcel_list as $key => $value) {
    			$value['booking_date'] = date("m-d-Y", strtotime($value['booking_date'])); 
    		}
    		$shuttle_list = LinerideBooking::where('driver_id', $user->id)->where('booking_date','>=',date('Y-m-d'))->whereIn('trip_status', ['pending','on_going','driver_arrived','completed','accepted','departed'])->orderBy('id', 'DESC')->get();
    		foreach ($shuttle_list as $key => $value) {
    			$value['booking_date'] = date("m-d-Y", strtotime($value['booking_date'])); 
    		}
            // $in_town_list   = Booking::where('driver_id', $user->id)->where('ride_setting_id', '1')->whereIn('trip_status', ['accepted'])->get();
            // $out_town_list  = Booking::where('driver_id', $user->id)->where('ride_setting_id', '2')->whereIn('trip_status', ['accepted'])->get();
            // $parcel_list    = ParcelDetail::where('driver_id', $user->id)->whereIn('parcel_status', ['accepted'])->get();
            // $shuttle_list   = LinerideBooking::where('driver_id', $user->id)->whereIn('trip_status', ['accepted','pending'])->get();

    		$data['in_town_list']   = $in_town_list;
    		$data['out_town_list']  = $out_town_list;
    		$data['parcel_list']    = $parcel_list;
    		$data['shuttle_list']   = $shuttle_list;

    		return response()->json(['status' => 'success','message' => 'My Schedule List Successfully','data' => $data]);

    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }

    public function OutTwonrideBookingDelete(Request $request){
    	$validation_array =[
    	'id'          => 'nullable',
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
    		$OutTwonrideBooking = OutTwonrideBooking::where('id', $request->id)->first();
    		if(!empty($OutTwonrideBooking)){
    			$booking = UserNotification::where('out_of_town',$request->id)->where('out_of_town','!=',NULL)->pluck('id');
    			if(count($booking)>0){
    				$pending_booking = UserNotification::whereIn('id',$booking)->where('notification_for','pending')->pluck('booking_id');
    				$notpending_booking = UserNotification::whereIn('id',$booking)->where('sent_to_user',$user->id)->whereIn('notification_for',['cancel','completed','emergency'])->pluck('id');
    				$allbooking = UserNotification::whereIn('id',$booking)->where('sent_to_user',$user->id)->whereNotIn('notification_for',['pending','cancel','completed','emergency'])->pluck('id');
    				if(count($pending_booking)>0){
    					$notdriverschedulebooking = UserNotification::where('sent_to_user','!=',$user->id)->whereIn('booking_id',$pending_booking)->pluck('booking_id');
    					if(count($notdriverschedulebooking)>0){
    						UserNotification::whereIn('id',$booking)->where('sent_to_user',$user->id)->where('out_of_town',$request->id)->where('notification_for','pending')->update([
    							'notification_for' => "cancel",
    							'title' => "Booking cancelled",
    							'description' => "Your Booking cancelled Successfully",
    							'admin_flag' => "0",
    							]);
    						$OutTwonrideBooking->delete();
    						return response()->json(['status' => 'success','message' => 'Driver Out Of Town Booking Delete Successfully']);
    					}else{
    						$userBooking = Booking::whereIn('id',$pending_booking)->get();
    						foreach ($userBooking as $value) {
    							PopupNotification::create([
    								'from_user_id' => $user->id,
    								'to_user_id' => $value->user_id,
    								'title' => 'Booking Cancelled',
    								'description' => 'Booking has been cancelled Successfully',
    								'date' => Carbon::now()->format('Y-m-d'),
    								'time' => Carbon::now()->format('H:i'),
    								'booking_id' => $value->id,
    								]);
    						}
    						$userBookings = Booking::whereIn('id',$pending_booking)->groupBy('user_id')->get();
    						foreach ($userBookings as $value) {
    							$user_datas = User::where('id',$value->user_id)->first();
    							$data['message']   =  'Trip Cancelled';
    							$data['type']      =  'request_cancel';
    							$data['booking_id'] = $value->id;
    							$data['user_id']   =   $value->user_id;
    							$data['notification'] = Event::dispatch('send-notification-assigned-user',array($user_datas,$data));
    						}
    						Booking::whereIn('id', $pending_booking)->update(['trip_status'=>'cancelled']);
    						UserNotification::whereIn('id',$booking)->where('sent_to_user',$user->id)->where('out_of_town',$request->id)->where('notification_for','pending')->update([
    							'notification_for' => "cancel",
    							'title' => "Booking cancelled",
    							'description' => "Your Booking cancelled Successfully",
    							'admin_flag' => "0",
    							]);
    						$OutTwonrideBooking->delete();
    						return response()->json(['status' => 'success','message' => 'Driver Out Of Town Booking Delete Successfully.Also user booking is cancelled.']);
    					}

    				}elseif(count($notpending_booking)>0){
    					$OutTwonrideBooking->delete();
    					return response()->json(['status' => 'success','message' => 'Driver Out Of Town Booking Delete Successfully']);
    				}elseif(count($allbooking)>0){
    					return response()->json(['status' => 'error','message' => 'Schedule is booked by user.So you can not delete this schedule.']);
    				}else{
    					return response()->json(['status' => 'error','message' => 'Schedule is booked by user.So you can not delete this schedule.']);
    				}
    			}else{
    				$OutTwonrideBooking->delete();
    				return response()->json(['status' => 'success','message' => 'Driver Out Of Town Booking Delete Successfully']);
    			}
    		} else {
    			return response()->json(['status' => 'error','message' => 'Booking not found']);
    		}
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }

    public function driverOutOfTown(Request $request){
    	try{
    		$user = JWTAuth::parseToken()->authenticate();
    		if(!$user){
    			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    		}
    		$OutTwonrideBooking = OutTwonrideBooking::where('driver_id', $user->id)->where('booking_date','>=',date('Y-m-d'))->get();
    		return response()->json(['status' => 'success','message' => 'Driver Out Of Town Schedule Successfully','data'=>$OutTwonrideBooking]);
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }


    public function driverOutOfTownDetail(Request $request){
    	try{
    		$user = JWTAuth::parseToken()->authenticate();
    		if(!$user){
    			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
    		}
    		$data =  UserNotification::with(['booking_details','user'])->where('taxi_hailing','sharing')->where('sent_to_user', $user->id)->where('out_of_town',$request->out_of_town_id)->whereIn('notification_for',['pending','accepted'])->orderBy('id','desc')->get()->toArray();
    		return response()->json(['status' => 'success','message' => 'Driver Out Of Town Trip Details', 'data'=>$data]);
    	} catch (Exception $e) {
    		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
    	}
    }
}
