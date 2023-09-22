<?php
namespace App\Http\Controllers\Admin;
use App\Models\Booking;
use App\Models\City;
use App\Models\EmergencyDetails;
use App\Models\ParcelDetail;
use App\Models\Promocodes;
use App\Models\RatingReviews;
use App\Models\State;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use App\Models\UserBehavior;
use Auth;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;
use Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class BehaviorsController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct() {
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.behaviors.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request) {
        $userbehavior = UserBehavior::orderBy('id','DESC');
        if (request()->ajax()) {
            return DataTables::of($userbehavior->get())
            ->addIndexColumn()
            ->editColumn('flag', function(UserBehavior $userbehavior) {
                if($userbehavior->flag == "1"){
                    return "User Behavior";
                }elseif($userbehavior->flag == "0"){
                    return "Driver Behavior";
                }elseif($userbehavior->flag == "2"){
                    return "User Parcel Behavior";
                }elseif($userbehavior->flag == "3"){
                    return "Driver Parcel Behavior";
                }
            })
            ->editColumn('action', function(UserBehavior $userbehavior) {
                return '<a title="Edit" class="btn btn-warning btn-sm ml-1" href='.route('admin.behaviors.edit',[$userbehavior->id]).'><i class="fa fa-pencil"></i></a><a title="Delete" class="btn btn-danger btn-sm deletebehavior ml-1" data-id ="'.$userbehavior->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
            })
            ->rawColumns(['action','flag'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%'],
            ['data' => 'feedback', 'name'    => 'feedback', 'title' => 'feedback Name','width'=>'10%'],
            ['data' => 'flag', 'name'    => 'flag', 'title' => 'flag','width'=>'10%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'10%',"orderable" => false],
        ])->parameters(['order' =>[] ]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        return view($this->pageLayout.'create');
    }

    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'feedback'        => 'required',
            'flag'            => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $userbehavior = UserBehavior::create([
                'feedback'     => @$request->get('feedback'),
                'flag'         => @$request->get('flag'),
            ]);
            Notify::success('Behaviors Created Successfully.');
            return redirect()->route('admin.behaviors.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function edit($id){
        $userbehavior = UserBehavior::find($id);
        return view($this->pageLayout.'edit',compact('userbehavior'));
    }

    public function update(Request $request, $id) {
        $validatedData = Validator::make($request->all(),[
            'feedback'   => 'required',
            'flag'       => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $userbehavior = UserBehavior::where('id',$id)->update([
                'feedback'    => @$request->get('feedback'),
                'flag'        => @$request->get('flag'),
            ]);
            Notify::success('Behaviors Updated Successfully.');
            return redirect()->route('admin.behaviors.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function delete($id){
        $userbehavior = UserBehavior::where('id',$id)->first();
        $userbehavior->delete();
        Notify::success('Behaviors deleted successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Behaviors removed successfully.'
        ]);
    }

    public function live_map(){
        $usersData = User::whereIn('user_type',['driver','company'])
        ->where('latitude', '!=', null)->where('longitude', '!=', null)
        ->get();
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

    public function location_index(){
        $userRole = '';
        $userRole = Helper::checkPermission(['location-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $total_superadmin = User::where('user_type','superadmin')->where('id','!=',auth()->user()->id)->count();
        $total_user = User::where('user_type','user')->count();
        $total_driver = User::where('user_type','driver')->count();
        $total_promocode = Promocodes::count();
        $total_emergencyDetails = EmergencyDetails::count();
        $country = Vehicle::count();
        $total_ratingreviews = RatingReviews::count();
        $total_vehiclecategories = Vehicle::count();
        $total_parcelbooking = ParcelDetail::count();
        $usersData = User::where('user_type','driver')->get();
        $today_transaction = TransactionDetail::whereDay('created_at', Carbon::now()->day);
        $t_amount = $today_transaction->sum('amount');
        $t_admin_profit = Booking::whereDay('created_at', Carbon::now()->day);
        $t_amount_profit = $t_admin_profit->sum('admin_commision');
        $total_admin_profit = Booking::where('trip_status','completed')->get()->pluck('admin_commision');
        $total_admin_pr = $total_admin_profit->sum();
        $state = State::pluck('state','id');
        $city = City::pluck('city','id');
        $taxi = User::where('user_type','driver')->orWhere('user_type','company')->get();
        return view($this->pageLayout.'location',compact('total_superadmin','total_user','total_driver','total_promocode','country','total_ratingreviews','total_vehiclecategories','usersData','total_emergencyDetails','t_amount','t_amount_profit','total_admin_pr','total_parcelbooking','t_admin_profit','state','city','taxi'));
    }
}