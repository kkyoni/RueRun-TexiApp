<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\DriverDetails;
use App\Models\ParcelDetail;
use App\Models\RideSetting;
use App\Models\Vehicle;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use Auth;
use Event,Str,Storage;
use App\Models\Booking;
use Validator;
use Ixudra\Curl\Facades\Curl;
use Illuminate\Support\Facades\View;
use config;
use Settings;
use App\Models\User;
use App\Models\CompanyDetail;
use App\Models\CardDetails;
use App\Models\State;
use App\Models\City;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;
use App\Models\VehicleType;
class ComapnyController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.companies.';
        $this->middleware('auth');
        View::share('states',State::get()->pluck('state','id'));
        View::share('cities',City::get()->pluck('city','id'));
        View::share('vehicles',Vehicle::get()->pluck('name','id'));
        View::share('ridesetting',RideSetting::get()->pluck('name','id'));
    }
    public function index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['company-driver-list','company-driver-create','company-driver-edit','company-driver-delete']);
        $permission_data['hasUpdatePermission'] = Helper::checkPermission(['company-driver-edit']);
        $permission_data['hasRemovePermission'] = Helper::checkPermission(['company-driver-delete']);

        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }

        $company = CompanyDetail::with('driverDetail')->whereHas('driverDetail')->where('type','driver')->orderBy('updated_at','desc');
        if (request()->ajax()) {
            return DataTables::of($company->get())
            ->addIndexColumn()
            ->editColumn('driverDetail.avatar', function (CompanyDetail $company) {
                if($company->driverDetail->avatar){
                    if (file_exists( "storage/avatar/".$company->driverDetail->avatar)){
                        return "<img src=".url("storage/avatar/".$company->driverDetail->avatar)."  width='60px'/> ";
                    }else{
                        return "<img src=".url("storage/avatar/default.png")."  width='60px'/> ";
                    }
                }else{
                    return "<img src=".url("storage/avatar/default.png")."  width='60px'/> ";
                }
            })
            ->editColumn('action', function (CompanyDetail $company) use($permission_data){
                $action = '';
                if($permission_data['hasUpdatePermission']){
                    $action .= '<a title="Edit" class="btn btn-warning btn-sm ml-1" href='.route('admin.company.edit',[$company->id]).'><i class="fa fa-pencil"></i></a>';
                }
                $action .= '<a title="View" class="btn btn-info btn-sm ml-1" href='.route('admin.company.companyDriver',[$company->company_id]).'><i class="fa fa-eye"></i></a>';
                return $action;
            })
            ->editColumn('driverDetail.company_name', function (CompanyDetail $company) {
                return $company->driverDetail->company_name;
            })
            ->rawColumns(['action','driverDetail.avatar','status','user_type'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'driverDetail.avatar', 'name'    => 'driverDetail.avatar', 'title' => 'Avatar','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'driverDetail.company_name', 'name' => 'driverDetail.company_name','title'=>'Company Name','width'=>'10%'],
            ['data' => 'recipient_name', 'name' => 'recipient_name', 'title' => 'Recipient Name','width'=>'10%'],
            ['data' => 'job_title', 'name' => 'job_title', 'title' => 'Job Title','width'=>'10%'],
            ['data' => 'company_size', 'name' => 'company_size', 'title' => 'Company Size','width'=>'10%'],
            ['data' => 'website', 'name' => 'website', 'title' => 'Website','width'=>'15%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
            ])
        ->parameters([ 'order' =>[] ]);
        $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
        \View::share('roles',$roles);
        \View::share('title','Company Management');
        return view($this->pageLayout.'index',compact('html'));
    }

    public function companyDriver(Builder $builder,Request $request,$id){
        $users = User::where('user_type','driver')->where('driver_company_id',$id)->orderBy('updated_at','desc');
        if (request()->ajax()) {
            return DataTables::of($users->get())
            ->addIndexColumn()
            ->editColumn('image', function (User $users) {
                $i="";
                if($users->avatar != ""){
                    $i .= "<img src=".url('storage/avatar/'.$users->avatar)."  width='60px'/>";
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
            ->editColumn('action', function (User $users) use ($id) {
                $action = '';
                $action .= '<a title="Edit" class="btn btn-warning btn-sm ml-1" href='.route('admin.company.companyDriverEdit',[$id,$users->id]).'><i class="fa fa-pencil"></i></a>';
                $ridehistory = Booking::where('driver_id', $users->id)->first();
                $parcelhistory = ParcelDetail::where('driver_id', $users->id)->first();
                if(!empty($ridehistory)){
                    $action .='<a class="btn btn-sm btn-success ml-1" href='.route('admin.driver_trip',[$users->id]).'><i class="fa fa-car" title="Ride History"></i></a>';
                }
                if(empty(Booking::where('driver_id', $users->id)->first())) {
                    $action .= '<a title="Delete" class="btn btn-danger ml-1 deleteuser" data-id ="' . $users->id . '" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                }
                if($users->status == "active"){
                    $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeStatus" data-id="'.$users->id.'"><i class="fa fa-unlock"></i></a>';
                }else{
                    $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeStatus" data-id="'.$users->id.'"><i class="fa fa-lock" ></i></a>';
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
            ['data' => 'image', 'name'    => 'avatar', 'title' => 'Avatar','width'=>'10%',"orderable" => false, "searchable" => false],
            ['data' => 'first_name', 'name' => 'first_name', 'title' => 'Name','width'=>'15%'],
            ['data' => 'email', 'name' => 'email', 'title' => 'Email','width'=>'15%'],
            ['data' => 'contact_number', 'name' => 'contact_number', 'title' => 'Contact Number','width'=>'5%'],
            ['data' => 'state_id', 'name' => 'state_id', 'title' => 'State','width'=>'3%'],
            ['data' => 'city_id', 'name' => 'city_id', 'title' => 'City','width'=>'3%'],
            ['data' => 'avgRate', 'name' => 'avgRate', 'title' => 'Average Rating','width'=>'5%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'5%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
            ])
        ->parameters(['order' =>[]]);
        $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
        \View::share('roles',$roles);
        return view($this->pageLayout.'index',compact('html','id'));
    }

    public function companyDriverCreate($id){
        $users=array();
        return view($this->pageLayout.'companyDriverCreate',compact('users','id'));
    }

    public function create(){
        $userRole = Helper::checkPermission(['company-driver-create']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        // $make = VehicleType::get()->pluck('fullname','id');
        $company_detail=array();
        $vehicles = Vehicle::get()->pluck('name','id');
        $ridesetting = RideSetting::get()->pluck('name','id');
        return view($this->pageLayout.'create',compact('company_detail','vehicles','ridesetting'));
        // return view($this->pageLayout.'create',compact('company_detail','vehicles','ridesetting','make'));
    }

    public function store(Request $request){
        $customMessages = ['password.regex' => 'Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters'];
        $validatedData = Validator::make($request->all(),[
            'company_name'      => 'required',
            'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password'          => 'required|min:8',
            'contact_number'    => 'required|numeric|digits:10',
            'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
            'state_id'          => 'required',
            'city_id'           => 'required',
            'address'           => 'required',
            'country'           => 'required',
            'recipient_name'    => 'required',
            'job_title'         => 'required',
            'company_size'      => 'required|numeric',
            'website'           => 'required',
            'vehicle_image'     => 'sometimes|mimes:jpeg,jpg,png|max:3000',
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
            $userID=User::create([
                'company_name'      => @$request->get('company_name'),
                'avatar'            => @$filename,
                'email'             => @$request->get('email'),
                'password'          => \Hash::make($request->get('password')),
                'user_type'         => 'driver',
                'sign_up_as'        => 'web',
                'contact_number'    => @$request->get('contact_number'),
                'address'           => @$request->get('address'),
                'city_id'           => @$request->get('city_id'),
                'state_id'          => @$request->get('state_id'),
                'country'           => @$request->get('country'),
                'status'            => @$request->get('status'),
                'make'              =>$request->get('make'),
                'driver_signup_as' =>  'company'
                ]);
            $companyDetail = CompanyDetail::create([
                'company_id'        => $userID->id,
                'recipient_name'    => $request->get('recipient_name'),
                'job_title'         => $request->get('job_title'),
                'company_size'      => $request->get('company_size'),
                'website'           => $request->get('website'),
                ]);
            $userID->company_id = $companyDetail->id;
            $userID->save();
            if($request->get('vehicle_id')){
                $vehicle_filename='';
                if(isset($request->ride_type)){
                    if($request->hasFile('vehicle_image')){
                        $file = $request->file('vehicle_image');
                        $extension = $file->getClientOriginalExtension();
                        $vehicle_filename = Str::random(10).'.'.$extension;
                        Storage::disk('public')->putFileAs('vehicle_images', $file,$vehicle_filename);
                    }else{
                        $vehicle_filename = 'default.png';
                    }
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
                    User::where('id', $userID->id)->update([
                        'vehicle_id'=> $request->get('vehicle_id'),
                        'first_name'=> $request->get('company_name'),
                        'last_name' => $request->get('company_name'),
                        ]);
                }else{
                    $ride_type = '';
                }
            }
            Notify::success('Company Created Successfully.');
            return redirect()->route('admin.company.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }
    public function show($id){

    }

    public function edit($id){
        $company_detail = CompanyDetail::with('driverDetail')->whereHas('driverDetail')->where('id',$id)->first();
        return view($this->pageLayout.'company_edit', compact('company_detail'));
    }

    public function update(Request $request, $id){
        $validatedData = Validator::make($request->all(),[
            'company_name'      => 'required',
            'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
            'recipient_name'    => 'required',
            'job_title'         => 'required',
            'company_size'      => 'required|numeric',
            'website'           => 'required',
            ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $company_details = CompanyDetail::find($id);
            $oldDetails = User::find($company_details->company_id);
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
            $password = $request->get('password') === null ?
            $oldDetails->password : \Hash::make($request->get('password'));
            User::where('id',$company_details->company_id)->update([
                'avatar'            => @$filename,
                'company_name' =>@$request->company_name
                ]);
            if(!empty($company_details)){
                CompanyDetail::where('id',$id)->update([
                    'recipient_name'    => $request->get('recipient_name'),
                    'job_title'         => $request->get('job_title'),
                    'company_size'      => $request->get('company_size'),
                    'website'           => $request->get('website'),
                    'company_size' =>$request->get('company_size'),
                    ]);
            }
            Notify::success('Company Updated Successfully.');
            return redirect()->route('admin.company.index');
        } catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function companyDriverStore(Request $request,$id){
        $customMessages = ['password.regex' => 'Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters'];
        $validatedData = Validator::make($request->all(),[
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
            if($request->reason_for_inactive){
                $reasoninactive = $request->reason_for_inactive;
            }else{
                $reasoninactive = '';
            }
            $userID=User::create([
                'first_name'            => @$request->get('first_name'),
                'last_name'             => @$request->get('last_name'),
                'avatar'                => @$filename,
                'email'                 => @$request->get('email'),
                'password'              => \Hash::make($request->get('password')),
                'status'                => @$request->get('status'),
                'user_type'             => 'driver',
                'login_status'          => 'offline',
                'sign_up_as'            => 'web',
                'doc_status'            => 'pending',
                'contact_number'        => @$request->get('contact_number'),
                'address'               => @$request->get('address'),
                'city_id'               => @$request->get('city_id'),
                'state_id'              => @$request->get('state_id'),
                'country'               => @$request->get('country'),
                'driver_signup_as'      => 'individual',
                'reason_for_inactive'   => @$reasoninactive,
                'driver_company_id'     => $id,
                ]);
            $userID->save();
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
            return redirect()->route('admin.company.companyDriver',$id);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function companyDriverEdit($id,$did){
        $states = State::get()->pluck('state','id');
        $users = User::with(['card_details', 'driver_details'])->where('id',$did)->first();
        $cities = City::where('state_id', $users->state_id)->get()->pluck('city','id');
        $vehicles = Vehicle::get()->pluck('name','id');
        $ridesetting = RideSetting::get()->pluck('name','id');
        $driverridetypes = DriverDetails::where('driver_id', $did)->get()->pluck('ride_type');
        if(!empty($users)){
            return view($this->pageLayout.'companyDriverEdit',compact('users','id','states','cities','vehicles','ridesetting','driverridetypes','did'));
        }else{
            return redirect()->route('admin.index');
        }
    }

    public function companyDriverUpdate(Request $request,$id,$did){
        $customMessages = ['password.regex' => 'Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters'];
        $validatedData = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'         => 'required',
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
            $oldDetails = User::with(['card_details', 'driver_details'])->where('id',$did)->first();
            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10).'.'.$extension;\
                Storage::disk('public')->putFileAs('avatar', $file,$filename);
            }else{
                if($oldDetails->avatar !== null){
                    $filename = $oldDetails->avatar;
                }else{
                    $filename = 'default.png';
                }
            }
            if($request->reason_for_inactive){
                $reasoninactive = $request->reason_for_inactive;
            }else{
                $reasoninactive = '';
            }
            $password = $request->get('password') === null ?
            $oldDetails->password : \Hash::make($request->get('password'));
            User::where('id',$did)->update([
                'first_name'        => @$request->get('first_name'),
                'last_name'         => @$request->get('last_name'),
                'avatar'            => @$filename,
                'password'          => @$password,
                'status'            => @$request->get('status'),
                'address'           => @$request->get('address'),
                'country'           => @$request->get('country'),
                'city_id'           => @$request->get('city_id'),
                'state_id'          => @$request->get('state_id'),
                'reason_for_inactive'   => @$reasoninactive
                ]);
            CardDetails::where('user_id',$did)->delete();
            if(isset($request->card_number)){
                foreach ($request->card_number as $key => $value) {
                    if ($request->card_number[$key]) {
                        $card_details['user_id'] = $did;
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
                    DriverDetails::where('driver_id',$did)->delete();
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
                        DriverDetails::create([ 'driver_id' => $did,'ride_type' => $ridetype ]);
                    }
                    DriverDetails::where('driver_id' ,$did)->update([
                        'vehicle_model' => $request->get('vehicle_model'),
                        'vehicle_plate' => $request->get('vehicle_plate'),
                        'color' => $request->get('color'),
                        'mileage' => $request->get('mileage'),
                        'year' => $request->get('year'),
                        'vehicle_image' => $vehicle_filename,
                        ]);
                    User::where('id', $did)->update([ 'vehicle_id'=> $request->get('vehicle_id')]);
                }else{
                    $ride_type = '';
                }
            }
            Notify::success('Driver Updated Successfully.');
            return redirect()->route('admin.company.companyDriver',$id);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }
}