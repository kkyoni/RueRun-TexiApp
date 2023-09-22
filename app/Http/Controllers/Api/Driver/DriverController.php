<?php
namespace App\Http\Controllers\Api\Driver;
use App\Jobs\sendNotification;
use App\Models\CompanyDetail;
use App\Models\Otp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage, Illuminate\Support\Facades\DB, Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\DriverDetails;
use App\Models\PasswordReset;
use App\Models\Setting;
use Carbon\Carbon;
use App\Models\DriverDocuments;
use App\Models\DriverVehicleDocument;
use App\Models\State;
use App\Models\City;

class DriverController extends Controller{
  public function getAuthenticatedUser(){
    try {
      if (!$user = JWTAuth::parseToken()->authenticate()) {
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
| Driver Register
|--------------------------------------------------------------------------
|
*/
// public function driver_register(Request $request){
//   $validator = Validator::make($request->all(), [
//     'last_name'          => ['required', 'string', 'max:190'],
//     'first_name'         => ['required', 'string', 'max:190'],
//     'email'    =>  'required|email|unique:users,email,NULL,id,deleted_at,NULL',
//     // 'email'              => 'required|string|email|max:190|unique:users',
//     'password'           => ['required', 'string', 'min:6'],
//     // 'contact_number'     => 'required|min:10|max:10|unique:users',
//     'contact_number'          => 'required|unique:users,contact_number,NULL,id,deleted_at,NULL',
//     'address'            => ['required', 'string', 'max:255'],
//     'country'            => ['required', 'string', 'max:190'],
//     'zipcode'           => ['required'],
//     'city_id'            => ['required'],
//     'state_id'           => ['required'],
//     'avatar'             => 'image|mimes:jpeg,png,jpg,gif|max:3000',
//   ]);
//   if($validator->fails()){
//     return response()->json(['status'=> 'error','message'  => $validator->messages()->first()]);
//   }
//   try{
//     if($request->hasFile('avatar')){
//       $file = $request->file('avatar');
//       $extension = $file->getClientOriginalExtension();
//       $filename = Str::random(10).'.'.$extension;
//       Storage::disk('public')->putFileAs('avatar', $file,$filename);
//     }else{
//       $filename = 'default.png';
//     }
//     $data['avatar']=$filename;
//     if(request('ref_id')){
//       $uuid = User::where('uuid',$request->get('ref_id'))->first();
//       if (!empty($uuid)) {
//         $ref_id=$uuid->uuid;
//       }else{
//         return response()->json(['status' => 'error','message' => 'Enter valid invite code','data'   => (object)[]]);
//       }
//     } else{
//       $ref_id='';
//     }
//     $user = User::create([
//         'avatar'            => $filename,
//         'username'          => $request->get('username'),
//         'first_name'        => $request->get('first_name'),
//         'last_name'         => $request->get('last_name'),
//         'email'             => $request->get('email'),
//         'contact_number'    => $request->get('contact_number'),
//         'password'          => Hash::make($request->get('password')),
//         'status'            => 'active',
//         'user_type'         => 'driver',
//         'country_code'      => $request->get('country_code'),
//         'zipcode'          => $request->get('zipcode'),
//         'ref_id'            => $ref_id,
//         'address'           => $request->get('address'),
//         'country'           => $request->get('country'),
//         'city_id'           => $request->get('city_id'),
//         'state_id'          => $request->get('state_id'),
//         'sign_up_as'        => 'other',
//         'device_token'      =>  $request->get('device_token'),
//         'device_type'       =>  $request->get('device_type'),
//         'driver_doc'        =>  '0',
//         'car_doc'           =>  '0',
//         'vehicle_id'        =>  $request->get('vehicle_id'),
//         'add_latitude'      => $request->get('add_latitude'),
//         'add_longitude'     => $request->get('add_longitude'),
//         'driver_signup_as'  => $request->get('driver_signup_as'),
//     ]);
//     if($request->hasFile('vehicle_images')){
//       $file = $request->file('vehicle_images');
//       $extension = $file->getClientOriginalExtension();
//       $filename_vehicle = Str::random(10).'.'.$extension;
//       Storage::disk('public')->putFileAs('vehicle_images', $file,$filename_vehicle);
//     }else{
//       $filename_vehicle = '';
//     }
//     if($request->get('driver_signup_as') === 'company'){
//       $companydetails = CompanyDetail::create([
//         'recipient_name'        => $request->recipient_name,
//         'job_title'             => $request->job_title,
//         'company_size'          => $request->company_size,
//         'website'               => $request->website,
//         'company_id'            => $user->id,
//       ]);
//       User::where('id', $user->id)->update(['company_id'=> $companydetails->id, 'company_name'=> request('company_name')]);
//     }
//     $adduser_info = DriverDetails::create([
//       'driver_id'         => $user->id,
//       'vehicle_model'     => $request->get('vehicle_model'),
//       'image'             => $filename_vehicle,
//       'vehicle_plate'     => $request->get('vehicle_plate'),
//       'color'             => $request->get('color'),
//       'mileage'           => $request->get('mileage'),
//       'year'              => $request->get('year'),
//       'ride_type'         => $request->get('ride_type')
//     ]);
//     $otpNumber = random_int(1000, 9999);
//     if(!empty($user)){
//       $UserOtpCreated = Otp::create([
//         'email'         => $user->email,
//         'contact_number' => (string)$user->contact_number,
//         'otp_number'    => $otpNumber,
//       ]);
//       $text = 'Your OTP is: '.$otpNumber;
//       $emailcontent = array (
//         'text' => $text,
//         'title' => 'Thanks for join Ruerun App, Please use Below OTP for Contact Number Verification.',
//         'userName' => $user->first_name
//       );
//       $details['email'] = $user->email;
//       $details['username'] = $user->first_name;
//       $details['subject'] = 'Welcome to Ruerun, OTP Confirmation';
//       dispatch(new sendNotification($details,$emailcontent));
//       $data['otpNumber'] = $otpNumber;
//     }
//     $token = JWTAuth::fromUser($user);
//     $status = 'success';
//     $message = 'Driver registered';
//     $data = [
//       'token' => $token,
//       'user'  => $user,
//       'user_details' => $adduser_info,
//       'otpNumber'=>$otpNumber
//     ];
//     return response()->json(['status'    => $status,'message'   => $message,'data'      => $data]);
//   }catch(\Exception $e){
//     return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
//   }
// }

// public function driver_register(Request $request){
//   $validator = Validator::make($request->all(), [
//     'last_name'          => ['required', 'string', 'max:190'],
//     'first_name'         => ['required', 'string', 'max:190'],
//     'email'    =>  'required|email|unique:users,email,NULL,id,deleted_at,NULL',
//     // 'email'              => 'required|string|email|max:190|unique:users',
//     'password'           => ['required', 'string', 'min:6'],
//     // 'contact_number'     => 'required|min:10|max:10|unique:users',
//     'contact_number'          => 'required|unique:users,contact_number,NULL,id,deleted_at,NULL',
//     'address'            => ['required', 'string', 'max:255'],
//     'country'            => ['required', 'string', 'max:190'],
//     'zipcode'           => ['required'],
//     'city_id'            => ['required'],
//     'state_id'           => ['required'],
//     'avatar'             => 'image|mimes:jpeg,png,jpg,gif|max:3000',
//   ]);
//   if($validator->fails()){
//     return response()->json(['status'=> 'error','message'  => $validator->messages()->first()]);
//   }
//   try{
//     if($request->hasFile('avatar')){
//       $file = $request->file('avatar');
//       $extension = $file->getClientOriginalExtension();
//       $filename = Str::random(10).'.'.$extension;
//       Storage::disk('public')->putFileAs('avatar', $file,$filename);
//     }else{
//       $filename = 'default.png';
//     }
//     $data['avatar']=$filename;
//     if(request('ref_id')){
//       $uuid = User::where('uuid',$request->get('ref_id'))->first();
//       if (!empty($uuid)) {
//         $ref_id=$uuid->uuid;
//       }else{
//         return response()->json(['status' => 'error','message' => 'Enter valid invite code','data'   => (object)[]]);
//       }
//     } else{
//       $ref_id='';
//     }
//     $user = User::create([
//         'avatar'            => $filename,
//         'username'          => $request->get('username'),
//         'first_name'        => $request->get('first_name'),
//         'last_name'         => $request->get('last_name'),
//         'email'             => $request->get('email'),
//         'contact_number'    => $request->get('contact_number'),
//         'password'          => Hash::make($request->get('password')),
//         'status'            => 'active',
//         'user_type'         => 'driver',
//         'country_code'      => $request->get('country_code'),
//         'zipcode'          => $request->get('zipcode'),
//         'ref_id'            => $ref_id,
//         'address'           => $request->get('address'),
//         'country'           => $request->get('country'),
//         'city_id'           => $request->get('city_id'),
//         'state_id'          => $request->get('state_id'),
//         'sign_up_as'        => 'other',
//         'device_token'      =>  $request->get('device_token'),
//         'device_type'       =>  $request->get('device_type'),
//         'driver_doc'        =>  '0',
//         'car_doc'           =>  '0',
//         'vehicle_id'        =>  $request->get('vehicle_id'),
//         'add_latitude'      => $request->get('add_latitude'),
//         'add_longitude'     => $request->get('add_longitude'),
//         'driver_signup_as'  => $request->get('driver_signup_as'),
//     ]);


//     if($request->hasFile('vehicle_images')){
//       $file = $request->file('vehicle_images');
//       $extension = $file->getClientOriginalExtension();
//       $filename_vehicle = Str::random(10).'.'.$extension;
//       Storage::disk('public')->putFileAs('vehicle_images', $file,$filename_vehicle);
//     }else{
//       $filename_vehicle = '';
//     }
//     if($request->get('driver_signup_as') === 'company'){
//       $companydetails = CompanyDetail::create([
//         'recipient_name'        => $request->recipient_name,
//         'job_title'             => $request->job_title,
//         'company_size'          => $request->company_size,
//         'website'               => $request->website,
//         'company_id'            => $user->id,
//       ]);
//       User::where('id', $user->id)->update(['company_id'=> $companydetails->id, 'company_name'=> request('company_name')]);
//     }
//     $adduser_info = DriverDetails::create([
//       'driver_id'         => $user->id,
//       'vehicle_model'     => $request->get('vehicle_model'),
//       'image'             => $filename_vehicle,
//       'vehicle_plate'     => $request->get('vehicle_plate'),
//       'color'             => $request->get('color'),
//       'mileage'           => $request->get('mileage'),
//       'year'              => $request->get('year'),
//       'ride_type'         => $request->get('ride_type')
//     ]);
//     $otpNumber = random_int(1000, 9999);
//     if(!empty($user)){
//       $UserOtpCreated = Otp::create([
//         'email'         => $user->email,
//         'contact_number' => (string)$user->contact_number,
//         'otp_number'    => $otpNumber,
//       ]);
//       $text = 'Your OTP is: '.$otpNumber;
//       $emailcontent = array (
//         'text' => $text,
//         'title' => 'Thanks for join Ruerun App, Please use Below OTP for Contact Number Verification.',
//         'userName' => $user->first_name
//       );
//       $details['email'] = $user->email;
//       $details['username'] = $user->first_name;
//       $details['subject'] = 'Welcome to Ruerun, OTP Confirmation';
//       dispatch(new sendNotification($details,$emailcontent));
//       $data['otpNumber'] = $otpNumber;
//     }
//     $token = JWTAuth::fromUser($user);

//     $first_name = substr($request->first_name, 0, 2);
//     $last_name = substr($request->last_name, 0, 2);
//     $city_id = substr($request->city_id, 0, 2);
//     $contact_number = substr($request->contact_number, -2);
//     $user_id = $user->id;
//     $uuid = strtoupper($first_name.$last_name.$city_id.$contact_number.$user_id);
//     User::where('id',$user_id)->update(['uuid' => $uuid]);

//     $status = 'success';
//     $message = 'Driver registered';
//     $data = [
//       'token' => $token,
//       'user'  => $user,
//       'user_details' => $adduser_info,
//       'otpNumber'=>$otpNumber
//     ];
//     // dd($user);
//     // $data['user'] = $user;
//     return response()->json(['status'    => $status,'message'   => $message,'data'      => $data]);
//   }catch(\Exception $e){
//     return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
//   }
// }

public function driver_register(Request $request){
  $validator = Validator::make($request->all(), [
    'last_name'          => ['required', 'string', 'max:190'],
    'first_name'         => ['required', 'string', 'max:190'],
    'email'    =>  'required|email|unique:users,email,NULL,id,deleted_at,NULL',
    // 'email'              => 'required|string|email|max:190|unique:users',
    'password'           => ['required', 'string', 'min:6'],
    // 'contact_number'     => 'required|min:10|max:10|unique:users',
    'contact_number'          => 'required|unique:users,contact_number,NULL,id,deleted_at,NULL',
    'address'            => ['required', 'string', 'max:255'],
    'country'            => ['required', 'string', 'max:190'],
    'zipcode'           => ['required'],
    'city_id'            => ['required'],
    'state_id'           => ['required'],
    'avatar'             => 'image|mimes:jpeg,png,jpg,gif|max:3000',
    'device_token'      => ['required'],
    'device_type'       => ['required'],
    ]);
  if($validator->fails()){
    return response()->json(['status'=> 'error','message'  => $validator->messages()->first()]);
  }
  try{
    if($request->hasFile('avatar')){
      $file = $request->file('avatar');
      $extension = $file->getClientOriginalExtension();
      $filename = Str::random(10).'.'.$extension;
      Storage::disk('public')->putFileAs('avatar', $file,$filename);
    }else{
      $filename = 'default.png';
    }
    $data['avatar']=$filename;
    if(request('ref_id')){
      $uuid = User::where('uuid',$request->get('ref_id'))->first();
      if (!empty($uuid)) {
        $ref_id=$uuid->uuid;
      }else{
        return response()->json(['status' => 'error','message' => 'Enter valid invite code','data'   => (object)[]]);
      }
    } else{
      $ref_id='';
    }
    $user = User::create([
      'avatar'            => $filename,
      'username'          => $request->get('username'),
      'first_name'        => $request->get('first_name'),
      'last_name'         => $request->get('last_name'),
      'email'             => $request->get('email'),
      'contact_number'    => $request->get('contact_number'),
      'password'          => Hash::make($request->get('password')),
      'status'            => 'active',
      'user_type'         => 'driver',
      'country_code'      => $request->get('country_code'),
      'zipcode'          => $request->get('zipcode'),
      'ref_id'            => $ref_id,
      'address'           => $request->get('address'),
      'country'           => $request->get('country'),
      'city_id'           => $request->get('city_id'),
      'state_id'          => $request->get('state_id'),
      'sign_up_as'        => 'app',
      'device_token'      =>  $request->get('device_token'),
      'device_type'       =>  $request->get('device_type'),
      'driver_doc'        =>  '0',
      'car_doc'           =>  '0',
      'vehicle_id'        =>  $request->get('vehicle_id'),
      'add_latitude'      => $request->get('add_latitude'),
      'add_longitude'     => $request->get('add_longitude'),
      'driver_signup_as'  => $request->get('driver_signup_as'),
      ]);
    if($request->hasFile('vehicle_images')){
      $file = $request->file('vehicle_images');
      $extension = $file->getClientOriginalExtension();
      $filename_vehicle = Str::random(10).'.'.$extension;
      Storage::disk('public')->putFileAs('vehicle_images', $file,$filename_vehicle);
    }else{
      $filename_vehicle = '';
    }
    if($request->get('driver_signup_as') === 'company'){
      $companydetails = CompanyDetail::create([
        'recipient_name'        => $request->recipient_name,
        'job_title'             => $request->job_title,
        'company_size'          => $request->company_size,
        'website'               => $request->website,
        'company_id'            => $user->id,
        ]);
      User::where('id', $user->id)->update(['company_id'=> $companydetails->id, 'company_name'=> request('company_name')]);
    }
    $adduser_info = DriverDetails::create([
      'vehicle_model_id'  => @$request->get('vehicle_model_id'),
      'driver_id'         => $user->id,
      'vehicle_model'     => $request->get('vehicle_model'),
      'image'             => $filename_vehicle,
      'vehicle_plate'     => $request->get('vehicle_plate'),
      'color'             => $request->get('color'),
      'mileage'           => $request->get('mileage'),
      'year'              => $request->get('year'),
      'ride_type'         => $request->get('ride_type')
      ]);
    $otpNumber = random_int(1000, 9999);
    if(!empty($user)){
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
      // dispatch(new sendNotification($details,$emailcontent));
      $data['otpNumber'] = $otpNumber;
    }
    $token = JWTAuth::fromUser($user);
    User::where('id',$user->id)->update(['login_token' => $token]);
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
    User::where('id',$user->id)->update(['uuid' => $uuid]);
    
    $status = 'success';
    $message = 'Driver registered';
    $data = [
    'token' => $token,
    'user'  => $user,
    'user_details' => $adduser_info,
    'otpNumber'=>$otpNumber
    ];
    return response()->json(['status'    => $status,'message'   => $message,'data'      => $data]);
  }catch(\Exception $e){
    return response()->json(['status'    => 'error','message'   => $e->getMessage()]);
  }
}

/*
|--------------------------------------------------------------------------
| Driver Details
|--------------------------------------------------------------------------
|
*/
public function driver_details(Request $request){ 
  $validator = Validator::make($request->all(), [
    // 'vehicle_model_id'   => ['required'],
    'vehicle_model'      => ['required'],
    'vehicle_plate'      => ['required'],
    'vehicle_images'     => ['nullable'],
    'mileage'            => ['required'],
    'year'               => ['required'],
    'driver_id'          => ['required'],
    'color'              => ['required'],
    ]);
  if ($validator->fails()) {
    return response()->json(['status' => 'error', 'message' => $validator->messages()->first() ]);
  }
  try{
    $message='';
    $driverdetail = DriverDetails::where('driver_id',$request->driver_id)->first();
    if($request->hasFile('vehicle_images')){
      $file = $request->file('vehicle_images');
      $extension = $file->getClientOriginalExtension();
      $filename_vehicle = Str::random(10).'.'.$extension;
      Storage::disk('public')->putFileAs('vehicle_images', $file,$filename_vehicle);
    }elseif (!empty($driverdetail) && $driverdetail->vehicle_image) {
      $filename_vehicle = $driverdetail->vehicle_image;
    }
    else{
      return response()->json(['status' => 'error','message' => 'Vehicle Image not found','data'=> (object)[]]);
      // $filename_vehicle = '';
    }
    
    if(!empty($driverdetail)){
      $adduser_info = DriverDetails::where('driver_id',$request->driver_id)->update([
        'vehicle_model_id'  => @$request->get('vehicle_model_id'),
        'vehicle_model'     => $request->get('vehicle_model'),
        'vehicle_image'     => $filename_vehicle,
        'vehicle_plate'     => $request->get('vehicle_plate'),
        'color'             => $request->get('color'),
        'mileage'           => $request->get('mileage'),
        'year'              => $request->get('year'),
        ]);
      $message = 'Driver Details updated';
    }else{
      $adduser_info = DriverDetails::create([
        'vehicle_model_id'  => @$request->get('vehicle_model_id'),
        'driver_id'         => $request->get('driver_id'),
        'vehicle_model'     => $request->get('vehicle_model'),
        'vehicle_image'     => $filename_vehicle,
        'vehicle_plate'     => $request->get('vehicle_plate'),
        'color'             => $request->get('color'),
        'mileage'           => $request->get('mileage'),
        'year'              => $request->get('year'),
        ]);
      $message = 'Driver Details saved';
    }
    if($request->vehicle_id){
      $user = User::where('id', $request->get('driver_id'))->update(['vehicle_id'=>$request->vehicle_id,'make'=>$request->make]);
    }
    $status = 'success';
    return response()->json(['status' => $status, 'message' => $message]);
  }catch(\Exception $e){
    return response()->json(['status' => 'error', 'message' => $e->getMessage() ]);
  }catch(\Illuminate\Database\QueryException $e){
    return response()->json(['status' => 'error', 'message' => $e->getMessage() ]);
  }
}

/*
|--------------------------------------------------------------------------
| Driver Ride Type
|--------------------------------------------------------------------------
|
*/
public function driver_ride_type(Request $request){
  $validator = Validator::make($request->all(), [
    'ride_type'  => 'required',
    'driver_id'  => 'required'
    ]);
  if ($validator->fails()){
    return response()->json(['status' => 'error', 'message' => $validator->messages()->first() ]);
  }
  try{
    $driver = DriverDetails::where('driver_id',$request->driver_id)->first();
    $ride_types = explode(',', $request->get('ride_type') );
    if($driver){
      DriverDetails::where('driver_id', $request->driver_id)->delete();
      foreach($ride_types as $type){
        DriverDetails::create([
          'driver_id' => $request->driver_id,
          'ride_type' => $type,
          'vehicle_model'     => $driver->vehicle_model,
          'vehicle_image'     => $driver->vehicle_image,
          'vehicle_plate'     => $driver->vehicle_plate ,
          'color'             => $driver->color,
          'mileage'           => $driver->mileage,
          'year'              => $driver->year,
          ]);
      }
    }else{
      foreach($ride_types as $type){
        DriverDetails::create(['driver_id' => $request->driver_id,'ride_type' => $type ]);
      }
    }
    $status = 'success';
    $message = 'Driver ride type saved';
    return response()->json(['status' => $status, 'message' => $message]);
  }catch(\Exception $e){
    return response()->json(['status' => 'error', 'message' => $e->getMessage() ]);
  }
}
}
