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

class CronController extends Controller{

/*
|--------------------------------------------------------------------------
| Check Authenticated user
|--------------------------------------------------------------------------
|
*/
	public function cancle_booking(){
	
	}
}
