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

class ComapnyControllerOld extends Controller{
	protected $authLayout = '';
	protected $pageLayout = '';
/**
* Create a new controller instance.
*
* @return void
*/
public function __construct()
{
	$this->authLayout = 'admin.auth.';
	$this->pageLayout = 'admin.pages.companies.';
	$this->middleware('auth');
	View::share('states',State::get()->pluck('state','id'));
	View::share('cities',City::get());
}

public function index(Builder $builder, Request $request)
{
	$users = User::where('user_type','company')->orderBy('id','desc');
	if (request()->ajax()) {
		return DataTables::of($users->get())
		->addIndexColumn()
		->editColumn('avatar', function (User $users) {
			if($users->avatar){
				return "<img src=".url("storage/avatar/".$users->avatar)."  width='60px'/> ";
			}else{
				return "<img src=".url("storage/avatar/default.png")."  width='60px'/> ";
			}
		})
		->editColumn('action', function (User $users) {
			$action = '';

			$action .= '<a class="ml-2 mr-2" href='.route('admin.company.edit',[$users->id]).'><i class="fa fa-pencil"></i></a>';
			if(empty(Booking::where('driver_id', $users->id)->first())) {
				$action .= '<a title="Delete" class="ml-2 mr-2 deleteuser" data-id ="' . $users->id . '" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
			}
			$ridehistory = Booking::where('driver_id', $users->id)->first();
			$parcelhistory = ParcelDetail::where('driver_id', $users->id)->first();
			if(!empty($ridehistory)){
				$action .='<a class="ml-2 mr-2 " href='.route('admin.driver_trip',[$users->id]).'><i class="fa fa-car" title="Ride History"></i></a>';
			}
			if(!empty($parcelhistory)){
				$action .='<a class="ml-2 mr-2 " href='.route('admin.user_parcel_booking',[$users->id]).'><i class="fa fa-truck" title="Parcel Booking History"></i></a>';
			}
			return $action;
		})
		->rawColumns(['action','avatar','status','user_type'])
		->make(true);
	}
	$html = $builder->columns([
		['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
		['data' => 'avatar', 'name'    => 'avatar', 'title' => 'Avatar','width'=>'10%',"orderable" => false, "searchable" => false],
		['data' => 'company_name', 'name' => 'company_name','title'=>'Company Name','width'=>'15%'],
		['data' => 'email', 'name' => 'email', 'title' => 'Email','width'=>'15%'],
		['data' => 'contact_number', 'name' => 'contact_number', 'title' => 'Contact Number','width'=>'5%'],
		['data' => 'state_id', 'name' => 'state_id', 'title' => 'State','width'=>'3%'],
		['data' => 'city_id', 'name' => 'city_id', 'title' => 'City','width'=>'3%'],
		['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
		])
	->parameters([ 'order' =>[] ]);

	$roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
	\View::share('roles',$roles);
	\View::share('title','Company Management');
	return view($this->pageLayout.'index',compact('html'));
}

/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
	$company_detail=array();
	$vehicles = Vehicle::get()->pluck('name','id');
	$ridesetting = RideSetting::get()->pluck('name','id');
	return view($this->pageLayout.'create',compact('company_detail','vehicles','ridesetting'));
}

/**
* Store a newly created resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
	$customMessages = [
	'password.regex' => 'Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters'
	];
	$validatedData = Validator::make($request->all(),[
		'company_name'      => 'required',
		'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
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
			'status'            => 'active',
			'user_type'         => 'company',
			'sign_up_as'        => 'web',
			'contact_number'    => @$request->get('contact_number'),
			'address'           => @$request->get('address'),
			'city_id'           => @$request->get('city_id'),
			'state_id'          => @$request->get('state_id'),
			'country'           => @$request->get('country'),
			'country_code'      => @$request->get('country_code'),
			'country_code_al'   => @$request->get('country_code_al')
			]);

		CompanyDetail::create([
			'company_id'        => $userID->id,
			'recipient_name'    => $request->get('recipient_name'),
			'job_title'         => $request->get('job_title'),
			'company_size'      => $request->get('company_size'),
			'website'           => $request->get('website'),
			]);
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
		Notify::success('Company Created Successfully.');
		return redirect()->route('admin.company.index');
	}catch(\Exception $e){
		return back()->with([
			'alert-type'    => 'danger',
			'message'       => $e->getMessage()
			]);
	}
}

/**
* Show the form for editing the specified resource.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function edit($id)
{
	$states = State::get()->pluck('state','id');
	$cities = City::get();
	$company_detail = User::with(['company_details','driver_details'])->where('id', $id)->first();
	$vehicles = Vehicle::get()->pluck('name','id');
	$ridesetting = RideSetting::get()->pluck('name','id');
	$driverridetypes = DriverDetails::where('driver_id', $id)->get()->pluck('ride_type');
	return view($this->pageLayout.'edit', compact('company_detail','states','cities','driverridetypes','ridesetting','vehicles'));
}

/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
	$customMessages = [
	'password.regex' => 'Password should contain minimum eight characters , one uppercase , one lowercase , one number , one special characters'
	];
	$validatedData = Validator::make($request->all(),[
		'company_name'      => 'required',
		'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
		'state_id'          => 'required',
		'city_id'           => 'required',
		'address'           => 'required',
		'country'           => 'required',
		'recipient_name'    => 'required',
		'job_title'         => 'required',
		'company_size'      => 'required|numeric',
		'website'           => 'required',
		'password'          => 'nullable|min:8',
		'vehicle_image'     => 'sometimes|mimes:jpeg,jpg,png|max:3000',
		],$customMessages);
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
		}
		else{
			if($oldDetails->avatar !== null){
				$filename = $oldDetails->avatar;
			}else{
				$filename = 'default.png';
			}
		}
		$password = $request->get('password') === null ?
		$oldDetails->password : \Hash::make($request->get('password'));
		User::where('id',$id)->update([
			'company_name'      => @$request->get('company_name'),
			'avatar'            => @$filename,
			'password'          => \Hash::make($request->get('password')),
			'address'           => @$request->get('address'),
			'city_id'           => @$request->get('city_id'),
			'state_id'          => @$request->get('state_id'),
			'country'           => @$request->get('country'),
			'contact_number'    => @$request->get('contact_number'),
			'country_code'      => @$request->get('country_code'),
			'country_code_al'   => @$request->get('country_code_al')
			]);
		$company_details = CompanyDetail::where('company_id',$id)->first();
		if(!empty($company_details)){
			CompanyDetail::where('company_id',$id)->update([
				'recipient_name'    => $request->get('recipient_name'),
				'job_title'         => $request->get('job_title'),
				'company_size'      => $request->get('company_size'),
				'website'           => $request->get('website'),
				]);
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
					'vehicle_model' => $request->get('vehicle_model'),
					'vehicle_plate' => $request->get('vehicle_plate'),
					'color' => $request->get('color'),
					'mileage' => $request->get('mileage'),
					'year' => $request->get('year'),
					'vehicle_image' => $vehicle_filename,
					]);

			}else{
				$ride_type = '';
			}
			User::where('id', $id)->update([ 'vehicle_id'=> $request->get('vehicle_id')]);
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

/**
* Remove the specified resource from storage.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
}
