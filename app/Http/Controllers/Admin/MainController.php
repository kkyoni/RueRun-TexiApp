<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Promocodes;
use App\Models\EmergencyDetails;
use App\Models\Vehicle;
use App\Models\RatingReviews;
use App\Models\TransactionDetail;
use App\Models\Booking;
use App\Models\Setting;
use App\Models\State;
use App\Models\City;
use App\Models\ParcelDetail;
use Carbon\Carbon;
use Response;

class MainController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = 'admin.pages.';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.';
        $this->middleware('auth');
    }

    public function index(){
        return view('front.auth.login');
    }

    public function dashboard(){
        $house_edge = Setting::where('code','house_edge')->first();
        $total_superadmin = User::where('user_type','superadmin')->where('id','!=',auth()->user()->id)->count();
        $total_user = User::where('user_type','user')->count();
        $total_driver = User::where('user_type','driver')->where('driver_signup_as','individual')->count();
        $total_promocode = Promocodes::where('status','active')->get()->count();
        $total_emergencyDetails = EmergencyDetails::where('status','active')->get()->count();
        $country = Vehicle::count();
        $total_ratingreviews = RatingReviews::count();
        $total_vehiclecategories = Vehicle::where('status','active')->get()->count();
        $total_parcelbooking = ParcelDetail::count();
        $usersData = User::where('user_type','driver')->get();
        $today_transaction = TransactionDetail::whereDay('created_at', Carbon::now()->day);
        $t_amount = $today_transaction->sum('amount');
        $t_admin_profit = TransactionDetail::whereDay('created_at', Carbon::now()->day);
        $allrides = Booking::where('booking_date','=',date('Y-m-d'))->where('trip_status','completed')->get();
        $total_per_day=0;
        if(sizeof($allrides) > 0){
            $total_per_day1 = array_sum(array_pluck($allrides, 'total_amount'));
            $total_per_day = $total_per_day1 * $house_edge->value /100;
        }
        $t_amount_profit['earnings_total_amount'] = (int)$total_per_day;
        // dd($daily_data['earnings_total_amount']);
        // $t_amount_profit = $t_admin_profit->sum('amount');
        // dd($t_amount_profit);
        $total_admin_profit = Booking::where('trip_status','completed')->get()->pluck('admin_commision');
        $total_admin_pr = $total_admin_profit->sum();
        $parcel_admin_profit = ParcelDetail::where('parcel_status','completed')->get()->pluck('admin_commision');
        $total_admin_pr = (float)$total_admin_pr + $parcel_admin_profit->sum();
        $state = State::pluck('state','id');
        $city = City::pluck('city','id');
        $taxi = null;
        $total_company = User::where('user_type','driver')->where('driver_signup_as','company')->where('driver_company_id',null)->count();
        return view('admin.pages.dashboard',compact('total_superadmin','total_user','total_driver','total_promocode','country','total_ratingreviews','total_vehiclecategories','usersData','total_emergencyDetails','t_amount','t_amount_profit','total_admin_pr','total_parcelbooking','t_admin_profit','state','city','taxi','total_company'));
    }

    public function live_map(){
        $usersData = User::where('user_type','driver')->where('latitude', '!=', null)->where('longitude', '!=', null)->orWhere('user_type','company')->get();
        return Response::json(['data'=>$usersData]);
    }

    public function get_city(Request $request){
        $option = "<option value=''>Select State</option>";
        $city = City::where('state_id',$request->id)->get();
        foreach ($city as $key => $value) {
            $option .= "<option value='" . $value->id . "'>" . $value->city . "</option>";
        }
        return Response::json([$option]);
    }

    public function get_taxi(Request $request){
        $option = "<option value=''>Select Driver</option>";
        $city = User::where('city_id',$request->id)->get();
        foreach ($city as $key => $value) {
            $option .= "<option value='" . $value->id . "'>".$value->first_name."</option>";
        }
        return Response::json([$option]);
    }

    public function get_taxi_lat_long(Request $request){
        $user = User::where('id',$request->taxi_id)->first();
        return Response::json(['latitude'=>$user->latitude,'longitude'=>$user->longitude]);
    }

    public function sidebar_pending_list(){
        $total_pending_doc = User::where('doc_status','pending')->count();
        return view('admin.pages.includes.sideBar.blade',compact('total_pending_doc'));
    }
}