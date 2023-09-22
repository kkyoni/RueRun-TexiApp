<?php

namespace App\Http\Controllers\Admin;

use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DataTables,Validator,Str,Storage;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use Auth;
use Event;
use Settings;
use App\Models\User;
use App\Models\CardDetails;
use App\Models\State;
use App\Models\City;


class SubAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $authLayout = '';
    protected $pageLayout = '';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.subadmin.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $users = User::where('user_type','sub_admin')->where('id','!=',auth()->user()->id)->orderBy('id','desc');
        if (request()->ajax()) {
            return DataTables::of($users->get())
            ->addIndexColumn()
            ->editColumn('image', function (User $users) {
                if($users->avatar != ""){
                    return "<img src=".url("storage/avatar/".$users->avatar)."  width='60px'/> ";
                }else{
                    return "<img src=".url("storage/avatar/default.png")."  width='60px'/> ";
              }
            })
            ->editColumn('status', function (User $operator) {
                $s="";
                if($operator->status == "active"){
                    $s .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-icon btn-success changeStatusRecord" data-id="'.$operator->id.'">Active</a>';
                }else{
                    $s .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="InActive" class="btn btn-sm btn-icon btn-danger changeStatusRecord" data-id="'.$operator->id.'">Inactive</a>';
                }
                return $s;
            })
            ->editColumn('action', function (User $users) {
                $action = '';
                $action .= '<a title="Edit" class="ml-2 mr-2" href='.route('admin.subadmin.edit',[$users->id]).'><i class="fa fa-pencil"></i></a>';
                $action .='<a title="Delete" class="ml-2 mr-2 deleteuser" data-id ="'.$users->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                return $action;
            })
            ->editColumn('first_name', function (User $users) {
                return $users->first_name.' '.$users->last_name;
            })
            ->rawColumns(['action','image','status','user_type'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'1%',"orderable" => false, "searchable" => false],
            ['data' => 'image', 'name'    => 'avatar', 'title' => 'Avatar','width'=>'4%',"orderable" => false, "searchable" => false],
            ['data' => 'first_name', 'name'    => 'first_name', 'title' => 'User Name','width'=>'6%'],
            ['data' => 'email', 'name' => 'email', 'title' => 'Email','width'=>'6%'],
            ['data' => 'contact_number', 'name' => 'contact_number', 'title' => 'Contact Number','width'=>'5%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'5%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'10%',"orderable" => false, "searchable" => false],
            ])
        ->parameters([ 'order' =>[]   ]);
        return view($this->pageLayout.'index',compact('html'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $states = State::get()->pluck('state','id');
        $cities = City::get();
        return view($this->pageLayout.'create', compact('states','cities'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password'          => 'required|min:6',
            'contact_number'    => 'required|numeric|digits:10',
            'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
            'state_id'          => 'required',
            'city_id'           => 'required',
            'address'           => 'required',
            'country'           => 'required',
            ]);
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
                'first_name'        => @$request->get('first_name'),
                'last_name'         => @$request->get('last_name'),
                'avatar'            => @$filename,
                'email'             => @$request->get('email'),
                'password'          => \Hash::make($request->get('password')),
                'status'            => @$request->get('status'),
                'user_type'         => 'sub_admin',
                'login_status'      => 'offline',
                'sign_up_as'        => 'web',
                'doc_status'        => 'pending',
                'contact_number'    => @$request->get('contact_number'),
                'address'           => @$request->get('address'),
                'city_id'           => @$request->get('city_id'),
                'state_id'          => @$request->get('state_id'),
                'country'           => @$request->get('country'),
                ]);
            Notify::success('Sub-Admin Created Successfully.');

            return redirect()->route('admin.subadmin.index');
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
    public function edit($id) {
        $states = State::get()->pluck('state','id');
        $users = User::with('card_details')->where('id',$id)->first();
        $cities = City::where('state_id', $users->state_id)->get();
        if(!empty($users)){
            return view($this->pageLayout.'edit',compact('users','id','states','cities'));
        }else{
            return redirect()->route('admin.subadmin.index');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        $validatedData = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required',
            'contact_number'    => 'required|numeric|digits:10',
            'avatar'            => 'sometimes|mimes:jpeg,jpg,png',
            'state_id'          => 'required',
            'city_id'           => 'required',
            'address'           => 'required',
            'country'           => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $oldDetails = User::find($id);
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

            if($request->get('password')){
                $password = \Hash::make($request->get('password'));
            }else{
                $password = $oldDetails->password;
            }

            User::where('id',$id)->update([
                'first_name'        => @$request->get('first_name'),
                'last_name'         => @$request->get('last_name'),
                'avatar'            => @$filename,
                'email'             => @$request->get('email'),
                'password'          => @$password,
                'status'            => @$request->get('status'),
                'contact_number'    => @$request->get('contact_number'),
                'address'           => @$request->get('address'),
                'city_id'           => @$request->get('city_id'),
                'state_id'          => @$request->get('state_id'),
                'country'           => @$request->get('country'),
            ]);
            Notify::success('SubAdmin Updated Successfully.');
            return redirect()->route('admin.subadmin.index');
        }catch(\Exception $e){
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
    public function delete($id){
        try{
            $checkUser = User::where('id',$id)->first();
            $checkUser->delete();

            $deletecarddetails = CardDetails::where('user_id',$id)->first();
            if(isset($deletecarddetails)){
                $deletecarddetails->delete();
            }
            Notify::success('SubAdmin deleted successfully.');
            return response()->json([
               'status'    => 'success',
               'title'     => 'Success!!',
               'message'   => 'SubAdmin deleted successfully.'
            ]);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
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
            Notify::success('Status updated successfully !!');
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
}
