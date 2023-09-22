<?php

namespace App\Http\Controllers\Admin;

use App\Models\CompanyDetail;
use App\Models\DriverDocuments;
use App\Models\ParcelDetail;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DataTables,Notify,Str,Storage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Auth;
use App\Models\User;
use App\Models\Booking;
use App\Models\CardDetails;
use Event;
use Settings;
use App\Models\State;
use App\Models\City;
use App\Models\RatingReviews;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class UsersController extends Controller
{
	protected $authLayout = '';
	protected $pageLayout = '';
	protected $contactsLayout = '';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    	$this->authLayout = 'admin.auth.';
    	$this->pageLayout = 'admin.pages.user.';
    	$this->contactsLayout = 'admin.pages.contacts.';
    	$this->middleware('auth');

    	View::share('states',State::get()->pluck('state','id'));
    	View::share('cities',City::get()->pluck('city','id'));
    }
    public function index(Builder $builder, Request $request)
    {
    	$userRole = '';
    	$userRole = Helper::checkPermission(['user-list','user-create','user-edit','user-delete']);
    	if(!$userRole){
    		$message = "You don't have permission to access this module.";
    		return view('error.permission',compact('message'));
    	}
    	$permission_data['hasUpdatePermission'] = Helper::checkPermission(['user-edit']);
    	$permission_data['hasRemovePermission'] = Helper::checkPermission(['user-delete']);

    	$users = User::where('user_type','user')->orderBy('id','desc');
    	if (request()->ajax()) {
    		return DataTables::of($users->get())
    		->addIndexColumn()
    		->editColumn('avatar', function (User $users) {
    			if($users->avatar){
    				$i='';
    				if (file_exists( 'storage/avatar/'.$users->avatar)) {
    					$i .= "<img src=".url("storage/avatar/".$users->avatar)." style='max-width:50px;max-height:50px;'/> ";
    				}else{
    					$i .= "<img src=".url("storage/avatar/default.png")."  style='max-width:50px;max-height:50px;'/> ";
    				}
    				return $i;
    			}else{
    				return "<img src=".url("storage/avatar/default.png")."  style='max-width:50px;max-height:50px;'/> ";
    			}
    		})
    		->editColumn('status', function (User $operator) {
    			if ($operator->status == "active") {
    				return '<span class="label label-success">Active</span>';
    			} else {
    				return '<span class="label label-danger">Block</span>';
    			}
    		})
    		->editColumn('action', function (User $users) use($permission_data){
    			$action  = '';
    			if($permission_data['hasUpdatePermission']){
    				$action .= '<a title="Edit" class="btn btn-warning btn-sm ml-1" href='.route('admin.edit',[$users->id]).'><i class="fa fa-pencil"></i></a>';
    			}

    			if($users->status == "active"){
    				$action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$users->id.'"><i class="fa fa-unlock"></i></a>';
    			}else{
    				$action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$users->id.'"><i class="fa fa-lock" ></i></a>';
    			}

    			$ridehistory = Booking::where('user_id', $users->id)->first();
    			$parcelhistory = ParcelDetail::where('user_id', $users->id)->first();

    			if(!empty($ridehistory)){
    				$action .='<a class="btn btn-sm btn-success ml-1" href='.route('admin.user_trip',[$users->id]).'><i class="fa fa-car" title="Ride History"></i></a>';
    			}
    			if(!empty($parcelhistory)){
    				$action .='<a class="btn btn-sm btn-success ml-1" href='.route('admin.user_parcel_booking',[$users->id]).'><i class="fa fa-truck" title="Parcel Booking History"></i></a>';
    			}
    			if($permission_data['hasRemovePermission']){
    				if(empty($ridehistory) && empty($parcelhistory)){
    					$action .='<a title="Delete" class="btn btn-danger ml-1 btn-sm deleteuser" data-id ="'.$users->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
    				}
    			}
    			return $action;
    		})
    		->editColumn('first_name', function (User $users) {
    			if($users->first_name){
    				return $users->first_name.' '.$users->last_name;
    			}else if($users->company_name){
    				return $users->company_name;
    			}else{
    				return '-';
    			}
    		})
    		->editColumn('total_uuid', function (User $users) {
    			$total_uuid = User::where('ref_id',$users->uuid)->get()->count();
    			return $total_uuid;
    		})
    		->rawColumns(['action','avatar','status','user_type','user_trip'])
    		->make(true);
    	}
    	$html = $builder->columns([
    		['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'2%',"orderable" => false, "searchable" => false],
    		['data' => 'avatar', 'name' => 'avatar', 'title' => 'Avatar','width'=>'5%',"orderable" => false, "searchable" => false],
    		['data' => 'first_name', 'name' => 'first_name', 'title' => 'Name','width'=>'6%'],
    		['data' => 'email', 'name' => 'email', 'title' => 'Email','width'=>'7%'],
            // ['data' => 'contact_number', 'name' => 'contact_number', 'title' => 'Contact Number','width'=>'5%'],
            // ['data' => 'state_id', 'name' => 'state_id', 'title' => 'State','width'=>'3%'],
            // ['data' => 'city_id', 'name' => 'city_id', 'title' => 'City','width'=>'3%'],
            // ['data' => 'driver_signup_as', 'name' => 'driver_signup_as', 'title' => 'User Type','width'=>'2%'],
    		['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'4%'],
    		['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
    		])
    	->parameters([ 'order' =>[] ]);
    	return view($this->pageLayout.'index',compact('html'));
    }
    /*
    create users
    */
    public function create(){
    	$userRole = Helper::checkPermission(['user-create']);
    	if(!$userRole){
    		$message = "You don't have permission to access this module.";
    		return view('error.permission',compact('message'));
    	}
    	$users=array();
    	return view($this->pageLayout.'create', compact('users'));
    }
    /*
    Edit user page
    */
    public function edit($id){
    	$userRole = Helper::checkPermission(['user-edit']);
    	if(!$userRole){
    		$message = "You don't have permission to access this module.";
    		return view('error.permission',compact('message'));
    	}
    	$states = State::get()->pluck('state','id');
    	$users = User::with('card_details','company_details')->where('id',$id)->first();
    	$cities = City::where('state_id', $users->state_id)->get()->pluck('city','id');

    	if(!empty($users)){
    		return view($this->pageLayout.'edit',compact('users','id','states','cities'));
    	}else{
    		return redirect()->route('admin.index');
    	}
    }

    /*
    Store user details
    */
    public function store(Request $request){
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
    		'first_name'        => 'nullable',
    		'last_name'         => 'nullable',
    		'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
    		'password'          => 'required|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
    		'contact_number'    => 'required|numeric|digits:10|unique:users,contact_number,NULL,id,deleted_at,NULL',
    		'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
    		'state_id'          => 'required',
    		'city_id'           => 'required',
    		'address'           => 'required',
    		'country'           => 'required',
    		'driver_signup_as'  => 'required',
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
    		$first_name=$last_name='';
    		if(@$request->get('first_name') && @$request->get('last_name')){
    			$first_name = @$request->get('first_name');
    			$last_name = @$request->get('last_name');
    		}

    		$userID=User::create([
    			'first_name'        => @$first_name,
    			'last_name'         => @$last_name,
    			'avatar'            => @$filename,
    			'email'             => @$request->get('email'),
    			'password'          => \Hash::make($request->get('password')),
    			'status'            => @$request->get('status'),
    			'user_type'         => 'user',
    			'login_status'      => 'offline',
    			'sign_up_as'        => 'web',
    			'doc_status'        => 'pending',
    			'contact_number'    => @$request->get('contact_number'),
    			'country_code'    	=> @$request->get('country_code'),
    			'country_code_al'   => @$request->get('country_code_al'),
    			'address'           => @$request->get('address'),
    			'city_id'           => @$request->get('city_id'),
    			'state_id'          => @$request->get('state_id'),
    			'country'           => @$request->get('country'),
    			'driver_signup_as'  => @$request->get('driver_signup_as'),
    			]);
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

            if(isset($request->card_number)){
               foreach ($request->card_number as $key => $value) {
                if($request->card_number[$key]){
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
     if($request->get('driver_signup_as') == 'company'){
       $companydetail = CompanyDetail::create([
        'recipient_name'        => @$request->get('recipient_name'),
        'job_title'             => @$request->get('job_title'),
        'company_size'          => @$request->get('company_size'),
        'website'               => @$request->get('website'),
        'company_id'            => @$userID->id,
        ]);
       User::where('id', @$userID->id)->update([
        'company_id'    => @$companydetail->id,
        'company_name'  => @$request->get('company_name'),
        'first_name'    => @$request->get('company_name'),
        'last_name'     => @$request->get('company_name')
        ]);
   }

   Notify::success('User Created Successfully.');
   return redirect()->route('admin.index');
}catch(\Exception $e){
  return back()->with([
   'alert-type'    => 'danger',
   'message'       => $e->getMessage()
   ]);
}
}
    /*
        Update user details
    */
        public function update(Request $request,$id){
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
        		'first_name'        => 'nullable',
        		'last_name'         => 'nullable',
        		'email'             => 'sometimes',
        		'contact_number'    => 'sometimes',
        		'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
        		'state_id'          => 'required',
        		'city_id'           => 'required',
        		'address'           => 'required',
        		'country'           => 'required',
        		'password'          => 'nullable|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/',
        		'driver_signup_as' => 'required',
        		],$customMessages);

        	if($validatedData->fails()){
        		return redirect()->back()->withErrors($validatedData)->withInput();
        	}

        	try{
        		$oldDetails = User::with('card_details','company_details')->where('id', $id)->first();
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
        		if($request->reason_for_inactive && (@$request->get('status') === 'inactive')){
        			$reasoninactive = $request->reason_for_inactive;
        		}else{
        			$reasoninactive = '';
        		}

        		$first_name=$last_name='';
        		if(@$request->get('first_name') && @$request->get('last_name')){
        			$first_name = @$request->get('first_name');
        			$last_name = @$request->get('last_name');
        		}

        		$password = $request->get('password') === null ?
        		$oldDetails->password : \Hash::make($request->get('password'));
        		User::where('id',$id)->update([
        			'first_name'            => @$first_name,
        			'last_name'             => @$last_name,
        			'avatar'                => @$filename,
        			'password'              => @$password,
        			'status'                => @$request->get('status'),
        			'address'               => @$request->get('address'),
        			'city_id'               => @$request->get('city_id'),
        			'state_id'              => @$request->get('state_id'),
        			'country'               => @$request->get('country'),
        			'reason_for_inactive'   => @$reasoninactive,
        			'driver_signup_as'      => @$request->get('driver_signup_as'),
        			'country_code'          => @$request->get('country_code'),
        			'country_code_al'   => @$request->get('country_code_al')
        			]);

        		if(isset($request->card_number)){
        			foreach ($request->card_number as $key => $value) {
        				if($request->card_number[$key]){
        					$card_details['user_id'] = $id;
        					$card_details['card_number'] = $request->card_number[$key];
        					$card_details['card_holder_name'] = $request->card_holder_name[$key];
        					$card_details['card_expiry_month'] = $request->card_expiry_month[$key];
        					$card_details['card_expiry_year'] = $request->card_expiry_year[$key];
        					$card_details['billing_address'] = $request->billing_address[$key];
        					$card_details['bank_name'] = $request->bank_name[$key];
        					$card_details['card_name'] = $request->card_name[$key];
        					$card_details['card_type'] = $request->card_name[$key];
        					$card_details['cvv'] = $request->cvv[$key];
        					CardDetails::create($card_details);
        				}
        			}
        		}

        		if(@$request->get('driver_signup_as') === 'company'){
        			if(!empty($oldDetails->company_details)){
        				CompanyDetail::where('company_id',$id)->update([
        					'recipient_name'        => @$request->get('recipient_name'),
        					'job_title'             => @$request->get('job_title'),
        					'company_size'          => @$request->get('company_size'),
        					'website'               => @$request->get('website'),
        					]);
        				User::where('id', $id)->update([
        					'company_name' => @$request->get('company_name'),
        					'first_name'    => @$request->get('company_name'),
        					'last_name'     => @$request->get('company_name')
        					]);
        			}else{
        				$companydetail = CompanyDetail::create([
        					'recipient_name'        => @$request->get('recipient_name'),
        					'job_title'             => @$request->get('job_title'),
        					'company_size'          => @$request->get('company_size'),
        					'website'               => @$request->get('website'),
        					'company_id'            => @$id
        					]);
        				User::where('id', $id)->update([
        					'company_id' => @$companydetail->id,
        					'company_name' => @$request->get('company_name'),
        					'first_name'    => @$request->get('company_name'),
        					'last_name'     => @$request->get('company_name')
        					]);
        			}
        		}

        		Notify::success('User Updated Successfully.');
        		return redirect()->route('admin.index');
        	} catch(\Exception $e){
        		return back()->with([
        			'alert-type'    => 'danger',
        			'message'       => $e->getMessage()
        			]);
        	}
        }
    /*
    Remove user
    */
    public function delete($id){
    	try{
    		$userRole = Helper::checkPermission(['user-delete']);
    		if(!$userRole){
    			$message = "You don't have permission to access this module.";
    			return view('error.permission',compact('message'));
    		}
    		$checkUser = User::where('id',$id)->first();
    		$checkUser->delete();
    		$deletecarddetails = CardDetails::where('user_id',$id)->get();
    		if(isset($deletecarddetails)){
    			foreach ($deletecarddetails as $key => $value) {
    				$value->delete();
    			}
    		}
    		Notify::success('User deleted successfully.');
    		return response()->json([
    			'status'    => 'success',
    			'title'     => 'Success!!',
    			'message'   => 'User deleted successfully.'
    			]);
    	}catch(\Exception $e){
    		return back()->with([
    			'alert-type'    => 'danger',
    			'message'       => $e->getMessage()
    			]);
    	}
    }
    /*
        Change Status
    */
        public function change_status(Request $request){
        	try{
        		$user = User::where('id',$request->id)->first();
        		if($user === null){
        			return redirect()->back()->with([
        				'status'    => 'warning',
        				'title'     => 'Warning!!',
        				'message'   => 'User not found !!'
        				]);
        		}else{
        			if($user->status == "active"){
        				User::where('id',$request->id)->update([ 'status' => "inactive",]);
        			}
        			if($user->status == "inactive"){
        				User::where('id',$request->id)->update(['status'=> "active"]);
        			}
        		}
        		Notify::success('User status updated successfully !!');
        		return response()->json([
        			'status'    => 'success',
        			'title'     => 'Success!!',
        			'message'   => 'User status updated successfully.'
        			]);
        	}catch (Exception $e){
        		return response()->json([
        			'status'    => 'error',
        			'title'     => 'Error!!',
        			'message'   => $e->getMessage()
        			]);
        	}
        }
    /* -----------------------------------------------------------------------------------------
    @Description: Function for Update profile details
    @input: name,email.
    @Output: update profile details
    -------------------------------------------------------------------------------------------- */
    public function updateProfile()
    {
    	$user = User::where(['status'=>'active','id'=>Auth::user()->id])->first();
    	if(empty($user)){
    		Notify::error('User not found.');
    		return redirect()->to('admin/dashboard');
    	}
    	$total_booking_admin = Booking::where('trip_status','completed')->where('admin_comm_status','pending')->get()->pluck('admin_commision');
    	$total_admin_pr = $total_booking_admin->sum();
    	$total_parcel_admin = ParcelDetail::where('parcel_status','completed')->where('admin_comm_status','pending')->get()->pluck('admin_commision');
    	$total_admin_pr = (float)$total_admin_pr + $total_parcel_admin->sum();

    	return view($this->pageLayout.'updateprofile',compact('user','total_admin_pr'));
    }

    /* -----------------------------------------------------------------------------------------
    @Description: Function for Update profile details
    @input: name,email.
    @Output: update profile details
    -------------------------------------------------------------------------------------------- */

    public function updateProfileDetail(Request $request){
    	$validatedData = $request->validate([
    		'email'         => 'required|unique:users,email,'.Auth::user()->id,
    		'first_name'    => 'required',
    		'last_name'     => 'required',
    		'contact_number'=> 'required|numeric|digits:10',
    		'avatar'        => 'sometimes|mimes:jpeg,jpg,png'
    		]);
    	try{
    		$allowedfileExtension=['pdf','jpg','png'];
    		if($request->hasFile('avatar')){
    			$file = $request->file('avatar');
    			$extension = $file->getClientOriginalExtension();
    			$filename = Str::random(10).'.'.$extension;
    			Storage::disk('public')->putFileAs('avatar', $file,$filename);
    		}else{
    			$userDetail=User::where('id',Auth::user()->id)->first()->avatar;
    			$filename = $userDetail;
    		}
    		User::where('id',Auth::user()->id)->update([
    			'avatar'         => $filename,
    			'email'          => $request->email,
    			'first_name'     => $request->first_name,
    			'last_name'      => $request->last_name,
    			'contact_number' => $request->contact_number,
    			]);
    		Notify::success('Profile updated successfully !!');
    		return redirect()->back();
    	}catch(\Exception $e){
    		Notify::error($e->getMessage());
    	}
    }


    /* -----------------------------------------------------------------------------------------
    @Description: Function for update Password
    @input: old_password,password,password_confirmation.
    @Output: update Password
    -------------------------------------------------------------------------------------------- */
    public function updatePassword(Request $request){
    	try{
    		$validatedData = Validator::make($request->all(),[
    			'old_password'          => 'required',
    			'password'              => 'required|min:6',
    			'password_confirmation' => 'required|min:6',
    			],

    			[
    			'old_password.required'          => 'The current password field is required.',
    			'password.required'              => 'The new password field is required.',
    			'password_confirmation.required' => 'The confirm password field is required.'
    			]
    			);
    		$validatedData->after(function() use($validatedData,$request){
    			if($request->get('password') !== $request->get('password_confirmation')){
    				$validatedData->errors()->add('password_confirmation','The Confirm password does not match.');
    			}
    		});
    		if ($validatedData->fails()) {
    			return redirect()->back()
    			->withErrors($validatedData)
    			->withInput();
    		}
    		if (\Hash::check($request->get('old_password'),auth()->user()->password) === false) {
                // The passwords matches
    			Notify::error('Your current password does not matches with the password you provided. Please try again.');
    			return redirect()->back();
    		}
    		$user = auth()->user();
    		$user->password =\Hash::make($request->get('password'));
    		$user->save();
    		Notify::success('Password updated successfully !');
    		return redirect()->back();
    	}catch(Exception $e){
    		dd($e->getMessage());
    	}
    }

    public function user_trip(Builder $builder, Request $request,$id){
    	$trip = Booking::with('user','driver')->where('user_id',$id)->orderBy('updated_at','desc');
    	if (request()->ajax()) {
    		return DataTables::of($trip->get())
    		->addIndexColumn()
    		->editColumn('action', function (Booking $trip) {
    			return '<a title="View" class="btn btn-info btn-sm ml-1 showtrip" data-id ="'.$trip->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
    		})
    		->editColumn('user_name', function (Booking $trip) {
    			return @$trip->user->first_name.' '.@$trip->user->last_name;
    		})
    		->editColumn('ride_type_date', function (Booking $booking) {
                    //return "$ ".$booking->total_amount."";

    			if($booking->trip_status !== 'completed' && $booking->trip_status !== 'cancelled' && (Carbon::parse($booking->booking_date)->greaterThan(Carbon::now()->format('Y-m-d')) )){
    				return "Upcoming Ride";
    			}else if(($booking->trip_status == 'completed') || ($booking->trip_status == 'cancelled') && (Carbon::parse($booking->booking_date)->lessThan(Carbon::now()->format('Y-m-d')))){
    				return "Past Ride";
    			}else if($booking->trip_status == 'pending'){
    				return "Ride Requested (Driver Not Found)";
    			}else if($booking->trip_status == 'accepted'){
    				return "Ride Accepted";
    			} else{
    				return 'Ride Requested';
    			}

    		})
    		->editColumn('driver_name', function (Booking $trip) {
    			return @$trip->driver->first_name.' '.@$trip->driver->last_name;
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
    	->parameters([
    		'order' =>[]
    		]);
    	return view($this->pageLayout.'user_trip_details',compact('html'));
    }

    public function u_trip_info($id){
    	$tripDetails = Booking::with('driver','user','ride_type_id')->where('id',$id)->first();
    	return response()->json([
    		'driver'       => @$tripDetails['driver']->first_name.' '.@$tripDetails['driver']->last_name,
    		'user'       => @$tripDetails['user']->first_name.' '.@$tripDetails['user']->last_name,
    		'pick_up_location'       => $tripDetails->pick_up_location,
    		'drop_location'       => $tripDetails->drop_location,
    		'start_time'       => $tripDetails->start_time,
    		'end_time' => $tripDetails->end_time,
    		'hold_time' => $tripDetails->hold_time,
    		'base_fare' => $tripDetails->base_fare,
    		'total_km' => $tripDetails->total_km,
    		'admin_commision' => $tripDetails->admin_commision,
    		'transaction_id'  =>$tripDetails->transaction_id,
    		'trip_status'  => ucfirst(str_replace("_"," ",$tripDetails->trip_status)),
    		'extra_notes'  =>$tripDetails->extra_notes,
    		'promo_name'  =>$tripDetails->promo_name,
    		'promo_amount'  =>$tripDetails->promo_amount,
    		'promo_id'  =>$tripDetails->promo_id,
    		'total_amount'  => $tripDetails->total_amount,
    		'trip_type_status' => @$tripDetails['ride_type_id']->name,
    		'booking_date'  => date("m-d-Y", strtotime($tripDetails->booking_date))
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

    public function user_parcel_booking(Builder $builder, Request $request,$id){
    	$parcel = ParcelDetail::with('user','driver')->where('user_id',$id)->orderBy('updated_at','desc');

    	if (request()->ajax()) {
    		return DataTables::of($parcel->get())
    		->addIndexColumn()
    		->editColumn('action', function (ParcelDetail $parcel) {
    			return '<a title="View" class="btn btn-info btn-sm ml-1 showparceldetail" data-id ="'.$parcel->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
    		})
    		->editColumn('user_name', function (ParcelDetail $parcel) {
    			return @$parcel->user->first_name.' '.@$parcel->user->last_name;
    		})
    		->editColumn('total_amount', function (ParcelDetail $parcel) {
    			return "$".$parcel->total_amount."";
    		})
    		->editColumn('driver_name', function (ParcelDetail $parcel) {
    			return @$parcel->driver->first_name.' '.@$parcel->driver->last_name;
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
    		['data' => 'total_distance', 'name' => 'total_distance', 'title' => 'Total Distance','width'=>'5%'],
    		['data' => 'total_amount', 'name' => 'total_amount', 'title' => 'Total Amount','width'=>'5%'],
    		['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'5%'],

    		])
    	->parameters([
    		'order' =>[]
    		]);
    	return view($this->pageLayout.'parcel_details',compact('html'));
    }

    public function u_booking_info($id){
    	$tripDetails = ParcelDetail::with('driver','user')->where('id',$id)->first();
    	return response()->json([
    		'driver'       => @$tripDetails['driver']->first_name.' '.@$tripDetails['driver']->last_name,
    		'user'       => @$tripDetails['user']->first_name.' '.@$tripDetails['user']->last_name,
    		'pick_up_location'       => $tripDetails->pick_up_location,
    		'drop_location'       => $tripDetails->drop_location,
    		'start_time'       => $tripDetails->booking_start_time,
    		'end_time' => $tripDetails->booking_end_time,
    		'booking_date' => $tripDetails->booking_date,
    		'total_km' => $tripDetails->total_distance,
    		'parcel_status'  => ucfirst(str_replace("_"," ",$tripDetails->parcel_status)),
    		'extra_notes'  =>$tripDetails->extra_notes,
    		'total_amount'  => $tripDetails->total_amount
    		]);
    }

    public function contacts(Builder $builder, Request $request){
    	$contacts_list = User::where('user_type','!=','superadmin')->orderBy('id','desc')->get();
    	return view($this->contactsLayout.'index',compact('contacts_list'));
    }

    public function sendAdminCommission(Request $request){
    	try{
    		$total_booking_complete = Booking::where('trip_status','completed')->where('admin_comm_status','pending')->select('admin_commision','id')->get();
    		$total_parcel_complete = ParcelDetail::where('parcel_status','completed')->where('admin_comm_status','pending')->select('admin_commision','id')->get();
    		$booking_ids = Arr::pluck($total_booking_complete, 'id');
    		$parcel_ids = Arr::pluck($total_parcel_complete, 'id');
    		Booking::whereIn('id', $booking_ids)->update(['admin_comm_status'=>'transferred']);
    		ParcelDetail::whereIn('id', $parcel_ids)->update(['admin_comm_status'=>'transferred']);

    		$checkadminwallet = Wallet::where('user_id', Auth::User()->id)->first();
    		if($request->admin_amount){
    			if(!empty($checkadminwallet)){
    				$final_amount = (float)$request->admin_amount + (float)$checkadminwallet->amount;
    				Wallet::where('user_id', Auth::User()->id)->update([ 'amount'=>$final_amount ]);
    			}else{
    				Wallet::create(['user_id'=> Auth::User()->id, 'amount'=>(float)$request->admin_amount ]);
    			}
    		}else{
    			Notify::error('Admin Commision not found !!');
    			return redirect()->route('admin.profile');
    		}


    		return redirect()->route('admin.profile');
    	}catch(\Exception $e){
    		return back()->with([
    			'alert-type'    => 'danger',
    			'message'       => $e->getMessage()
    			]);
    	}
    }

}
