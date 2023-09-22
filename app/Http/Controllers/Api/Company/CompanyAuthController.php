<?php
namespace App\Http\Controllers\Api\Company;
use App\Jobs\sendNotification;
use App\Models\CardDetails;
use Helmesvs\Notify\Facades\Notify;
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
use App\Models\Booking;
use App\Models\DriverDetails;
use App\Models\DriverDocuments;
use App\Models\Promocodes;
use App\Models\VehicleCategories;
use App\Models\Otp;
use App\Models\TransactionDetail;
use App\Models\Wallet, App\Models\WalletHistory, App\Models\UserCredits;
use Event;
use PushNotification;
use App\Models\CompanyDetail;
use App\Models\RatingReviews;
use App\Models\EmergencyRequest;
use App\Models\EmergencyType;

class CompanyAuthController extends Controller{
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
    | Company Register
    |--------------------------------------------------------------------------
    |
    */
    // public function company_register(Request $request){
    //     $validation_array =[
    //         'company_name'  	=> 'required',
    //         'applicant_name'    => 'required',
    //         'job_title' 		=> 'required',
    //         'company_size'	    => 'required|numeric',
    //         'website'			=> 'required',
    //         'email'			    => 'required|unique:users',
    //         'contact_number' 	=> 'required|unique:users',
    //         'address'           => 'required',
    //         'zipcode'          => 'required',
    //         'country'        	=> 'required',
    //         'city_id'           => 'required',
    //         'state_id'          => 'required',
    //         'add_latitude'      => 'required',
    //         'add_longitude'     => 'required',
    //         'password' 		 	=> 'min:8|required_with:password_confirmation|same:password_confirmation',
    //         'password_confirmation'=> 'required',
    //         'avatar' 		 => 'image|mimes:jpeg,png,jpg,gif|max:3000',
    //         'device_type'	 => 'required',
    //         'device_token'	 => 'required',
    //     ];
    //     $validation = Validator::make($request->all(),$validation_array);
    //     if($validation->fails()){
    //         return response()->json(['status' => 'error','message'   => $validation->errors()->first(),'data'=> (object)[]]);
    //     }
    //     try {
    //         if($request->hasFile('avatar')){
    //             $file = $request->file('avatar');
    //             $extension = $file->getClientOriginalExtension();
    //             $filename = Str::random(10).'.'.$extension;
    //             Storage::disk('public')->putFileAs('avatar', $file,$filename);
    //         }else{
    //             $filename = 'default.png';
    //         }
    //         $firstname = $lastname = '';
    //         if(@$request->get('applicant_name')){
    //             $firstname = @$request->get('applicant_name');
    //             $lastname = @$request->get('applicant_name');
    //         }
    //         if(@$request->get('country_code')){
    //             $country_code = @$request->get('country_code');
    //         }else{
    //             $country_code = '';
    //         }


    //         $userdata = User::create([
    //             'company_name'      => @$request->get('company_name'),
    //             'email'             => @$request->get('email'),
    //             'contact_number'    => @$request->get('contact_number'),
    //             'address'           => @$request->get('address'),
    //             'country'           => @$request->get('country'),
    //             'city_id'           => @$request->get('city_id'),
    //             'state_id'          => @$request->get('state_id'),
    //             'add_latitude'      => @$request->get('add_latitude'),
    //             'add_longitude'     => @$request->get('add_longitude'),
    //             'zipcode'           => @$request->get('zipcode'),
    //             'avatar'            => @$filename,
    //             'password'          => \Hash::make($request->get('password')),
    //             'status'            => 'active',
    //             'user_type'         => @$request->get('user_type'),
    //             'sign_up_as'        => 'app',
    //             'device_type'		=> @$request->get('device_type'),
    //             'device_token'		=> @$request->get('device_token'),
    //             'login_status'		=> 'online',
    //             'driver_signup_as'  => 'company',
    //             'first_name'        => @$firstname,
    //             'last_name'         => @$lastname,
    //             'country_code'		=> @$country_code,
    //         ]);

    //         $company_details = CompanyDetail::create([
    //             'company_id'		=>	$userdata->id,
    //             'recipient_name'	=>	@$request->get('applicant_name'),
    //             'job_title'			=>	@$request->get('job_title'),
    //             'company_size'		=>	@$request->get('company_size'),
    //             'website'			=>	@$request->get('website'),
    //             'type'			    =>	@$request->get('user_type'),
    //         ]);
    //         if(!empty($company_details)){
    //             User::where('id', $userdata->id)->update([ 'company_id'=>$company_details->id ]);
    //         }

    //         $user = User::where('id',$userdata->id)->first();
    //         $data1['token'] = JWTAuth::fromUser($userdata);
    //         $data1['company'] = $user;
    //         $data1['company_details'] = $company_details;
    //         if(request('ref_id')){
    //             $this->set_refer_user($userdata, request('ref_id'));
    //         }
    //         $otpNumber = random_int(1000, 9999);
    //         if(!empty($user)){
    //             $UserOtpCreated = Otp::create([
    //                 'email'         => $user->email,
    //                 'contact_number' => (string)$user->contact_number,
    //                 'otp_number'    => $otpNumber,
    //             ]);

    //             $text = 'Your OTP is: '.$otpNumber;
    //             $emailcontent = array (
    //                 'text' => $text,
    //                 'title' => 'Thanks for join Ruerun App, Please use Below OTP for Contact Number Verification.',
    //                 'userName' => $user->first_name
    //             );
    //             $details['email'] = $user->email;
    //             $details['username'] = $user->first_name;
    //             $details['subject'] = 'Welcome to Ruerun, OTP Confirmation';
    //             dispatch(new sendNotification($details,$emailcontent));
    //             $data1['otpNumber'] = $otpNumber;
    //         }

    //         return response()->json(['status' => 'success','message' => 'You are successfully Register!','data' => $data1]);
    //     }catch (Exception $e) {
    //         return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
    //     }
    // }
    public function company_register(Request $request){
        $validation_array =[
            'company_name'      => 'required',
            'applicant_name'    => 'required',
            'job_title'         => 'required',
            'company_size'      => 'required|numeric',
            'website'           => 'required',
            'email'             => 'required|unique:users',
            'contact_number'    => 'required|unique:users,contact_number,NULL,id,deleted_at,NULL',
            'address'           => 'required',
            'zipcode'          => 'required',
            'country'           => 'required',
            'city_id'           => 'required',
            'state_id'          => 'required',
            'add_latitude'      => 'required',
            'add_longitude'     => 'required',
            'password'          => 'min:8|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation'=> 'required',
            'avatar'         => 'image|mimes:jpeg,png,jpg,gif|max:3000',
            'device_type'    => 'required',
            'device_token'   => 'required',
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
            $firstname = $lastname = '';
            if(@$request->get('applicant_name')){
                $firstname = @$request->get('applicant_name');
                $lastname = @$request->get('applicant_name');
            }
            if(@$request->get('country_code')){
                $country_code = @$request->get('country_code');
            }else{
                $country_code = '';
            }


            $userdata = User::create([
                'company_name'      => @$request->get('company_name'),
                'email'             => @$request->get('email'),
                'contact_number'    => @$request->get('contact_number'),
                'address'           => @$request->get('address'),
                'country'           => @$request->get('country'),
                'city_id'           => @$request->get('city_id'),
                'state_id'          => @$request->get('state_id'),
                'add_latitude'      => @$request->get('add_latitude'),
                'add_longitude'     => @$request->get('add_longitude'),
                'zipcode'           => @$request->get('zipcode'),
                'avatar'            => @$filename,
                'password'          => \Hash::make($request->get('password')),
                'status'            => 'active',
                'user_type'         => @$request->get('user_type'),
                'sign_up_as'        => 'app',
                'device_type'       => @$request->get('device_type'),
                'device_token'      => @$request->get('device_token'),
                'login_status'      => 'online',
                'driver_signup_as'  => 'company',
                'first_name'        => @$firstname,
                'last_name'         => @$lastname,
                'country_code'      => @$country_code,
            ]);

            $company_details = CompanyDetail::create([
                'company_id'        =>  $userdata->id,
                'recipient_name'    =>  @$request->get('applicant_name'),
                'job_title'         =>  @$request->get('job_title'),
                'company_size'      =>  @$request->get('company_size'),
                'website'           =>  @$request->get('website'),
                'type'              =>  @$request->get('user_type'),
            ]);
            if(!empty($company_details)){
                User::where('id', $userdata->id)->update([ 'company_id'=>$company_details->id ]);
            }

            $user = User::where('id',$userdata->id)->first();
            $data1['token'] = JWTAuth::fromUser($userdata);
            $data1['company'] = $user;
            $data1['company_details'] = $company_details;
            // if(request('ref_id')){
            //     $this->set_refer_user($userdata, request('ref_id'));
            // }
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
                $data1['otpNumber'] = $otpNumber;
            }

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
            $checkwallet = UserCredits::where('user_id', $first_user->id)->first();
            if(!empty($checkwallet)){
                $amount = (int)$checkwallet->amount + (int)$refferal_rate->value;
                UserCredits::where('user_id', $first_user->id)->update([
                    'amount' => $amount,
                ]);
            }else{
                UserCredits::create([
                    'amount' => $refferal_rate->value,
                    'user_id' => $first_user->id
                ]);
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
    | Company Update Profile
    |--------------------------------------------------------------------------
    |
    */
    public function CompanyProfile(Request $request){
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
                $user_data->company_name 	= request('company_name');
                $user_data->address 		= request('address');
                $user_data->country 		= request('country');
                $user_data->state_id 		= request('state_id');
                $user_data->city_id 		= request('city_id');
                $user_data->add_longitude 	= request('add_longitude');
                $user_data->add_latitude 	= request('add_latitude');
                $user_data->save();

                $company_details = CompanyDetail::where('company_id',$user->id)->first();
                if(!empty($company_details)){
                    $company_details->recipient_name 	= request('applicant_name');
                    $company_details->job_title 		= request('job_title');
                    $company_details->company_size 		= request('company_size');
                    $company_details->website 			= request('website');
                    $company_details->save();
                }
                return response()->json(['status' => 'success','message' => 'Profile Update Successfully']);
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
    public function PasswordChange(Request $request){
        $validation_array =[
            'old_password'        => 'required|string|min:8',
            'new_password'        => 'required|string|min:8',
            'confirm_password'    => 'required|string|min:8',
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
            if($user->user_type != 'company') {
                return response()->json(['status'    => 'error','data'      => "Permission denied !!"]);
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
    | List of Company's Driver
    |--------------------------------------------------------------------------
    |
    */
    public function companyDriverList(){
        try{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status' => 'error','message' => "Invalid Token ..."],200);
            }
            if($user->user_type != 'driver' && $user->driver_signup_as != 'company') {
                return response()->json(['status'    => 'error','data'      => "Permission denied !!"]);
            }
            if($user !== null){
                $driverlist = User::where('user_type','driver')->where('driver_company_id',$user->id)->get();
                return response()->json(['status' => 'success','message' => "Company's Driver List.", 'data'=>$driverlist, 'total_driver'=>$driverlist->count()]);
            }else{
                return response()->json(['status'=> 'error','message' => "You are not able login from this "]);
            }
        }catch(\Exception $e){
            return response()->json(['status'=> 'error eee','message'=> $e->getMessage()],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Company Check Driver Wallet
    |--------------------------------------------------------------------------
    |
    */
    public function checkUserWallet(Request $request){
        $validation_array =[
            'user_id'=> 'required',
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
            if($user->user_type != 'driver' && $user->driver_signup_as != 'company') {
                return response()->json(['status'    => 'error','data'      => "Permission denied !!"]);
            }
            if($user !== null){
                $walletdetail = Wallet::where('user_id', $request->get('user_id'))->get();
                return response()->json(['status' => 'success','message' => 'User Wallet Details', 'data'=>$walletdetail]);
            }else{
                return response()->json(['status'=> 'error','message'=> "You are not able login from this "]);
            }
        }catch(\Exception $e){
            return response()->json(['status'=> 'error','message'=> $e->getMessage()],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Company withdraw from Driver Wallet
    |--------------------------------------------------------------------------
    |
    */
    public function withdrawFromWallet(Request $request){
        $validation_array =[
            'user_id'=> 'required',
            'amount'=> 'required|integer',
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
            if($user->user_type != 'company') {
                return response()->json(['status'=> 'error','data'=> "Permission denied !!"]);
            }
            if($user !== null){
                $walletdetail = Wallet::where('user_id', $request->get('user_id'))->first();
                if(!empty($walletdetail)){
                    $getamount = (int)$walletdetail->amount - (int)$request->amount;
                    $companywallet = Wallet::where('user_id', $user->id)->first();
                    if(!empty($companywallet)){
                        $totalcompanyamt = (int)$companywallet->amount + (int)$request->amount;
                        Wallet::where('user_id', $user->id)->update(['amount'=>$totalcompanyamt]);
                    }else{
                        Wallet::create([
                            'amount'=>(int)$request->amount,
                            'user_id'=>$user->id,
                        ]);
                    }
                    Wallet::where('user_id', $request->get('user_id'))->update(['amount'=>$getamount]);
                    return response()->json(['status' => 'success','message' => 'Withdrawal Successfully Completed']);
                }else{
                    return response()->json(['status'=> 'error','message'=> "User Wallet Not Found"]);
                }
            }else{
                return response()->json(['status'=> 'error','message'=> "You are not able login from this "]);
            }
        }catch(\Exception $e){
            return response()->json(['status'=> 'error','message'=> $e->getMessage()],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Rating Reviews
    |--------------------------------------------------------------------------
    |
    */
    public function createRatingReviews(Request $request){
        $validation_array =[
            'rating'          => 'required',
            'to_user_id'      => 'required',
        ];
        $validation = Validator::make($request->all(),$validation_array);
        if($validation->fails()){
            return response()->json(['status'=> 'error','message'=> $validation->errors()->first(),'data'=> (object)[]]);
        }
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            if(request('comment')){
                $comment = request('comment');
            }else{
                $comment = '';
            }
            $checkrating = RatingReviews::where('from_user_id', $user->id)->where('to_user_id', request('to_user_id'))->first();
            if(!empty($checkrating)){
                return response()->json(['status' => 'error','message' => 'You Already Rated!']);
            }
            $data['from_user_id']    =   $user->id;
            $data['to_user_id']      =   request('to_user_id');
            $data['rating']          =   request('rating');
            $data['is_read_user']    =   "read";
            $data['comment']         =   $comment;
            $userratingreviews = RatingReviews::Create($data);
            return response()->json(['status' => 'success','message' => 'You are successfully Rating Reviews!','data' => $data]);
        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Rating Reviews
    |--------------------------------------------------------------------------
    |
    */
    public function getAllEmergencyRequest(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response()->json(['status'=>'error','message' => 'You are not able login from this application...'],200);
            }
            $allrequest = EmergencyRequest::where('status','!=','resolved')->get();
            return response()->json(['status' => 'success','message' => 'All Pending Emergency Request','data' => $allrequest]);
        }catch (Exception $e) {
            return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Create Company Driver
    |--------------------------------------------------------------------------
    |
    */
    public function companyDriverStore(Request $request){
        $validation_array =[
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password'          => 'required|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
            'contact_number'    => 'required|numeric|digits:10',
            'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
            'state_id'          => 'required',
            'city_id'           => 'required',
            'address'           => 'required',
            'country'           => 'required',
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

            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10).'.'.$extension;
                Storage::disk('public')->putFileAs('avatar', $file,$filename);
            }else{
                $filename = 'default.png';
            }

            $userdata=User::create([
                'first_name'            => @$request->get('first_name'),
                'last_name'             => @$request->get('last_name'),
                'avatar'                => @$filename,
                'email'                 => @$request->get('email'),
                'password'              => \Hash::make($request->get('password')),
                'country_code'		    => @$request->country_code,
                'user_type'             => 'driver',
                'login_status'          => 'offline',
                'sign_up_as'            => 'app',
                'doc_status'            => 'pending',
                'contact_number'        => @$request->get('contact_number'),
                'address'               => @$request->get('address'),
                'city_id'               => @$request->get('city_id'),
                'state_id'              => @$request->get('state_id'),
                'country'               => @$request->get('country'),
                'driver_signup_as'      => 'individual',
                'driver_company_id'     => $user->id,
            ]);

            return response()->json(['status' => 'success','message' => 'Company Driver Created Successfully','data' => $userdata]);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }
}
