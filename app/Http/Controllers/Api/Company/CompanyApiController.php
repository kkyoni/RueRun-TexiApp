<?php
namespace App\Http\Controllers\Api\Company;
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
use App\Models\TripDetails;
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

class CompanyApiController extends Controller{
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
| Add Driver
|--------------------------------------------------------------------------
|
*/
public function addDriver(Request $request){
	$validator = Validator::make($request->all(), [
		'first_name'         => ['required', 'string', 'max:190'],
		'last_name'          => ['required', 'string', 'max:190'],
		'email'              => ['required', 'string', 'email', 'max:190'],
		'password'           => ['required', 'string', 'min:6'],
		'contact_number'     => ['required', 'min:10', 'max:10', 'unique:users'],
		'address'            => ['required', 'string', 'max:255'],
		'country'            => ['required', 'string', 'max:190'],
		'state_id'			 => ['required'],
		'city_id'			 => ['required'],
		'avatar'             => ['image|mimes:jpeg,png,jpg,gif|max:3000'],
	]);
	if($validator->fails()){
		return response()->json(['status'   => 'error','message'  => $validator->messages()->first()]);
	}
	try{

	}catch(Exceptions $e){
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Edit Driver
|--------------------------------------------------------------------------
|
*/
public function editDriver(Request $Request){
	try{

	}catch(Exceptions $e){
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}

/*
|--------------------------------------------------------------------------
| Delete Driver
|--------------------------------------------------------------------------
|
*/
public function deleteDriver(Request $Request){
	try{

	}catch(Exceptions $e){
		return response()->json(['status' => 'error','message' => "Something went Wrong....."],200);
	}
}
}