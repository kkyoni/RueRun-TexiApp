<?php
namespace App\Http\Controllers\Admin;
use App\Models\CompanyDetail;
use App\Models\DriverDetails;
use App\Models\DriverDocuments;
use App\Models\DriverVehicleDocument;
use App\Models\RideSetting;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DataTables,Notify,Validator,Str,Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use phpDocumentor\Reflection\Types\Null_;
use Yajra\DataTables\Html\Builder;
use Auth;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Settings;
use App\Models\User;
use App\Models\Booking;
use App\Models\CardDetails;
use App\Models\State;
use App\Models\City;
use App\Models\RatingReviews, App\Models\ParcelDetail, App\Models\EmergencyType;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class DriverController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.driver.';
        $this->middleware('auth');
        View::share('states',State::get()->pluck('state','id'));
        View::share('cities',City::get()->pluck('city','id'));
        View::share('vehicles',Vehicle::get()->pluck('name','id'));
        View::share('ridesetting',RideSetting::get()->pluck('name','id'));
    }

    public function index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['driver-list','driver-create','driver-edit','driver-delete']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $permission_data['hasUpdatePermission'] = Helper::checkPermission(['driver-edit']);
        $permission_data['hasRemovePermission'] = Helper::checkPermission(['driver-delete']);

        $users = User::where('user_type','driver')->where('driver_signup_as','individual')->where('driver_company_id',null)->orderBy('id','desc');
        if (request()->ajax()) {
            return DataTables::of($users->get())
            ->addIndexColumn()
            ->editColumn('image', function (User $users) {
                $i="";
                if($users->avatar != ""){
                    if (file_exists( 'storage/avatar/'.$users->avatar)) {
                        $i .= "<img src=".url('storage/avatar/'.$users->avatar)."  width='60px'/>";
                    }else{
                        $i .= "<img src=".url('storage/avatar/default.png')."  width='60px'/>";
                    }
                }else{
                    $i .= "<img src=".url('storage/avatar/default.png')."  width='60px'/>";
                }
                return $i;
            })
            ->editColumn('status', function (User $operator) {
                if ($operator->status == "active") {
                    return '<span class="label label-success">Active</span>';
                } else {
                    return '<span class="label label-danger">Block</span>';
                }
            })
            ->editColumn('total_driver_trip', function (User $users) {
                return '<a title="View" class="ml-2 mr-2 " href='.route('admin.driver_trip',[$users->id]).'><i class="fa fa-eye"></i></a>';
            })
            ->editColumn('action', function (User $users) use($permission_data) {
                $action = '';
                if($permission_data['hasUpdatePermission']){
                    $action .= '<a title="Edit" class="btn btn-warning btn-sm ml-1" href='.route('admin.driver.edit',[$users->id]).'><i class="fa fa-pencil"></i></a>';
                }
                $ridehistory = Booking::where('driver_id', $users->id)->first();
                $parcelhistory = ParcelDetail::where('driver_id', $users->id)->first();
                if(!empty($ridehistory)){
                    $action .='<a class="btn btn-sm btn-success ml-1" href='.route('admin.driver_trip',[$users->id]).'><i class="fa fa-car" title="Ride History"></i></a>';
                }
                if($permission_data['hasRemovePermission']){
                    if(empty(Booking::where('driver_id', $users->id)->first())) {
                        $action .= '<a title="Delete" class="btn btn-danger btn-sm deleteuser ml-1" data-id ="' . $users->id . '" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                    }
                }
                if($users->status == "active"){
                    $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$users->id.'"><i class="fa fa-unlock"></i></a>';
                }else{
                    $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$users->id.'"><i class="fa fa-lock" ></i></a>';
                }
                return $action;
            })
            ->editColumn('total_uuid', function (User $users) {
                $total_uuid = User::where('ref_id',$users->uuid)->get()->count();
                return $total_uuid;
            })
            ->editColumn('avgRate', function (User $users) {
                if($users->avgRate>4 && $users->avgRate<=5){
                    return 'Very Good';
                }elseif($users->avgRate>3 && $users->avgRate<=4){
                    return 'Good';
                }elseif($users->avgRate>2 && $users->avgRate<=3){
                    return 'Star Sever Penalty';
                }elseif($users->avgRate>0 && $users->avgRate<=2){
                    return 'Blocking The User';
                }elseif($users->avgRate==0){
                    return 'Not Rated Yet';
                }
            })
            ->editColumn('first_name', function (User $users) {
                if( !empty($users->first_name)){
                    return $users->first_name.' '.$users->last_name;
                }else if($users->company_name){
                    return $users->company_name;
                }else{
                    return '-';
                }
            })
            ->rawColumns(['action','image','status'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'image', 'name'    => 'avatar', 'title' => 'Avatar','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'first_name', 'name' => 'first_name', 'title' => 'Name','width'=>'10%'],
            ['data' => 'email', 'name' => 'email', 'title' => 'Email','width'=>'10%'],
            ['data' => 'avgRate', 'name' => 'avgRate', 'title' => 'Average Rating','width'=>'5%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'5%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'10%',"orderable" => false, "searchable" => false],
            ])
        ->parameters(['order' =>[]]);
        $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
        \View::share('roles',$roles);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        $userRole = Helper::checkPermission(['driver-create']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        // $car_type = VehicleType::get()->pluck('fullname','id');
        // dd($car_type);
        // $make = VehicleType::get()->pluck('fullname','id');
        $users=array();
        return view($this->pageLayout.'create',compact('users'));
        // return view($this->pageLayout.'create',compact('users','make'));
    }

    public function edit($id){
        $states = State::get()->pluck('state','id');
        $users = User::with(['card_details', 'driver_details'])->where('id',$id)->first();
        $cities = City::where('state_id', $users->state_id)->get()->pluck('city','id');
        $vehicles = Vehicle::get()->pluck('name','id');
        // $make = VehicleType::get()->pluck('fullname','id');
        // dd($make,$users->driver_details->vehicle_model_id);
        $ridesetting = RideSetting::get()->pluck('name','id');
        $driverridetypes = DriverDetails::where('driver_id', $id)->get()->pluck('ride_type');
        $driver_booking_tips = Booking::where('trip_status','completed')->where('tip_amount_status','pending')->where('driver_id',$id)->select('tip_amount','id')->get();
        $driver_bookingtip_amount = (float)array_sum(Arr::pluck($driver_booking_tips, 'tip_amount'));
        $driver_parcel_tips = ParcelDetail::where('parcel_status','completed')->where('tip_amount_status','pending')->where('driver_id',$id)->select('tip_amount','id')->get();
        $driver_parceltip_amount = (float)array_sum(Arr::pluck($driver_parcel_tips, 'tip_amount'));
        $tip_amount = (float)$driver_bookingtip_amount + (float)$driver_parceltip_amount;
        if(!empty($users)){
            return view($this->pageLayout.'edit',compact('users','id','states','cities','vehicles','ridesetting','driverridetypes','tip_amount'));
        }else{
            return redirect()->route('admin.index');
        }
    }

    public function store(Request $request){
        $userRole = Helper::checkPermission(['driver-edit']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $customMessages = [
        'password.regex' => 'Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters',
        'first_name.required' => 'First Name is Required',
        'last_name.required' => 'Last Name is Required',
        'email.required' => 'Email Address is Required',
        'contact_number.required' => 'Contact Number is Required',
        'state_id.required' => 'State is Required',
        'city_id.required' => 'City is Required',
        'address.required' => 'Address is Required',
        'country.required' => 'Country is Required',
        'password.required' => 'Password is Required',
        ];
        $validatedData = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password'          => 'required|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
            'contact_number'    => 'required|numeric|digits:10|unique:users,contact_number,NULL,id,deleted_at,NULL',
            'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
            'state_id'          => 'required',
            'city_id'           => 'required',
            'address'           => 'required',
            'country'           => 'required',
            ],$customMessages);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
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
            if(Auth::user()->user_type === 'company'){
                $driver_signup_as = 'company';
                $company_id = Auth::User()->id;
            }else{
                $driver_signup_as = 'individual';
                $company_id = 0;
            }
            if($request->reason_for_inactive){
                $reasoninactive = $request->reason_for_inactive;
            }else{
                $reasoninactive = '';
            }
            $userID=User::create([
                'first_name'        => @$request->get('first_name'),
                'last_name'         => @$request->get('last_name'),
                'avatar'            => @$filename,
                'email'             => @$request->get('email'),
                'password'          => \Hash::make($request->get('password')),
                'status'            => @$request->get('status'),
                'user_type'         => 'driver',
                'login_status'      => 'offline',
                'sign_up_as'        => 'web',
                'doc_status'        => 'pending',
                'contact_number'    => @$request->get('contact_number'),
                'address'           => @$request->get('address'),
                'city_id'           => @$request->get('city_id'),
                'state_id'          => @$request->get('state_id'),
                'country'           => @$request->get('country'),
                'driver_signup_as'  => $driver_signup_as,
                'company_id'        => $company_id,
                'make'              =>$request->get('make'),
                'reason_for_inactive'   => @$reasoninactive,
                'country_code'          => @$request->get('country_code'),
                'country_code_al'   => @$request->get('country_code_al')
                ]);
            $userID->save();
            $total_first_name = strlen($request->first_name);
            if($total_first_name == "1"){
                $total_first_name = $request->first_name.random_int(1000000, 9999999);
            }elseif($total_first_name == "2"){
                $total_first_name = $request->first_name.random_int(100000, 999999);
            }elseif($total_first_name == "3"){
                $total_first_name = $request->first_name.random_int(10000, 99999);
            }elseif($total_first_name == "4"){
                $total_first_name = $request->first_name.random_int(1000, 9999);
            }else {
                $total_first_name1 = substr($request->first_name, 0, 4);
                $total_first_name = $total_first_name1.random_int(1000, 9999);
            }
            User::where('id',$userID->id)->update(['uuid' => strtoupper($total_first_name)]);
            if(!empty($request->card_number[0])){
                foreach ($request->card_number as $key => $value) {
                    if ($request->card_number[$key]) {
                        $card_details['user_id'] = $userID->id;
                        $card_details['card_number'] = $request->card_number[$key];
                        $card_details['card_holder_name'] = $request->card_holder_name[$key];
                        $card_details['card_expiry_month'] = $request->card_expiry_month[$key];
                        $card_details['card_expiry_year'] = $request->card_expiry_year[$key];
                        $card_details['billing_address'] = $request->billing_address[$key];
                        $card_details['bank_name'] = $request->bank_name[$key];
                        $card_details['card_name'] = $request->card_name[$key];
                        $card_details['cvv'] = $request->cvv[$key];
                        CardDetails::create($card_details);
                    }
                }
            }
            if($request->get('vehicle_id')){
                $vehicle_filename='';
                if($request->hasFile('vehicle_image')){
                    $file = $request->file('vehicle_image');
                    $extension = $file->getClientOriginalExtension();
                    $vehicle_filename = Str::random(10).'.'.$extension;
                    Storage::disk('public')->putFileAs('vehicle_images', $file,$vehicle_filename);
                }else{
                    $vehicle_filename = 'default.png';
                }
                if(isset($request->ride_type)){
                    foreach ($request->ride_type as $ridetype){
                        DriverDetails::create([ 'driver_id' => $userID->id,'ride_type' => $ridetype ]);
                    }
                    DriverDetails::where('driver_id' ,$userID->id)->update([
                        'vehicle_model_id' => @$request->get('vehicle_model_id'),
                        'vehicle_model' => $request->get('vehicle_model'),
                        'vehicle_plate' => $request->get('vehicle_plate'),
                        'color' => $request->get('color'),
                        'mileage' => $request->get('mileage'),
                        'year' => $request->get('year'),
                        'vehicle_image' => $vehicle_filename,
                        ]);
                    User::where('id', $userID->id)->update([ 'vehicle_id'=> $request->get('vehicle_id')]);
                }else{
                    $ride_type = '';
                }
            }
            Notify::success('Driver Created Successfully.');
            return redirect()->route('admin.driver.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function update(Request $request,$id){
        // dd($request->all());
        $customMessages = [
        'password.regex' => 'Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters',
        'first_name.required' => 'First Name is Required',
        'last_name.required' => 'Last Name is Required',
        'state_id.required' => 'State is Required',
        'city_id.required' => 'City is Required',
        'address.required' => 'Address is Required',
        'country.required' => 'Country is Required',
        ];
        $validatedData = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'sometimes',
            'contact_number'    => 'sometimes',
            'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
            'state_id'          => 'required',
            'city_id'           => 'required',
            'address'           => 'required',
            'country'           => 'required',
            'password'          => 'nullable|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/'
            ], $customMessages);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $oldDetails = User::with(['card_details', 'driver_details'])->where('id',$id)->first();
            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10).'.'.$extension;
                Storage::disk('public')->putFileAs('avatar', $file,$filename);
            }else{
                if($oldDetails->avatar !== null){
                    $filename = $oldDetails->avatar;
                }else{
                    $filename = 'default.png';
                }
            }
            if($request->reason_for_inactive && (@$request->get('status') === 'inactive')){
                $reasoninactive = $request->reason_for_inactive;
            }else{
                $reasoninactive = '';
            }
            $password = $request->get('password') === null ?
            $oldDetails->password : \Hash::make($request->get('password'));
            User::where('id',$id)->update([
                'first_name'        => @$request->get('first_name'),
                'last_name'         => @$request->get('last_name'),
                'avatar'            => @$filename,
                'password'          => @$password,
                'status'            => @$request->get('status'),
                'address'           => @$request->get('address'),
                'country'           => @$request->get('country'),
                'city_id'           => @$request->get('city_id'),
                'state_id'          => @$request->get('state_id'),
                'make'              =>$request->get('make'),
                'reason_for_inactive'=> @$reasoninactive,
                'country_code'       => @$request->get('country_code'),
                'country_code_al'   => @$request->get('country_code_al'),
                'contact_number'    => @$request->get('contact_number'),
                ]);
            if(isset($request->card_number)){
                foreach ($request->card_number as $key => $value) {
                    if ($request->card_number[$key]) {
                        $card_details['user_id'] = $id;
                        $card_details['card_number'] = $request->card_number[$key];
                        $card_details['card_holder_name'] = $request->card_holder_name[$key];
                        $card_details['card_expiry_month'] = $request->card_expiry_month[$key];
                        $card_details['card_expiry_year'] = $request->card_expiry_year[$key];
                        $card_details['billing_address'] = $request->billing_address[$key];
                        $card_details['bank_name'] = $request->bank_name[$key];
                        $card_details['card_name'] = $request->card_name[$key];
                        $card_details['cvv'] = $request->cvv[$key];
                        CardDetails::create($card_details);
                    }
                }
            }
            if($request->get('vehicle_id')){
                $vehicle_filename='';
                if(isset($request->ride_type)){
                    DriverDetails::where('driver_id',$id)->delete();
                    if($request->hasFile('vehicle_image')){
                        $file = $request->file('vehicle_image');
                        $extension = $file->getClientOriginalExtension();
                        $vehicle_filename = Str::random(10).'.'.$extension;
                        Storage::disk('public')->putFileAs('vehicle_images', $file,$vehicle_filename);
                    }else if (isset($oldDetails->driver_details->vehicle_image)){
                        $vehicle_filename = $oldDetails->driver_details->vehicle_image;
                    }else{
                        $vehicle_filename = 'default.png';
                    }
                    foreach ($request->ride_type as $ridetype){
                        DriverDetails::create([ 'driver_id' => $id,'ride_type' => $ridetype ]);
                    }
                    DriverDetails::where('driver_id' ,$id)->update([
                        'vehicle_model_id' => @$request->get('vehicle_model_id'),
                        'vehicle_model' => $request->get('vehicle_model'),
                        'vehicle_plate' => $request->get('vehicle_plate'),
                        'color' => $request->get('color'),
                        'mileage' => $request->get('mileage'),
                        'year' => $request->get('year'),
                        'vehicle_image' => $vehicle_filename,
                        ]);
                    User::where('id', $id)->update([ 'vehicle_id'=> $request->get('vehicle_id')]);
                }else{
                    $ride_type = '';
                }
            }
            Notify::success('Driver Updated Successfully.');
            return redirect()->route('admin.driver.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function delete($id){
        try{
            $checkUser = User::where('id',$id)->first();
            $checkUser->delete();
            $deletecarddetails = CardDetails::where('user_id',$id)->get();
            if(isset($deletecarddetails)){
                foreach ($deletecarddetails as $key => $value) {
                    $value->delete();
                }
            }
            DriverDocuments::where('driver_id', $id)->delete();
            DriverVehicleDocument::where('driver_id', $id)->delete();
            DriverDetails::where('driver_id', $id)->delete();
            Notify::success('Driver deleted successfully.');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Driver deleted successfully.'
                ]);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function change_status(Request $request){
        try{
            $userRole = Helper::checkPermission(['driver-delete']);
            if(!$userRole){
                $message = "You don't have permission to access this module.";
                return view('error.permission',compact('message'));
            }
            $user = User::where('id',$request->id)->first();
            if($user === null){
                return redirect()->back()->with([
                    'status'    => 'warning',
                    'title'     => 'Warning!!',
                    'message'   => 'Driver not found !!'
                    ]);
            }else{
                if($user->status == "active"){
                    User::where('id',$request->id)->update(['status' => "inactive"]);
                }
                if($user->status == "inactive"){
                    User::where('id',$request->id)->update(['status'=> "active" ]);
                }
            }
            Notify::success('Driver status updated successfully !!');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Driver status updated successfully.'
                ]);
        }catch (Exception $e){
            return response()->json([
                'status'    => 'error',
                'title'     => 'Error!!',
                'message'   => $e->getMessage()
                ]);
        }
    }

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
                }else{

                }
                $user_data->avatar     = $filename;
                $user_data->first_name = request('first_name');
                $user_data->last_name  = request('last_name');
                $user_data->contact_number = request('contact_number');
                $user_data->country_code   = @$request->get('country_code');
                $user_data->country_code_al= @$request->get('country_code_al');
                $user_data->address  = request('address');
                $user_data->country  = request('country');
                $user_data->city     = request('city');
                $user_data->password = request('password');
                $user_data->save();
                return response()->json(['status' => 'success','message' => 'Profile Update Successfully','data' => $user_data]);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error','message' => $e->getMessage()], 200);
        }
    }

    public function driver_trip(Builder $builder, Request $request,$id){
        $driver_trip = Booking::with('user','driver')->where('driver_id',$id)->orderBy('driver_id','desc');
        if (request()->ajax()) {
            return DataTables::of($driver_trip->get())
            ->addIndexColumn()
            ->editColumn('action', function (Booking $driver_trip) {
                return '<a title="View" class="btn btn-info ml-1 btn-sm showtrip" data-id ="'.$driver_trip->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
            })
            ->editColumn('user_name', function (Booking $driver_trip) {
                return $driver_trip->user->first_name;
            })
            ->editColumn('total_amount', function (Booking $driver_trip) {
                return "$".$driver_trip->total_amount."";
            })
            ->editColumn('ride_type_date', function (Booking $booking) {
                if($booking->trip_status !== 'completed' && $booking->trip_status !== 'cancelled' && (Carbon::parse($booking->booking_date)->greaterThan(Carbon::now()->format('Y-m-d')) )){
                    return "Upcoming Ride";
                }else if(($booking->trip_status == 'completed') || ($booking->trip_status == 'cancelled') && (Carbon::parse($booking->booking_date)->lessThan(Carbon::now()->format('Y-m-d')))){
                    return "Past Ride - ".$booking->trip_status;
                }else if($booking->trip_status == 'pending' && $booking->driver_id === null){
                    return "Ride Requested (Driver Not Found)";
                }else if($booking->trip_status == 'accepted'){
                    return "Ride Accepted";
                } else{
                    return 'Ride Requested';
                }
            })
            ->editColumn('driver_name', function (Booking $driver_trip) {
                return $driver_trip->driver->first_name;
            })
            ->rawColumns(['action','driver_name','user_name'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'user_name', 'name' => 'user_name', 'title' => 'User name','width'=>'5%'],
            ['data' => 'driver_name', 'name' => 'driver_name', 'title' => 'Driver name','width'=>'5%'],
            ['data' => 'pick_up_location', 'name' => 'pick_up_location', 'title' => 'Pick up location','width'=>'5%'],
            ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop location','width'=>'5%'],
            ['data' => 'total_km', 'name' => 'total_km', 'title' => 'Total Km','width'=>'5%'],
            ['data' => 'ride_type_date', 'name' => 'ride_type_date', 'title' => 'Upcoming/Past Ride','width'=>'5%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'5%'],
            ])
        ->parameters(['order' =>[]]);
        return view($this->pageLayout.'driver_trip_details',compact('html'));
    }

    public function d_trip_info($id){
        $tripDetails = Booking::with('driver','user','ride_type_id')->where('id',$id)->first();
        return response()->json([
            'driver'       => $tripDetails['driver']->first_name,
            'user'       => $tripDetails['user']->first_name,
            'pick_up_location'       => $tripDetails->pick_up_location,
            'drop_location'       => $tripDetails->drop_location,
            'start_time'       => $tripDetails->start_time,
            'end_time' => $tripDetails->end_time,
            'hold_time' => $tripDetails->hold_time,
            'base_fare' => $tripDetails->base_fare,
            'total_km' => $tripDetails->total_km,
            'admin_commision' => $tripDetails->admin_commision,
            'transaction_id'  =>$tripDetails->transaction_id,
            'trip_status'  =>$tripDetails->trip_status,
            'extra_notes'  =>$tripDetails->extra_notes,
            'promo_name'  =>$tripDetails->promo_name,
            'promo_amount'  =>$tripDetails->promo_amount,
            'promo_id'  =>$tripDetails->promo_id,
            'total_amount'  => $tripDetails->total_amount,
            'trip_type_status' => @$tripDetails['ride_type_id']->name,
            ]);
    }

    public function getcities(Request $request){
        try{
            $all_cities = City::where('state_id',$request->id)->get();
            return response()->json([
                'all_cities'    => $all_cities
                ]);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function setadminrating(Request $request){
        try{
            $checkrating = RatingReviews::where('from_user_id', Auth::User()->id)->where('to_user_id', $request->get('userid'))->first();
            if(empty($checkrating)){
                $rating = RatingReviews::create([
                    'from_user_id' => Auth::User()->id,
                    'to_user_id' => $request->get('userid'),
                    'rating' => $request->get('rating'),
                    'is_read_user' => 'read',
                    'status' => 'approved'
                    ]);
                return response()->json(['rating'=>$rating ]);
            }else{
                return back()->with([
                    'alert-type'    => 'error',
                    'message'       => 'Already Rated'
                    ]);
            }
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function sendDriverTipAmount(Request $request){
        try{
            $driver_booking_tips = Booking::where('trip_status','completed')->where('tip_amount_status','pending')->where('driver_id',$request->id)->select('tip_amount','id')->get();
            $driver_parcel_tips = ParcelDetail::where('parcel_status','completed')->where('tip_amount_status','pending')->where('driver_id',$request->id)->select('tip_amount','id')->get();
            $booking_ids = Arr::pluck($driver_booking_tips, 'id');
            $parcel_ids = Arr::pluck($driver_parcel_tips, 'id');
            Booking::whereIn('id', $booking_ids)->update(['tip_amount_status'=>'transferred']);
            ParcelDetail::whereIn('id', $parcel_ids)->update(['tip_amount_status'=>'transferred']);
            $checkdriverwallet = Wallet::where('user_id', $request->id)->first();
            if($request->tip_amount){
                if(!empty($checkdriverwallet)){
                    $final_amount = (float)$request->tip_amount + (float)$checkdriverwallet->amount;
                    Wallet::where('user_id', $request->id)->update([ 'amount'=>$final_amount ]);
                }else{
                    Wallet::create(['user_id'=> $request->id, 'amount'=>(float)$request->tip_amount ]);
                }
            }else{
                Notify::error('Tip Amount not found !!');
                return redirect()->route('admin.driver.index');
            }
            return redirect()->route('admin.driver.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }
}