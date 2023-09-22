<?php
namespace App\Http\Controllers\Api\User;
use App\Jobs\sendNotification;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\PasswordResetRequest;
use App\Helpers\GlobalH;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Ixudra\Curl\Facades\Curl;
use Carbon\Carbon;
use App\Models\Setting;
use App\Models\DriverDetails;
use App\Models\DriverDocuments;
use App\Models\Promocodes;
use App\Models\Vehicle;
use App\Models\Otp;
use App\Models\TransactionDetail;
use App\Models\Wallet, App\Models\WalletHistory, App\Models\UserCredits, App\Models\ParcelDetail;
use Event;
use PushNotification;
use App\Models\CompanyDetail;
use App\Models\Booking;
use App\Models\Notifications;
use App\Models\PopupNotification;
use App\Models\LinerideBooking;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Log;

class UserController extends Controller{
	public function __construct(){}
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
| User Login
|--------------------------------------------------------------------------
|
*/
public function login(Request $request){
	if($request->social_media && $request->social_id){
		$validation_array = [
		'social_media' 		=> 'required',
		'social_id' 		=> 'required',
		'device_type'		=> 'required',
		'device_token'		=> 'required',
		];
	}else{
		$validation_array = [
		'email' 		=> 'required',
		'password' 		=> 'required|min:6',
		'device_type'	=> 'required',
		'device_token'	=> 'required',
		];
	}
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
	}
	try{
		// Log::info(json_encode($request->email));
		if($request->social_media && $request->social_id){
			$user = User::firstOrCreate([
				"first_name"	=>request('first_name'),
				"last_name"		=>request('last_name'),
				"email"			=>request('email')
				]);
			$user->social_id = request('social_id');
			$user->social_media = request('social_media');
			if($user->is_verify == '0'){
				$token = mt_rand(1000, 9999);
				$user->otp = $token;
			}
			try {
				if($request->avatar){
					$filename = Str::random(10).'.jpg';
					file_put_contents(storage_path().'/app/public/avatar/' . $filename, file_get_contents($request->avatar));
					$user->avatar = $filename;
				} else {
					$user->avatar = "default.png";
				}
			}catch (\Intervention\Image\Exception\NotReadableException $e) {

			}
			$user->save();
			$token = JWTAuth::fromUser($user);
			$userdata = User::where('id',$user->id)->first();
			$userdata->login_token = $token;
			$userdata->device_token = $request->device_token;
			$userdata->device_type = $request->device_type;
			$userdata->status = 'active';
			$userdata->user_type = 'user';
			$userdata->save();
			$data['token']=$token;
			$data['user']=$userdata;
			return response()->json(['status' => 'success','message' => 'Login successfully','data'=>$data], 200);
		}else{
			if(filter_var($request->email, FILTER_VALIDATE_EMAIL) ){
				$credentials = $request->only('email', 'password','user_type');
			}else{
				$credentials =[ 'contact_number'=>$request->email,'password'=>$request->password ,'user_type'=>$request->user_type];
			}
			$data= [];
			try {
				if(! $token = JWTAuth::attempt($credentials)) {
					return response()->json(['status' => 'error','message' => 'Invalid Credentials, Please try again', 'data' => (object)[]], 200);
				}
				if(filter_var($request->email, FILTER_VALIDATE_EMAIL) ){
					$userTypeCheck = User::where('email',$request->get('email'))->where('status','active')->first();
				}else{
					$userTypeCheck = User::where('contact_number',$request->get('email'))->where('status','active')->first();
				}
				if(!empty($userTypeCheck)){
					if($userTypeCheck->user_type == 'user'){
						if($userTypeCheck->status != 'active'){
							return response()->json(['status' => 'error','message' => 'You are not able to login this application','data'		=> (object)[]], 200);
						}
					}
					if($userTypeCheck->user_type == 'driver'){
						if($userTypeCheck->status != 'active'){
							return response()->json(['status' => 'error','message' => 'You are not able to login this application','data'=> (object)[]], 200);
						}
					}
					if($userTypeCheck->user_type == 'company'){
						if($userTypeCheck->status != 'active'){
							return response()->json(['status' => 'error','message' => 'You are not able to login this application','data'=> (object)[]], 200);
						}
					}
				}else{
					if(filter_var($request->email, FILTER_VALIDATE_EMAIL) ){
						$userTypeCheck = User::where('email',$request->get('email'))->where('status','inactive')->first();
					}else{
						$userTypeCheck = User::where('contact_number',$request->get('email'))->where('status','inactive')->first();
					}
					$data['object'] = (object)[];
					if(!empty($userTypeCheck->reason_for_inactive))
					{
						$data['reason_for_inactive'] = $userTypeCheck->reason_for_inactive;
					} else {
						$data['reason_for_inactive'] = "You have Deactivated your account.";
					}
					return response()->json(['status' => 'error','message' => 'You are not able to login this application because of '.$data['reason_for_inactive'],'data'		=> (object)[]], 200);
				}
			}catch (JWTException $e) {
				return response()->json(['status' => 'error','message' => 'could_not_create_token', 'data' => (object)[]], 200);
			}
			if($userTypeCheck->user_type == 'user'){
				$data['token'] = $token;
				$data['user'] = $userTypeCheck;
				$userTypeCheck->login_token = $token;
				$userTypeCheck->device_token = $request->device_token;
				$userTypeCheck->device_type = $request->device_type;
				$userTypeCheck->save();
				User::where('id',$userTypeCheck->id)->update([
					'login_token' => $data['token'],
					]);
				// Sent otp code
				$otpNumber = random_int(1000, 9999);
				$checkContactNumInUser = User::where('contact_number',$userTypeCheck->contact_number)->first();
				// dd($checkContactNumInUser);
				if($checkContactNumInUser !== null){
					$checkIfUserOtpExist = Otp::where('email',$checkContactNumInUser->email)->where('contact_number',(string)$checkContactNumInUser->contact_number)->first();
					if($checkIfUserOtpExist !== null){
						Otp::where('id',$checkIfUserOtpExist->id)
						->where('contact_number',(string)$checkContactNumInUser->contact_number)
						->where('email',$checkContactNumInUser->email)
						->update([
							'otp_number'    => $otpNumber,
							'otp_expire'    => $checkIfUserOtpExist->updated_at->addSeconds(180)
							]);
					}else{
						$UserOtpCreated = Otp::create([
							'email'         => $checkContactNumInUser->email,
							'contact_number' => (string)$checkContactNumInUser->contact_number,
							'otp_number'    => $otpNumber,
							]);
						Otp::where('id',$UserOtpCreated->id)->update([
							'otp_expire'    => $UserOtpCreated->created_at->addSeconds(180)
							]);
					}
					$text = 'Your OTP is: '.$otpNumber;
					$emailcontent = array (
						'text' => $text,
						'title' => 'Thanks for join Ruerun for Ride, Please use Below OTP for Complete SignUp Process. You will need to complete Sign Up Process enter OTP.',
						'userName' => $checkContactNumInUser->first_name
						);
					$details['email'] = $checkContactNumInUser->email;
					$details['username'] = $checkContactNumInUser->first_name;
					$details['subject'] = 'OTP Confirmation';
					dispatch(new sendNotification($details,$emailcontent));
					$data['otpNumber'] = $otpNumber;
				}
				// sent otp code
				return response()->json(['status' => 'success','message' => 'Login successfully','data'=>$data], 200);
			}
			if($userTypeCheck->user_type == 'driver'){
				// if($userTypeCheck->vehicle_doc_status != "approved" || $userTypeCheck->doc_status != "approved"){
				// 	return response()->json(['status' => 'error','message' => 'Your Account is not Verified yet','data'		=> (object)[]], 200);
				// }
				$data['token'] = $token;
				$data['user'] = $userTypeCheck;
				$user_detail = DriverDetails::where('driver_id',$userTypeCheck->id)->first();
				$userTypeCheck->login_token = $token;
				$userTypeCheck->device_token = $request->device_token;
				$userTypeCheck->device_type = $request->device_type;
				$userTypeCheck->save();
				$user_model = User::find($userTypeCheck->id);
				$user_model->availability_status = "on";
				$user_model->save();
				// User::where('id',$userTypeCheck->id)->update([
				// 	'login_token' => 'Bearer'.' '.$data['token'],
				// ]);
				// Sent otp code
				$otpNumber = random_int(1000, 9999);
				$checkContactNumInUser = User::where('contact_number',$userTypeCheck->contact_number)->first();
				// $data['emails'] = $request->email;
				// dd($request->email);
				if($checkContactNumInUser !== null){
					$checkIfUserOtpExist = Otp::where('email',$checkContactNumInUser->email)->where('contact_number',(string)$checkContactNumInUser->contact_number)->first();
					if($checkIfUserOtpExist !== null){
						Otp::where('id',$checkIfUserOtpExist->id)
						->where('contact_number',(string)$checkContactNumInUser->contact_number)
						->where('email',$checkContactNumInUser->email)
						->update([
							'otp_number'    => $otpNumber,
							'otp_expire'    => $checkIfUserOtpExist->updated_at->addSeconds(180)
							]);
					}else{
						$UserOtpCreated = Otp::create([
							'email'         => $checkContactNumInUser->email,
							'contact_number' => (string)$checkContactNumInUser->contact_number,
							'otp_number'    => $otpNumber,
							]);
						Otp::where('id',$UserOtpCreated->id)->update([
							'otp_expire'    => $UserOtpCreated->created_at->addSeconds(180)
							]);
					}
					$text = 'Your OTP is: '.$otpNumber;
					$emailcontent = array (
						'text' => $text,
						'title' => 'Thanks for join Ruerun for Ride, Please use Below OTP for Complete SignUp Process. You will need to complete Sign Up Process enter OTP.',
						'userName' => $checkContactNumInUser->first_name
						);
					$details['email'] = $checkContactNumInUser->email;
					$details['username'] = $checkContactNumInUser->first_name;
					$details['subject'] = 'OTP Confirmation';
					dispatch(new sendNotification($details,$emailcontent));
					$data['otpNumber'] = $otpNumber;
				}
				// sent otp code
				if(!empty($user_detail)){
					$data['vehicle_model']=$user_detail->vehicle_model;
					$data['vehicle_plate']=$user_detail->vehicle_plate;
				}
				if(!empty($user_detail->ride_type)){
					$data['ride_type_screen']="1";
				} else {
					$data['ride_type_screen']="0";
				}
				if(!empty($userTypeCheck->vehicle_id)){
					$data['vehicle_screen']="1";
				} else {
					$data['vehicle_screen']="0";
				}
				if($userTypeCheck->driver_doc == "0"){
					$data['driver_doc_screen']="0";
				} else {
					$data['driver_doc_screen']="1";
				}
				if($userTypeCheck->car_doc == "0"){
					$data['car_doc_screen']="0";
				} else {
					$data['car_doc_screen']="1";
				}

				if($data['ride_type_screen'] == "1" and $data['vehicle_screen']== "1" and $data['driver_doc_screen']== "1" and $data['car_doc_screen']== "1"){
					if($userTypeCheck->vehicle_doc_status != "approved" || $userTypeCheck->doc_status != "approved"){
						return response()->json(['status' => 'error','message' => 'Your Account is not Verified yet','data'		=> (object)[]], 200);
					}
				}
				
				return response()->json(['status' => 'success','message' => 'Login successfully','data'=>$data], 200);
			}
			if($userTypeCheck->user_type == 'company'){
				$data['token'] = $token;
				$data['user'] = $userTypeCheck;
				$user_detail = CompanyDetail::where('company_id',$userTypeCheck->id)->first();
				$user_detail_chack = DriverDetails::where('driver_id',$userTypeCheck->id)->first();
				$userTypeCheck->login_token = $token;
				$userTypeCheck->device_token = $request->device_token;
				$userTypeCheck->device_type = $request->device_type;
				$userTypeCheck->save();
				User::where('id',$userTypeCheck->id)->update([
					'login_token' => $data['token'],
					]);
				$otpNumber = random_int(1000, 9999);
				$checkContactNumInUser = User::where('contact_number',$userTypeCheck->contact_number)->first();
				if($checkContactNumInUser !== null){
					$checkIfUserOtpExist = Otp::where('email',$checkContactNumInUser->email)->where('contact_number',(string)$checkContactNumInUser->contact_number)->first();
					if($checkIfUserOtpExist !== null){
						Otp::where('id',$checkIfUserOtpExist->id)
						->where('contact_number',(string)$checkContactNumInUser->contact_number)
						->where('email',$checkContactNumInUser->email)
						->update([
							'otp_number'    => $otpNumber,
							'otp_expire'    => $checkIfUserOtpExist->updated_at->addSeconds(180)
							]);
					}else{
						$UserOtpCreated = Otp::create([
							'email'         => $checkContactNumInUser->email,
							'contact_number' => (string)$checkContactNumInUser->contact_number,
							'otp_number'    => $otpNumber,
							]);
						Otp::where('id',$UserOtpCreated->id)->update([
							'otp_expire'    => $UserOtpCreated->created_at->addSeconds(180)
							]);
					}
					$text = 'Your OTP is: '.$otpNumber;
					$emailcontent = array (
						'text' => $text,
						'title' => 'Thanks for join Ruerun for Ride, Please use Below OTP for Complete SignUp Process. You will need to complete Sign Up Process enter OTP.',
						'userName' => $checkContactNumInUser->first_name
						);
					$details['email'] = $checkContactNumInUser->email;
					$details['username'] = $checkContactNumInUser->first_name;
					$details['subject'] = 'OTP Confirmation';
					dispatch(new sendNotification($details,$emailcontent));
					$data['otpNumber'] = $otpNumber;
				}
				// sent otp code
				if(!empty($user_detail)){
					$data['recipient_name']=$user_detail->recipient_name;
					$data['job_title']=$user_detail->job_title;
					$data['company_size']=$user_detail->company_size;
					$data['website']=$user_detail->website;
				}
				if(!empty($user_detail_chack)){
					$data['vehicle_model']=$user_detail->vehicle_model;
					$data['vehicle_plate']=$user_detail->vehicle_plate;
				}
				if(!empty($user_detail_chack->ride_type)){
					$data['ride_type_screen']="1";
				} else {
					$data['ride_type_screen']="0";
				}
				if(!empty($userTypeCheck->vehicle_id)){
					$data['vehicle_screen']="1";
				} else {
					$data['vehicle_screen']="0";
				}
				if($userTypeCheck->driver_doc == "0"){
					$data['driver_doc_screen']="0";
				} else {
					$data['driver_doc_screen']="1";
				}
				if($userTypeCheck->car_doc == "0"){
					$data['car_doc_screen']="0";
				} else {
					$data['car_doc_screen']="1";
				}
				return response()->json(['status' => 'success','message' => 'Login successfully','data'=>$data], 200);
			}
		}
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => 'Something went Wrong.....', 'data' => (object)[]],200);
	}
}

/*
|--------------------------------------------------------------------------
| User Register
|--------------------------------------------------------------------------
|
*/
public function register(Request $request){
	$validation_array =[
	'first_name'     => 'required',
	'last_name'      => 'required',
	'email'    =>  'required|email|unique:users,email,NULL,id,deleted_at,NULL',
		// 'email'          => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
	'contact_number'          => 'required|unique:users,contact_number,NULL,id,deleted_at,NULL',
		// 'contact_number' => 'required|unique:users',
		// 'email' 		 => 'required|unique:users',
		// 'contact_number' => 'required|unique:users',
	'zipcode'       => 'required',
	'address'        => 'required',
	'country'        => 'required',
	'city_id'        => 'required',
	'state_id'       => 'required',
	'add_latitude'   => 'required',
	'add_longitude'  => 'required',
	'password' 		 => 'min:8|required_with:password_confirmation|same:password_confirmation',
	'password_confirmation'=> 'required',
	'avatar' 		 => 'image|mimes:jpeg,png,jpg,gif|max:3000',
	'device_token' => 'required',
	'device_type' => 'required',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message'   => $validation->errors()->first(),'data'=> (object)[]]);
	}
	try {
		if($request->hasFile('avatar')){
			$file = $request->file('avatar');
			$extension = $file->getClientOriginalExtension();
			$filename = Str::random(10).'.'.$extension;
			Storage::disk('public')->putFileAs('avatar', $file,$filename);
		}else{
			$filename = 'default.png';
		}
		if($request->get('ref_id')){
			$uuid = User::where('uuid',$request->get('ref_id'))->first();
			if (!empty($uuid)) {
				$ref_id=$uuid->uuid;
			}else{
				return response()->json(['status' => 'error','message' => 'Enter valid invite code','data'=> (object)[]]);
			}
		} else{
			$ref_id='';
		}
		$data['avatar']				=$filename;
		$data['first_name']			=request('first_name');
		$data['last_name']			=request('last_name');
		$data['email']				=request('email');
		$data['contact_number']		=request('contact_number');
		$data['country_code']		=request('country_code');
		$data['address']			=request('address');
		$data['zipcode']			=request('zipcode');
		$data['country']			=request('country');
		$data['city_id']			=request('city_id');
		$data['state_id']			=request('state_id');
		$data['ref_id']				=$ref_id;
		$data['password']			=bcrypt(request('password'));
		$data['user_type']			='user';
		$data['status'] 			='active';
		$data['sign_up_as']     	='app';
		$data['add_latitude']	    =request('add_latitude');
		$data['add_longitude']		=request('add_longitude');
		$data['driver_signup_as']	=request('driver_signup_as');
		$data['device_token']	   =request('device_token');
		$data['device_type']	   =request('device_type');
		$userdata = User::Create($data);
		$user = User::where('id',$userdata->id)->first();
		$data1['token'] = JWTAuth::fromUser($userdata);
		$data1['user'] = $user;
		$otpNumber = '';


		if(!empty($userdata)){
			User::where('id',$userdata->id)->update(['login_token' => $data1['token']]);
			$otpNumber = random_int(1000, 9999);
			$UserOtpCreated = Otp::create([
				'email'         => $user->email,
				'contact_number' => (string)$user->contact_number,
				'otp_number'    => $otpNumber,
				]);
			$text = 'Your OTP is: '.$otpNumber;
			$emailcontent = array (
				'text' => $text,
				'title' => 'Thanks for join Ruerun App, Please use Below OTP for Contact Number Verification.',
				'userName' => $user->first_name
				);
			$details['email'] = $user->email;
			$details['username'] = $user->first_name;
			$details['subject'] = 'Welcome to Ruerun, OTP Confirmation';
			dispatch(new sendNotification($details,$emailcontent));
		}
		// $data1['otpNumber'] = $otpNumber;
		// $data['message'] =  'User Registration';
		// $data['type'] = 'registered';
		// $data['user_id'] = $userdata->id;
		// $data['notification'] = Event::dispatch('send-notification-assigned-user',array($userdata,$data));

		$total_first_name = strlen($request->first_name);
		if($total_first_name == "1"){
			$total_first_name = $request->first_name.random_int(1000000, 9999999);
		} elseif($total_first_name == "2"){
			$total_first_name = $request->first_name.random_int(100000, 999999);
		} elseif($total_first_name == "3"){
			$total_first_name = $request->first_name.random_int(10000, 99999);
		} elseif($total_first_name == "4"){
			$total_first_name = $request->first_name.random_int(1000, 9999);
		} else {
			$total_first_name1 = substr($request->first_name, 0, 4);
			$total_first_name = $total_first_name1.random_int(1000, 9999);
		}
		$uuid = strtoupper($total_first_name);
		User::where('id',$userdata->id)->update(['uuid' => $uuid]);	

		return response()->json(['status' => 'success','message' => 'You are successfully Register!','data' => $data1]);
	}catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}


public function createUserCompanyDetail(Request $request){
	$validator = Validator::make($request->all(), [
		'recipient_name'    => ['required'],
		'job_title'         => ['required'],
		'company_size'      => ['required'],
		'user_id'           => ['required'],
		]);
	if ($validator->fails()){
		return response()->json(['status' => 'error', 'message' => $validator->messages()->first() ]);
	}
	try{
		$driver = User::find($request->user_id);
		if($driver->driver_signup_as === 'company'){
			$companydetails = CompanyDetail::create([
				'recipient_name'        => $request->recipient_name,
				'job_title'             => $request->job_title,
				'company_size'          => $request->company_size,
				'website'               => $request->website,
				'company_id'            => $driver->id,
				'user_id'               => $driver->id,
				]);
			User::where('id', $driver->id)->update(['company_id'=> $companydetails->id, 'company_name'=> request('company_name')]);
		}
		$status = 'success';
		$message = 'User Company Details saved';
		return response()->json(['status' => $status, 'message' => $message]);
	}catch(\Exception $e){
		return response()->json(['status' => 'error', 'message' => $e->getMessage() ]);
	}catch(\Illuminate\Database\QueryException $e){
		return response()->json(['status' => 'error', 'message' => $e->getMessage() ]);
	}
}

/*
|--------------------------------------------------------------------------
| Send Otp
|--------------------------------------------------------------------------
|
*/
public function sendOtp(Request $request){
	$validator =  Validator::make($request->all(),[
		'contact_number' => 'required|min:7'
		]);
	if($validator->fails()){
		return response()->json(['status' => 'error','message'=> $validator->messages()->first()]);
	}
	try{
		$otpNumber = random_int(1000, 9999);
		$checkContactNumInUser = User::where('contact_number',$request->get('contact_number'))->first();
		if($checkContactNumInUser !== null){
			$checkIfUserOtpExist = Otp::where('email',$checkContactNumInUser->email)->where('contact_number',(string)$checkContactNumInUser->contact_number)->first();
			if($checkIfUserOtpExist !== null){
				Otp::where('id',$checkIfUserOtpExist->id)
				->where('contact_number',(string)$checkContactNumInUser->contact_number)
				->where('email',$checkContactNumInUser->email)
				->update([
					'otp_number'    => $otpNumber,
					'otp_expire'    => $checkIfUserOtpExist->updated_at->addSeconds(180)
					]);
			}else{
				$UserOtpCreated = Otp::create([
					'email'         => $checkContactNumInUser->email,
					'contact_number' => (string)$checkContactNumInUser->contact_number,
					'otp_number'    => $otpNumber,
					]);
				Otp::where('id',$UserOtpCreated->id)->update([
					'otp_expire'    => $UserOtpCreated->created_at->addSeconds(180)
					]);
			}
			$text = 'Your OTP is: '.$otpNumber;
			$emailcontent = array (
				'text' => $text,
				'title' => 'Thanks for join Ruerun for Ride, Please use Below OTP for Complete SignUp Process. You will need to complete Sign Up Process enter OTP.',
				'userName' => $checkContactNumInUser->first_name
				);
			$details['email'] = $checkContactNumInUser->email;
			$details['username'] = $checkContactNumInUser->first_name;
			$details['subject'] = 'OTP Confirmation';
			dispatch(new sendNotification($details,$emailcontent));
		}else{
			return response()->json(['status' => 'error','message'=> 'Mobile number does not exist.']);
		}
		return response()->json(['status'=> 'success','message' => 'Otp sent successfully','data'=> $otpNumber ]);
	}catch (\Exception $exception){
		return response()->json(['message'=> $exception->getMessage()]);
	}
}

/*
|--------------------------------------------------------------------------
| Verify Otp
|--------------------------------------------------------------------------
|
*/
public function verifyOtp(Request $request){
	$validator =  Validator::make($request->all(),[
		'contact_number' => 'required|min:7',
		'otp_number'    => 'required|max:4|min:4'
		]);
	if($validator->fails()){
		return response()->json(['status'    => 'error','message'   => $validator->messages()->first()]);
	}
	try{
		$getOtpData = Otp::where('otp_number',$request->get('otp_number'))->where('contact_number',$request->get('contact_number'))->first();
		if($getOtpData !== null){
			if( Carbon::now() >= Carbon::parse()){
				return response()->json(['status' => 'error','message' => 'Otp Expired']);
			}
			$getOtpuser = Otp::with('user')->where('contact_number',$request->get('contact_number'))->first();
			return response()->json(['status'    => 'success','message'   => 'OTP is Verified.','data'		=> $getOtpuser]);
		}
		return response()->json(['status'    => 'error','message'   => 'Invalid Otp Details',]);
	}catch (\Exception $exception){
		return response()->json(['message'   => $exception->getMessage()]);
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

			$user_data->first_name      = request('first_name');
			$user_data->last_name       = request('last_name');
			$user_data->email           = request('email');
			$user_data->contact_number  = $contact_number;
			$user_data->address         = request('address');
			$user_data->country         = request('country');
			$user_data->state_id        = request('state_id');
			$user_data->city_id         = request('city_id');
			$user_data->add_latitude    = request('add_latitude');
			$user_data->add_longitude   = request('add_longitude');
			$user_data->country_code    = request('country_code');
			$user_data->avatar          = $filename;
			$user_data->company_name    = @$company_name;
			$user_data->save();
			return response()->json(['status' => 'success','message' => 'Profile Update Successfully','data' => $user_data]);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
|
*/
public function changePassword(Request $request){
	$validation_array =[
	'old_password'        => 'required|string|min:6',
	'new_password'        => 'required|string|min:6',
	'confirm_password'    => 'required|string|min:6',
	];
	$validation = Validator::make($request->all(),$validation_array);
	if($validation->fails()){
		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
	}
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status' => 'error','message' => "Invalid Token ..."],200);
		}
		if($user !== null){
			$password     = $user->password;
			$old_password = request('old_password');
			$new_password = request('new_password');
			$c_password   = request('confirm_password');
			if($new_password != $c_password){
				return response()->json(['status' => 'error','message' => 'Your password does not match with confirm password']);
			}
			if(isset($password)) {
				if($old_password == $new_password){
					return response(['status' => 'error','message'=>'New Password cannot be same as your current password. Please choose a different password.']);
				}else{
					if(\Hash::check($old_password, $password)){
						$user->password = \Hash::Make($new_password);
						$user->save();
						return response()->json(['status' => 'success','message' => 'Your password change successfully.']);
					}else{
						return response()->json(['status' => 'error','message' => 'Your current password does not matches with the password you provided. Please try again.']);
					}
				}
			}else{
				return response()->json(['status' => 'error','message' => 'User not available.']);
			}
		}else{
			return response()->json(['status'    => 'error','message'   => "You are not able login from this "]);
		}
	}catch(\Exception $e){
		return response()->json(['status'    => 'error','message'   => $e->getMessage()],200);
	}
}

/*
|--------------------------------------------------------------------------
| Create New User Trip
|--------------------------------------------------------------------------
|
*/
// public function createUserTrip(Request $request){
// 	$validation_array =[
// 		'pick_up_location'    => 'required',
// 		'drop_location'       => 'required',
// 		'start_latitude'      => 'required',
// 		'start_longitude'     => 'required',
// 		'drop_latitude'       => 'required',
// 		'drop_longitude'      => 'required',
// 		'service_id'      	  => 'required',
// 	];
// 	$validation = Validator::make($request->all(),$validation_array);
// 	if($validation->fails()){
// 		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
// 	}
// 	try{
// 		if($request->promo_id){
// 			$promo_id = $request->promo_id;
// 		}else{
// 			$promo_id = '';
// 		}
// 		$user = JWTAuth::parseToken()->authenticate();
// 		if(!$user){
// 			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
// 		}
// 		if($request->extra_notes){
// 			$extra_notes = $request->extra_notes;
// 		}else{
// 			$extra_notes = '';
// 		}
// 		if($request->booking_type == 'immediate'){
// 			$booking_date = Carbon::now()->format('Y-m-d');
// 			$booking_start_time = Carbon::now()->format('H:i A');
// 			$trips = Booking::create([
// 				'booking_type'             => $request->booking_type,
// 				'pick_up_location'         => $request->pick_up_location,
// 				'drop_location'            => $request->drop_location,
// 				'start_latitude'           => $request->start_latitude,
// 				'start_longitude'          => $request->start_longitude,
// 				'drop_latitude'            => $request->drop_latitude,
// 				'drop_longitude'           => $request->drop_longitude,
// 				'otp'                      => mt_rand(1000, 9999),
// 				'booking_date'             => $booking_date,
// 				'booking_start_time'       => $booking_start_time,
// 				'extra_notes'              => $extra_notes,
// 				'ride_setting_id'          => $request->service_id,
// 				'user_id'                  => $user->id,
// 			]);
//             $text = 'Ride Booking OTP is: '.$trips->otp;
//             $emailcontent = array (
//                 'text' => $text,
//                 'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
//                 'userName' => $user->first_name.' '.$user->last_name
//             );
//             $details['email'] = $user->email;
//             $details['username'] = $user->first_name.' '.$user->last_name;
//             $details['subject'] = 'Ride Booking OTP';
//             dispatch(new sendNotification($details,$emailcontent));
// 			if(!empty($trips)){
// 				$tripdetail = Booking::find($trips->id);
// 				$path = storage_path().'/logs/'.date("d-m-Y").'_aircall_logs.log';
// 				file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Data Away response: ".$tripdetail."\n", FILE_APPEND);
// 				return response()->json(['status' => 'success','message' => 'Immediate Ride Booked Successfully','data' => $tripdetail]);
// 			}else{
// 				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 			}
// 		} elseif($request->booking_type == 'schedule') {
//             if($request->booking_date){
//                 $booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
//             }else{
//                 $booking_date = Carbon::now()->format('Y-m-d');
//             }

// 			$trips = Booking::create([
// 				'booking_type'             => $request->booking_type,
// 				'pick_up_location'         => $request->pick_up_location,
// 				'drop_location'            => $request->drop_location,
// 				'start_latitude'           => $request->start_latitude,
// 				'start_longitude'          => $request->start_longitude,
// 				'drop_latitude'            => $request->drop_latitude,
// 				'drop_longitude'           => $request->drop_longitude,
// 				'otp'                      => mt_rand(1000, 9999),
// 				'booking_date'             => $booking_date,
// 				'booking_start_time'       => $request->booking_start_time,
// 				'booking_end_time'         => $request->booking_end_time,
// 				'extra_notes'              => $extra_notes,
// 				'ride_setting_id'          => $request->service_id,
// 				'total_luggage'            => $request->total_luggage,
// 				'user_id'                  => $user->id,
// 			]);
//             $text = 'Ride Booking OTP is: '.$trips->otp;
//             $emailcontent = array (
//                 'text' => $text,
//                 'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
//                 'userName' => $user->first_name.' '.$user->last_name
//             );
//             $details['email'] = $user->email;
//             $details['username'] = $user->first_name.' '.$user->last_name;
//             $details['subject'] = 'Ride Booking OTP';
//             dispatch(new sendNotification($details,$emailcontent));
// 			if(!empty($trips)){
// 				$tripdetail = Booking::find($trips->id);
// 				return response()->json(['status' => 'success','message' => 'Schedule Booking Booked Successfully','data' => $tripdetail]);
// 			}else{
// 				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 			}
// 		} elseif($request->booking_type == 'out_of_town') {
// 			if($request->seats == null){
// 				$seats = '';
// 			} else {
// 				$seats = $request->seats;
// 			}
//             $booking_date='';
//             if($request->booking_date){
//                 $booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
//             }else{
//                 $booking_date = Carbon::now()->format('Y-m-d');
//             }
// 			$trips = Booking::create([
// 				'booking_type'             => $request->booking_type,
// 				'pick_up_location'         => $request->pick_up_location,
// 				'drop_location'            => $request->drop_location,
// 				'start_latitude'           => $request->start_latitude,
// 				'start_longitude'          => $request->start_longitude,
// 				'drop_latitude'            => $request->drop_latitude,
// 				'drop_longitude'           => $request->drop_longitude,
// 				'otp'                      => mt_rand(1000, 9999),
// 				'booking_date'             => $booking_date,
// 				'booking_start_time'       => $request->booking_start_time,
// 				'booking_end_time'         => $request->booking_end_time,
// 				'extra_notes'              => $extra_notes,
// 				'ride_setting_id'          => $request->service_id,
// 				'total_luggage'            => $request->total_luggage,
// 				'user_id'                  => $user->id,
// 				'taxi_hailing'             => $request->taxi_hailing,
// 				'seats'                    => $seats,
// 			]);
//             $text = 'Ride Booking OTP is: '.$trips->otp;
//             $emailcontent = array (
//                 'text' => $text,
//                 'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
//                 'userName' => $user->first_name.' '.$user->last_name
//             );
//             $details['email'] = $user->email;
//             $details['username'] = $user->first_name.' '.$user->last_name;
//             $details['subject'] = 'Ride Booking OTP';
//             dispatch(new sendNotification($details,$emailcontent));
// 			if(!empty($trips)){
// 				$tripdetail = Booking::find($trips->id);
// 				return response()->json(['status' => 'success','message' => 'Out Of Town Booking Booked Successfully','data' => $tripdetail]);
// 			}else{
// 				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 			}
// 		} else {
// 			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 		}
// 	} catch (Exception $e) {
// 		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
// 	}
// }

public function createUserTrip(Request $request){
	$validation_array =[
	'pick_up_location'    => 'required',
	'drop_location'       => 'required',
	'start_latitude'      => 'required',
	'start_longitude'     => 'required',
	'drop_latitude'       => 'required',
	'drop_longitude'      => 'required',
	'service_id'      	  => 'required',
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
		if($request->extra_notes){
			$extra_notes = $request->extra_notes;
		}else{
			$extra_notes = '';
		}
		if($request->booking_type == 'immediate'){
			// $booking_date = Carbon::now()->format('Y-m-d');
			// $booking_start_time = Carbon::now()->format('H:i A');

			// $check_booking_start_time = Carbon::now()->format(' h:i A');
			// // dd($booking_start_time);

			// $jk_booking_date = $booking_date.$check_booking_start_time;
			// // date_default_timezone_set('Asia/Kolkata');
			// date_default_timezone_set('America/New_York');
			// $objDateTime = new DateTime($jk_booking_date);
			// // $objDateTimeZone = new DateTimeZone('Europe/Paris');
			// $objDateTimeZone = new DateTimeZone('America/New_York');			
			// $objDateTime->setTimeZone($objDateTimeZone);
			// $timezone_array = $objDateTime->format('Y-m-d');

			$checkBooking = Booking::where('user_id', @$user->id)
			->where('pick_up_location', @$request->pick_up_location)
			->where('drop_location', @$request->drop_location)
			->where('booking_start_time', @$request->booking_start_time)
//                ->where('booking_end_time', @$request->booking_end_time)
			->where('booking_date', @$request->booking_date)
			->whereNotIn('trip_status', ["cancelled","pending","completed"])
			->first();
			if(!empty($checkBooking)){
				return response()->json(['status' => 'error','message' => 'You have already booked for this Locations and Time Intervals']);
			}

			$trips = Booking::create([
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
				'booking_end_time'         => $request->booking_end_time,
				'extra_notes'              => $extra_notes,
				'ride_setting_id'          => $request->service_id,
				'user_id'                  => $user->id,
				]);
			$text = 'Ride Booking OTP is: '.$trips->otp;
			$emailcontent = array (
				'text' => $text,
				'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
				'userName' => $user->first_name.' '.$user->last_name
				);
			$details['email'] = $user->email;
			$details['username'] = $user->first_name.' '.$user->last_name;
			$details['subject'] = 'Ride Booking OTP';
			dispatch(new sendNotification($details,$emailcontent));
			if(!empty($trips)){
				$tripdetail = Booking::find($trips->id);
				$path = storage_path().'/logs/'.date("d-m-Y").'_aircall_logs.log';
				file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Data Away response: ".$tripdetail."\n", FILE_APPEND);
				return response()->json(['status' => 'success','message' => 'Immediate Ride Booked Successfully','data' => $tripdetail]);
			}else{
				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
			}
		} elseif($request->booking_type == 'schedule') {
			$parceldetail = ParcelDetail::find($request->parceldetail_id);
			if(!empty($parceldetail))
			{
				// if($request->booking_date){
				// 	$booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
				// }else{
				// 	$booking_date = Carbon::now()->format('Y-m-d');
				// }

				$parcel = ParcelDetail::where('id', $request->parceldetail_id)->update([ 'booking_date'=> $request->booking_date]);
				if(!empty($parcel)){
					return response()->json(['status' => 'success','message' => 'Schedule Booking Booked Successfully','data' => $parceldetail]);
				}else{
					return response()->json(['status' => 'error','message' => 'Something went Wrong']);
				}

			} else {
				// if($request->booking_date){
				// 	$booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
				// }else{
				// 	$booking_date = Carbon::now()->format('Y-m-d');
				// }

				// $jk_booking_date = $booking_date." ".$request->booking_start_time;
				// $jk1_booking_date = $booking_date." ".$request->booking_end_time;

				// // America/New_York
				// // date_default_timezone_set('Asia/Kolkata');
				// date_default_timezone_set('America/New_York');
				// $objDateTime = new DateTime($jk_booking_date);
				// // $objDateTimeZone = new DateTimeZone('Europe/Paris');
				// $objDateTimeZone = new DateTimeZone('America/New_York');
				// $objDateTime->setTimeZone($objDateTimeZone);
				// $timezone_array = $objDateTime->format('Y-m-d');

				// // $date=date_create($jk_booking_date,timezone_open("Asia/Kolkata"));
				// // date_timezone_set($date,timezone_open("Europe/Paris"));
				// $date=date_create($jk_booking_date,timezone_open("America/New_York"));
				// date_timezone_set($date,timezone_open("America/New_York"));
				// $booking_start_time =  date_format($date,"H:i A");

				// // $date1=date_create($jk1_booking_date,timezone_open("Asia/Kolkata"));
				// // date_timezone_set($date1,timezone_open("Europe/Paris"));
				// $date1=date_create($jk1_booking_date,timezone_open("America/New_York"));
				// date_timezone_set($date1,timezone_open("America/New_York"));
				// $booking_end_time =  date_format($date1,"H:i A");

				$checkBooking = Booking::where('user_id', @$user->id)
				->where('pick_up_location', @$request->pick_up_location)
				->where('drop_location', @$request->drop_location)
				->where('booking_start_time', @$request->booking_start_time)
				->where('booking_end_time', @$request->booking_end_time)
				->where('booking_date', @$request->booking_date)
				->whereNotIn('trip_status', ["cancelled","pending","completed"])
				->first();
				if(!empty($checkBooking)){
					return response()->json(['status' => 'error','message' => 'You have already booked for this Locations and Time Intervals']);
				}

				$trips = Booking::create([
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
					'booking_end_time'         => $request->booking_end_time,
					'extra_notes'              => $extra_notes,
					'ride_setting_id'          => $request->service_id,
					'total_luggage'            => $request->total_luggage,
					'user_id'                  => $user->id,
					]);
				$text = 'Ride Booking OTP is: '.$trips->otp;
				$emailcontent = array (
					'text' => $text,
					'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
					'userName' => $user->first_name.' '.$user->last_name
					);
				$details['email'] = $user->email;
				$details['username'] = $user->first_name.' '.$user->last_name;
				$details['subject'] = 'Ride Booking OTP';
				dispatch(new sendNotification($details,$emailcontent));
				if(!empty($trips)){
					$tripdetail = Booking::find($trips->id);
					return response()->json(['status' => 'success','message' => 'Schedule Booking Booked Successfully','data' => $tripdetail]);
				}else{
					return response()->json(['status' => 'error','message' => 'Something went Wrong']);
				}

			}

		} elseif($request->booking_type == 'out_of_town') {
			if($request->seats == null){
				$seats = 'vip';
			} else {
				$seats = $request->seats;
			}
			// $booking_date='';
			// if($request->booking_date){
			// 	$booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
			// }else{
			// 	$booking_date = Carbon::now()->format('Y-m-d');
			// }

			// $jk_booking_date = $booking_date." ".$request->booking_start_time;
			// $jk1_booking_date = $booking_date." ".$request->booking_end_time;

			// date_default_timezone_set('Asia/Kolkata');
			// date_default_timezone_set('America/New_York');
			// $objDateTime = new DateTime($jk_booking_date);
			// $objDateTimeZone = new DateTimeZone('Europe/Paris');
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

			$checkBooking = Booking::where('user_id', @$user->id)
			->where('pick_up_location', @$request->pick_up_location)
			->where('drop_location', @$request->drop_location)
			->where('booking_start_time', @$request->booking_start_time)
			->where('booking_end_time', @$request->booking_end_time)
			->where('booking_date', @$request->booking_date)
			->whereNotIn('trip_status', ["cancelled","pending","completed"])
			->first();
			if(!empty($checkBooking)){
				return response()->json(['status' => 'error','message' => 'You have already booked for this Locations and Time Intervals']);
			}


			$trips = Booking::create([
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
				'booking_end_time'         => $request->booking_end_time,
				'extra_notes'              => $extra_notes,
				'ride_setting_id'          => $request->service_id,
				'total_luggage'            => $request->total_luggage,
				'user_id'                  => $user->id,
				'taxi_hailing'             => $request->taxi_hailing,
				'seats'                    => $seats,
				'bstart_date'			   => $request->start_date,
				'bend_date'                => $request->end_date,
				]);
			$text = 'Ride Booking OTP is: '.$trips->otp;
			$emailcontent = array (
				'text' => $text,
				'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
				'userName' => $user->first_name.' '.$user->last_name
				);
			$details['email'] = $user->email;
			$details['username'] = $user->first_name.' '.$user->last_name;
			$details['subject'] = 'Ride Booking OTP';
			dispatch(new sendNotification($details,$emailcontent));
			if(!empty($trips)){
				$tripdetail = Booking::find($trips->id);
				return response()->json(['status' => 'success','message' => 'Out Of Town Booking Booked Successfully','data' => $tripdetail]);
			}else{
				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
			}
		} else {
			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

// public function createUserTrip(Request $request){
// 	$validation_array =[
// 		'pick_up_location'    => 'required',
// 		'drop_location'       => 'required',
// 		'start_latitude'      => 'required',
// 		'start_longitude'     => 'required',
// 		'drop_latitude'       => 'required',
// 		'drop_longitude'      => 'required',
// 		'service_id'      	  => 'required',
// 	];
// 	$validation = Validator::make($request->all(),$validation_array);
// 	if($validation->fails()){
// 		return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
// 	}
// 	try{
// 		if($request->promo_id){
// 			$promo_id = $request->promo_id;
// 		}else{
// 			$promo_id = '';
// 		}
// 		$user = JWTAuth::parseToken()->authenticate();
// 		if(!$user){
// 			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
// 		}
// 		if($request->extra_notes){
// 			$extra_notes = $request->extra_notes;
// 		}else{
// 			$extra_notes = '';
// 		}
// 		if($request->booking_type == 'immediate'){
// 			// $booking_date = Carbon::now()->format('Y-m-d');


// 			$date_time = date("Y-m-d");
// 			$date_time1 =date_create($date_time,timezone_open("Asia/kolkata"));
// 			date_format($date_time1,"Y-m-d") . "<br>";
// 			date_timezone_set($date_time1,timezone_open("Europe/Paris"));
// 			$booking_date =  date_format($date_time1,"Y-m-d");


// 			$time = $request->booking_start_time;
// 			$date=date_create($time,timezone_open("Asia/kolkata"));
// 			date_format($date,"H:i A") . "<br>";
// 			date_timezone_set($date,timezone_open("Europe/Paris"));
// 			$booking_start_time =  date_format($date,"H:i A");


// 		// dd($jk);

//             $checkBooking = Booking::where('user_id', @$user->id)
//                 ->where('pick_up_location', @$request->pick_up_location)
//                 ->where('drop_location', @$request->drop_location)
//                 ->where('booking_start_time', @$booking_start_time)
// //                ->where('booking_end_time', @$request->booking_end_time)
//                 ->where('booking_date', @$booking_date)
//                 ->whereNotIn('trip_status', ["cancelled","pending","completed"])
//                 ->first();
//             if(!empty($checkBooking)){
//                 return response()->json(['status' => 'error','message' => 'You have already booked for this Locations and Time Intervals']);
//             }

// 			$trips = Booking::create([
// 				'booking_type'             => $request->booking_type,
// 				'pick_up_location'         => $request->pick_up_location,
// 				'drop_location'            => $request->drop_location,
// 				'start_latitude'           => $request->start_latitude,
// 				'start_longitude'          => $request->start_longitude,
// 				'drop_latitude'            => $request->drop_latitude,
// 				'drop_longitude'           => $request->drop_longitude,
// 				'otp'                      => mt_rand(1000, 9999),
// 				'booking_date'             => $booking_date,
// 				'booking_start_time'       => $booking_start_time,
// 				'extra_notes'              => $extra_notes,
// 				'ride_setting_id'          => $request->service_id,
// 				'user_id'                  => $user->id,
// 			]);
//             $text = 'Ride Booking OTP is: '.$trips->otp;
//             $emailcontent = array (
//                 'text' => $text,
//                 'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
//                 'userName' => $user->first_name.' '.$user->last_name
//             );
//             $details['email'] = $user->email;
//             $details['username'] = $user->first_name.' '.$user->last_name;
//             $details['subject'] = 'Ride Booking OTP';
//             dispatch(new sendNotification($details,$emailcontent));
// 			if(!empty($trips)){
// 				$tripdetail = Booking::find($trips->id);
// 				$path = storage_path().'/logs/'.date("d-m-Y").'_aircall_logs.log';
// 				file_put_contents($path, "\n\n".date("d-m-Y H:i:s") . "Data Away response: ".$tripdetail."\n", FILE_APPEND);
// 				return response()->json(['status' => 'success','message' => 'Immediate Ride Booked Successfully','data' => $tripdetail]);
// 			}else{
// 				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 			}
// 		} elseif($request->booking_type == 'schedule') {
// 			// dd($request->all());
// 			$parceldetail = ParcelDetail::find($request->parceldetail_id);
// 			if(!empty($parceldetail))
// 			{
// 				if($request->booking_date){
//                 $booking_date = date('Y-m-d', strtotime($request->booking_date));
//             }else{
//                 $booking_date = Carbon::now()->format('Y-m-d');
//             }

//             $parcel = ParcelDetail::where('id', $request->parceldetail_id)->update([ 'booking_date'=> $booking_date]);
//             if(!empty($parcel)){
// 				return response()->json(['status' => 'success','message' => 'Schedule Booking Booked Successfully','data' => $parceldetail]);
// 			}else{
// 				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 			}

// 			} else {
// 				if($request->booking_date){
//                 $booking_date = date('Y-m-d', strtotime($request->booking_date));
//             }else{
//                 $booking_date = Carbon::now()->format('Y-m-d');
//             }
//             // dd($booking_date);
//             // $date_time = $booking_dateing;
// 			$date_time1 =date_create($booking_date,timezone_open("Asia/kolkata"));
// 			date_format($date_time1,"Y-m-d") . "<br>";
// 			date_timezone_set($date_time1,timezone_open("Europe/Paris"));
// 			$booking_date =  date_format($date_time1,"Y-m-d");

// 			// dd($booking_date);


//             $time = $request->booking_start_time;
// 			$date=date_create($time,timezone_open("Asia/kolkata"));
// 			date_format($date,"H:i A") . "<br>";
// 			date_timezone_set($date,timezone_open("Europe/Paris"));
// 			$booking_start_time =  date_format($date,"H:i A");

// 			// $end_time = $request->booking_end_time;

// 			// $date=date_create($end_time,timezone_open("Asia/kolkata"));
// 			// dd($date);
// 			// date_format($date,"H:i A") . "<br>";
// 			// date_timezone_set($date1,timezone_open("Europe/Paris"));
// 			// $booking_end_time =  date_format($date,"H:i A");
// 			// dd($booking_end_time);
//             $checkBooking = Booking::where('user_id', @$user->id)
//                 ->where('pick_up_location', @$request->pick_up_location)
//                 ->where('drop_location', @$request->drop_location)
//                 ->where('booking_start_time', @$request->booking_start_time)
//                 ->where('booking_end_time', @$request->booking_end_time)
//                 ->where('booking_date', @$booking_date)
//                 ->whereNotIn('trip_status', ["cancelled","pending","completed"])
//                 ->first();
//             if(!empty($checkBooking)){
//                 return response()->json(['status' => 'error','message' => 'You have already booked for this Locations and Time Intervals']);
//             }

//             $trips = Booking::create([
// 				'booking_type'             => $request->booking_type,
// 				'pick_up_location'         => $request->pick_up_location,
// 				'drop_location'            => $request->drop_location,
// 				'start_latitude'           => $request->start_latitude,
// 				'start_longitude'          => $request->start_longitude,
// 				'drop_latitude'            => $request->drop_latitude,
// 				'drop_longitude'           => $request->drop_longitude,
// 				'otp'                      => mt_rand(1000, 9999),
// 				'booking_date'             => $booking_date,
// 				'booking_start_time'       => $booking_start_time,
// 				'booking_end_time'         => $request->booking_end_time,
// 				'extra_notes'              => $extra_notes,
// 				'ride_setting_id'          => $request->service_id,
// 				'total_luggage'            => $request->total_luggage,
// 				'user_id'                  => $user->id,
// 			]);
//             $text = 'Ride Booking OTP is: '.$trips->otp;
//             $emailcontent = array (
//                 'text' => $text,
//                 'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
//                 'userName' => $user->first_name.' '.$user->last_name
//             );
//             $details['email'] = $user->email;
//             $details['username'] = $user->first_name.' '.$user->last_name;
//             $details['subject'] = 'Ride Booking OTP';
//             dispatch(new sendNotification($details,$emailcontent));
// 			if(!empty($trips)){
// 				$tripdetail = Booking::find($trips->id);
// 				return response()->json(['status' => 'success','message' => 'Schedule Booking Booked Successfully','data' => $tripdetail]);
// 			}else{
// 				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 			}

// 			}

// 		} elseif($request->booking_type == 'out_of_town') {
// 			if($request->seats == null){
// 				$seats = 'vip';
// 			} else {
// 				$seats = $request->seats;
// 			}
//             $booking_date='';
//             if($request->booking_date){
//                 $booking_date = $request->booking_date;
//             }else{
//                 $booking_date = date("Y-m-d");
//             }




//             $checkBooking = Booking::where('user_id', @$user->id)
//                 ->where('pick_up_location', @$request->pick_up_location)
//                 ->where('drop_location', @$request->drop_location)
//                 ->where('booking_start_time', @$request->booking_start_time)
//                 ->where('booking_end_time', @$request->booking_end_time)
//                 ->where('booking_date', @$booking_date)
//                 ->whereNotIn('trip_status', ["cancelled","pending","completed"])
//                 ->first();
//             if(!empty($checkBooking)){
//                 return response()->json(['status' => 'error','message' => 'You have already booked for this Locations and Time Intervals']);
//             }

//             $date_time = $booking_date;
// 			$date_time1 =date_create($date_time,timezone_open("Asia/kolkata"));
// 			date_format($date_time1,"Y-m-d") . "<br>";
// 			date_timezone_set($date_time1,timezone_open("Europe/Paris"));
// 			$booking_date =  date_format($date_time1,"Y-m-d");

//             $time = $request->booking_start_time;
// 			$date=date_create($time,timezone_open("Asia/kolkata"));
// 			date_format($date,"H:i A") . "<br>";
// 			date_timezone_set($date,timezone_open("Europe/Paris"));
// 			$booking_start_time =  date_format($date,"H:i A");

// 			$time = $request->booking_end_time;
// 			$date=date_create($time,timezone_open("Asia/kolkata"));
// 			date_format($date,"H:i A") . "<br>";
// 			date_timezone_set($date,timezone_open("Europe/Paris"));
// 			$booking_end_time =  date_format($date,"H:i A");

// 			$trips = Booking::create([
// 				'booking_type'             => $request->booking_type,
// 				'pick_up_location'         => $request->pick_up_location,
// 				'drop_location'            => $request->drop_location,
// 				'start_latitude'           => $request->start_latitude,
// 				'start_longitude'          => $request->start_longitude,
// 				'drop_latitude'            => $request->drop_latitude,
// 				'drop_longitude'           => $request->drop_longitude,
// 				'otp'                      => mt_rand(1000, 9999),
// 				'booking_date'             => $booking_date,
// 				'booking_start_time'       => $booking_start_time,
// 				'booking_end_time'         => $booking_end_time,
// 				'extra_notes'              => $extra_notes,
// 				'ride_setting_id'          => $request->service_id,
// 				'total_luggage'            => $request->total_luggage,
// 				'user_id'                  => $user->id,
// 				'taxi_hailing'             => $request->taxi_hailing,
// 				'seats'                    => $seats,
// 			]);
//             $text = 'Ride Booking OTP is: '.$trips->otp;
//             $emailcontent = array (
//                 'text' => $text,
//                 'title' => 'Thanks for Booking a Ride on Ruerun , Please use Below OTP for Complete Your Trip. You will need to complete Booking Process enter OTP.',
//                 'userName' => $user->first_name.' '.$user->last_name
//             );
//             $details['email'] = $user->email;
//             $details['username'] = $user->first_name.' '.$user->last_name;
//             $details['subject'] = 'Ride Booking OTP';
//             dispatch(new sendNotification($details,$emailcontent));
// 			if(!empty($trips)){
// 				$tripdetail = Booking::find($trips->id);
// 				return response()->json(['status' => 'success','message' => 'Out Of Town Booking Booked Successfully','data' => $tripdetail]);
// 			}else{
// 				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 			}
// 		} else {
// 			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
// 		}
// 	} catch (Exception $e) {
// 		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
// 	}
// }

/*
|--------------------------------------------------------------------------
| Complete User Trip Booking
|--------------------------------------------------------------------------
|
*/
public function User_payment_type(Request $request){
	$validation_array =[
		// 'booking_id'    	  => 'required',
	'payment_type'        => 'required',
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
		if($request->booking_id){
			$Booking = Booking::where('id', $request->booking_id)->update([
				'payment_type' 	   => $request->payment_type,
				'card_id'          => $request->card_id,
				'total_amount'     => $request->total_amount,
				]);
			return response()->json(['status' => 'success','message' => 'Successfully Payment Type']);

		} else if($request->parcel_id){
			$Booking = ParcelDetail::where('id', $request->parcel_id)->update([
				'payment_type' 	   => $request->payment_type,
				'card_id'          => $request->card_id,
				'total_amount'     => $request->total_amount,
				]);
			return response()->json(['status' => 'success','message' => 'Successfully Payment Type']);
		}

	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

public function completeUserTripBooking(Request $request){
	$validation_array =[
	'booking_id'    	  => 'required',
	'driver_id'           => 'required',
	'vehicle_id'          => 'required',
	'payment_type'        => 'required',
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
		$tripdetail = Booking::where('id', $request->booking_id)->first();
		$vehicle = Vehicle::find($request->vehicle_id);
		$total_km = $this->distance($tripdetail->start_latitude, $tripdetail->start_longitude, $tripdetail->drop_latitude, $tripdetail->drop_longitude);
		$total_amount = $this->gettotalfare($vehicle->id, $total_km, $request->promo_id);
		Booking::where('id', $request->booking_id)->update([
			'driver_id'        => $request->driver_id,
			'base_fare' 	   => $total_amount['vehicle_base_fare'],
			'admin_commision'  => $total_amount['admin_commision'],
			'promo_id' 		   => $request->promo_id,
			'total_km'         => $total_km,
			'total_amount'     => $total_amount['total_fare'],
			'payment_type' 	   => $request->payment_type,
			'total_luggage'    => $request->total_luggage,
			'ride_setting_id'  => $tripdetail->ride_setting_id,
			]);
		$data['message']        =  'Trip Booking Successfully Done';
		$data['type']           =  'trip_booking';
		$data['user_id']        =  $user->id;
		$data['notification']   =  Event::dispatch('send-notification-assigned-user',array($tripdetail,$data));

		// Notification For Driver
		$driver_data['message']       = 'New Booking Arrived';
		$driver_data['type']          = 'trip_booking';
		$driver_data['driver_id']     = $tripdetail->driver_id;
		$driver_data['notification']  = Event::dispatch('send-notification-assigned-driver',array($tripdetail,$driver_data));

		PopupNotification::create([
			'from_user_id' => $user->id,
			'to_user_id' => $tripdetail->driver_id,
			'title' => 'New Booking Arrived',
			'description' => 'New Booking Arrived',
			'date' => Carbon::now()->format('Y-m-d'),
			'time' => Carbon::now()->format('H:i'),
			'booking_id' => $tripdetail->id,
			]);
		$text = 'Your OTP is: '.$tripdetail->otp;
		$emailcontent = array (
			'text' => $text,
			'title' => 'Thanks for Use Ruerun for Ride, Please use Below OTP for Ride Booking Verification. You will need to complete booking Process enter OTP.',
			'userName' => $user->first_name
			);
		$details['email'] = $user->email;
		$details['username'] = $user->first_name;
		$details['subject'] = 'Ride Booked, OTP Confirmation';
		dispatch(new sendNotification($details,$emailcontent));
		return response()->json(['status' => 'success','message' => 'Ride Booked Successfully','data' => Booking::where('id', $request->booking_id)->first()]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Get Profile
|--------------------------------------------------------------------------
|
*/
public function getProfile(Request $request){
	try {
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}
		$user_data = User::where(['id'=>$user->id])->first();
		return response()->json(['status' => 'success','message' => 'Getting profile successfully','data' => $user_data]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Promo List
|--------------------------------------------------------------------------
|
*/
public function getAllPromo(){
	try{
		$user = JWTAuth::parseToken()->authenticate();
		if(!$user){
			return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
		}

		$date = date('m/d/Y');
		$promo_codes = Promocodes::where('status','active')
		->where('promo_for_users',null)
		->get();

		$promo_users = Promocodes::where('status','active')
		->whereRaw('FIND_IN_SET('.$user->id.',promo_for_users)')
		->get();
		$data = array();
		$datas = array();
		foreach ($promo_users as $key => $value) {
			$date = date('Y-m-d');
			$start_date = date('Y-m-d', strtotime(str_replace('-', '/', $value->start_date)));
			$end_date = date('Y-m-d', strtotime(str_replace('-', '/', $value->end_date)));
			if($start_date <= $date && $end_date >= $date){
				$data[] = $value->id;
			}
		}
		foreach ($promo_codes as $key => $val) {
			$date = date('Y-m-d');
			$start_date = date('Y-m-d', strtotime(str_replace('-', '/', $val->start_date)));
			$end_date = date('Y-m-d', strtotime(str_replace('-', '/', $val->end_date)));
			if($start_date <= $date && $end_date >= $date){
				$data[] = $val->id;
			}
		}
		if(count($data)>0){
			$response_data = Promocodes::whereIn('id',$data)->get();
			return response()->json(['status' => 'success','message' => 'All Promos found','data' => $response_data ]);
		}else{
			return response()->json(['status' => 'error','message' => 'No Promos found']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Get fare price by api
|--------------------------------------------------------------------------
|
*/
public function getFarePrice(Request $request){

	if(!empty($request->ride_setting_id == "4")){
		$validation_array =[
		'ride_setting_id' => 'required',
		'booking_id'   => 'required',
		];
		$validation = Validator::make($request->all(),$validation_array);
		if($validation->fails()){
			return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
		}
		try{
			if($request->promo_id){
				$promo_id = $request->promo_id;
			}else{
				$promo_id = 0;
			}
			$promo_code = Promocodes::where('id', $promo_id)->first();
			$tripdetail = ParcelDetail::find($request->booking_id);
			$total_km = round($this->distance($tripdetail->start_latitude, $tripdetail->start_longitude, $tripdetail->drop_latitude, $tripdetail->drop_longitude));
			if($total_km == "0.0"){
				$total_km = 1;
			} else {
				$total_km = $total_km;
			}

			$commission_rate = Setting::where('code','comission_rate')->first();
			$admin_commision = ((float)$tripdetail->total_amount * (float)$commission_rate->value) / 100;

			$tripdetail->base_fare          = $total_km;
			$tripdetail->promo_id		    = $request->promo_id;
			$tripdetail->admin_commision    = round($admin_commision);
			$tripdetail->save();
			$total_amount['vehicle_base_fare']  = 0;
			$total_amount['admin_commision']    = round($admin_commision);
			$total_amount['total_fare']         = $tripdetail->total_amount;
			$distance = 0;

			return response()->json(['status' => 'success','message' => 'Total Fare','total_amount' => $total_amount,'vehicle_data' => (object)[],'distance' => $total_km]);
		} catch (Exception $e) {
			return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
		}
	} else{
		$validation_array =[
		'vehicle_id' => 'required',
		'booking_id'   => 'required',
		];
		$validation = Validator::make($request->all(),$validation_array);
		if($validation->fails()){
			return response()->json(['status' => 'error','message' => $validation->messages()->first()],200);
		}
		try{
			$commission_rate = Setting::where('code','comission_rate')->first();
			if($request->promo_id){
				$promo_id = $request->promo_id;
			}else{
				$promo_id = 0;
			}

			$date = date('m/d/Y');
			$promo_code = Promocodes::where('id', $promo_id)->first();
			$vehicle = Vehicle::where('id', $request->vehicle_id)->first();
			$tripdetail = Booking::find($request->booking_id);
			$total_km = round($this->distance($tripdetail->start_latitude, $tripdetail->start_longitude, $tripdetail->drop_latitude, $tripdetail->drop_longitude));
			if($total_km == "0.0"){
				$total_km = 1;
			} else {
				$total_km = $total_km;
			}
			$total_amount = $this->gettotalfare($vehicle->id, $total_km, $request->promo_id);
			$base_fare       = $total_amount['total_fare'];
			$admin_commision = $total_amount['admin_commision'];
			$total_km        = $total_km;

			$tripdetail->base_fare       = $base_fare;
			$tripdetail->admin_commision = $admin_commision;
			$tripdetail->total_km        = $total_km;
			$tripdetail->promo_id		 = $request->promo_id;
			$tripdetail->save();


			return response()->json(['status' => 'success','message' => 'Total Fare','total_amount' => $total_amount, 'vehicle_data'=>$vehicle, 'distance'=>$total_km]);
		} catch (Exception $e) {
			return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
		}
	}
}

/*
|--------------------------------------------------------------------------
| get distance between two lat, long (used in completeUserTripBooking() )
|--------------------------------------------------------------------------
|
*/
public function distance($lat1, $lon1, $lat2, $lon2) {
	// dd($lat1, $lon1, $lat2, $lon2);
	$unit = Setting::where('code','distance_in')->first();
	// dd($unit);
	$latitudeFrom = $lat1;
	$longitudeFrom = $lon1;
	
	$latitudeTo = $lat2;
	$longitudeTo = $lon2;
	// dd($latitudeFrom,$longitudeFrom , $latitudeTo ,	$longitudeTo );
	//Calculate distance from latitude and longitude
	$theta = $longitudeFrom - $longitudeTo;
	$dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
	// dd($dist);
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	// $miles = $miles;
	$miles = round($miles, 2);
	// dd($miles);
	$unit = strtoupper($unit->value);
	// dd($miles);
	// dd($unit);
	if ($unit === "KM") {
		// dd($miles * 1.609344);
		// $miles = $miles;
		$miles = $miles * 1.609344;
		// dd($miles);
		return round($miles);
	}else {
		return round($miles);
	}
}

/*
|--------------------------------------------------------------------------
| get total fare with admin commission (used in completeUserTripBooking() )
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

/*
|--------------------------------------------------------------------------
| Set Refer User
|--------------------------------------------------------------------------
|
*/
public function setReferUser(Request $request){
	$validation_array =[
	'refer_user_id' => 'required',
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
		$refferal_rate = Setting::where('code','referral_rate')->first();
		if(!empty($refferal_rate) && (int)$refferal_rate->value){
			$checkuserwallet = Wallet::where('user_id', $user->id)->first();
			if(!empty($checkuserwallet)){
				$amount = (int)$checkuserwallet->amount + (int)$refferal_rate->value;
				Wallet::where('user_id', $user->id)->update([
					'amount' => $amount
					]);
				WalletHistory::create([
					'refer_user_id' => $request->refer_user_id,
					'amount'        => $refferal_rate->value,
					'description'   => '$'.$refferal_rate->value.' added to '.$user->username.' Wallet for refer Another One',
					'user_id'       => $user->id,
					]);
			}else{
				$checkuserwallet = Wallet::create([
					'amount' => $refferal_rate->value,
					'user_id' => $user->id
					]);
				WalletHistory::create([
					'refer_user_id' => $request->refer_user_id,
					'amount'        => $refferal_rate->value,
					'description'   => '$'.$refferal_rate.' added to '.$user->username.' Wallet',
					'user_id'       => $user->id,
					]);
			}
		}else{
			return response()->json(['status' => "error",'message' => 'Refferal Rate not created',]);
		}
		return response()->json(['status' => 'success','message' => 'User Reffered', 'data'=> Wallet::where('user_id', $user->id)->first()]);
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}

/*
|--------------------------------------------------------------------------
| Set Refer User by refer unique code
|--------------------------------------------------------------------------
|
*/
public function set_refer_user($userdata=array(), $ref_id=''){
	if(!empty($userdata) && $ref_id){
		$first_user = User::where('uuid',$ref_id)->first();
		$refferal_rate = Setting::where('code','referral_rate')->first();
		$checkwallet = Wallet::where('user_id', $first_user->id)->first();
		if(!empty($checkwallet)){
			$amount = (int)$checkwallet->amount + (int)$refferal_rate->value;
			Wallet::where('user_id', $first_user->id)->update(['amount' => $amount,]);
		}else{
			Wallet::create(['amount' => $refferal_rate->value,'user_id' => $first_user->id ]);
		}
		WalletHistory::create([
			'refer_user_id' => $userdata->id,
			'amount'        => $refferal_rate->value,
			'description'   => '$'.$refferal_rate->value.' added to '.$first_user->username.' Wallet for reffering Another One',
			'user_id'       => $first_user->id,
			]);
	}
}

/*
|--------------------------------------------------------------------------
| Create New User Trip
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
	'service_id'      	  => 'required',
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
		if($request->booking_type == 'immediate'){
			$booking_date = Carbon::now()->format('Y-m-d');
			$booking_start_time = Carbon::now()->format('H:i A');
			$trips = Booking::create([
				'booking_type'             => $request->booking_type,
				'pick_up_location'         => $request->pick_up_location,
				'drop_location'            => $request->drop_location,
				'start_latitude'           => $request->start_latitude,
				'start_longitude'          => $request->start_longitude,
				'drop_latitude'            => $request->drop_latitude,
				'drop_longitude'           => $request->drop_longitude,
				'otp'                      => mt_rand(1000, 9999),
				'booking_date'             => $booking_date,
				'booking_start_time'       => $booking_start_time,
				'extra_notes'              => $extra_notes,
				'ride_setting_id'          => $request->service_id,
				'user_id'                  => $user->id,
				'driver_id'                => $request->driver_id,
				'seats'                    => $request->seats,
				'total_luggage'            => $total_luggage,
				'total_amount'             => $request->total_amount,
				]);
			if(!empty($trips)){
				$tripdetail = Booking::find($trips->id);
				return response()->json(['status' => 'success','message' => 'Immediate Shuttle Ride Booked Successfully','data' => $tripdetail]);
			}else{
				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
			}
		} elseif($request->booking_type == 'schedule') {
			if($request->booking_date){
				$booking_date = \Carbon\Carbon::parse($request->booking_date)->format('Y-m-d');
			}else{
				$booking_date = Carbon::now()->format('Y-m-d');
			}
			$trips = Booking::create([
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
				'booking_end_time'         => $request->booking_end_time,
				'extra_notes'              => $extra_notes,
				'ride_setting_id'          => $request->service_id,
				'total_luggage'            => $request->total_luggage,
				'user_id'                  => $user->id,
				'driver_id'                => $request->driver_id,
				'seats'                    => $request->seats,
				'total_luggage'            => $total_luggage,
				'total_amount'             => $request->total_amount,
				]);
			if(!empty($trips)){
				$tripdetail = Booking::find($trips->id);
				return response()->json(['status' => 'success','message' => 'Schedule Shuttle Booking Successfully','data' => $tripdetail]);
			}else{
				return response()->json(['status' => 'error','message' => 'Something went Wrong']);
			}
		} else {
			return response()->json(['status' => 'error','message' => 'Something went Wrong']);
		}
	} catch (Exception $e) {
		return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
	}
}
}
