<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use DataTables,Notify,Validator,Str,Storage;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Html\Builder;
use Auth;
use App\Models\User;
use App\Models\CardDetails;
use Event;
use Illuminate\Http\Request;
use Settings;

class SuperAdminController extends Controller
{
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
        $this->pageLayout = 'admin.pages.admin.';
        $this->middleware('auth');
    }
    public function index(Builder $builder, Request $request)
    {
        $users = User::where('user_type','superadmin')->where('id','!=',auth()->user()->id)->orderBy('id','desc');

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
                
                $action .= '<a title="Edit" class="ml-2 mr-2" href='.route('admin.sadmin.edit',[$users->id]).'><i class="fa fa-pencil"></i></a>';
                $action .='<a title="Delete" class="ml-2 mr-2 deleteuser" data-id ="'.$users->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                return $action;
            })
            ->rawColumns(['action','image','status','user_type'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'image', 'name'    => 'avatar', 'title' => 'Avatar','width'=>'10%',"orderable" => false, "searchable" => false],
            ['data' => 'username', 'name'    => 'username', 'title' => 'User Name','width'=>'15%'],
            ['data' => 'email', 'name' => 'email', 'title' => 'Email','width'=>'15%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'5%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
            ])
        ->parameters([ 'order' =>[]   ]);

        $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
        \View::share('roles',$roles);

        return view($this->pageLayout.'index',compact('html'));
    }
    /*
        create users
    */ 
        public function create(){
            $user_role = 0;
            $users = User::where('user_type','driver')->get();
            return view($this->pageLayout.'create',compact('user_role'));
        }
    /*
        Edit user page
    */
        public function edit($id){


            $users = User::with('card_details')->where('id',$id)->first();
            if(!empty($users)){
                return view($this->pageLayout.'edit',compact('users','id'));
            }else{
                return redirect()->route('admin.index');
            }


        }

    /*
        Store user details
    */
        public function store(Request $request){
            $validatedData = Validator::make($request->all(),[
                'first_name'        => 'required',
                'last_name'         => 'required',
                'username'          => 'required|unique:users,username,NULL,id,deleted_at,NULL',
                'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
                'password'          => 'required|min:6',
                'gender'            => 'required',
                'contact_number'    => 'required|numeric|digits:10',
                'avatar'            => 'sometimes|mimes:jpeg,jpg,png'
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
                }
                
                $userID=User::create([
                    'first_name'        => @$request->get('first_name'),
                    'last_name'         => @$request->get('last_name'),
                    'username'          => @$request->get('username'), 
                    'avatar'            => @$filename,
                    'email'             => @$request->get('email'), 
                    'password'          => \Hash::make($request->get('password')),
                    'status'            => @$request->get('status'), 
                    'user_type'         => 'superadmin',
                    'gender'            => @$request->get('gender'),
                    'login_status'      => 'offline',
                    'sign_up_as'        => 'web',
                    'doc_status'        => 'pending',
                    'contact_number'    => @$request->get('contact_number')
                    ]);
                $userID->uuid = $userID->id;
                $userID->save();

                $user_carddetails = CardDetails::create([

                    'user_id'        => @$userID->id,
                    'card_number'         => @$request->get('card_number'),
                    'card_holder_name'          => @$request->get('card_holder_name'), 
                    'card_expiry_month'            => @$request->get('card_expiry_month'),
                    'card_expiry_year'             => @$request->get('card_expiry_year'), 
                    'billing_address'             => @$request->get('billing_address'), 
                    'bank_name'             => @$request->get('bank_name')
                    ]); 


                Notify::success('Admin Created Successfully.');

                return redirect()->route('admin.sadmin.index');
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

        $validatedData = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'         => 'required',
            'username'          => 'required|unique:users,username,'.$id.',id,deleted_at,NULL',
            'email'             => 'required|email|unique:users,email,'.$id.',id,deleted_at,NULL',
            'gender'            => 'required',
            'contact_number'    => 'required|numeric|digits:10',
            'avatar'            => 'sometimes|mimes:jpeg,jpg,png'
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
                }
            }
            $password = $request->get('password') === null ? 
            
            $oldDetails->password : \Hash::make($request->get('password'));
            User::where('id',$id)->update([
                'first_name'        => @$request->get('first_name'),
                'last_name'         => @$request->get('last_name'),
                'username'          => @$request->get('username'), 
                'avatar'            => @$filename,
                'email'             => @$request->get('email'), 
                'password'          => @$password,  
                'status'            => @$request->get('status'),
                'gender'            => @$request->get('gender'),
                'contact_number'    => @$request->get('contact_number')
                ]);

            CardDetails::where('user_id',$id)->update([
                'card_number'         => @$request->get('card_number'),
                'card_holder_name'          => @$request->get('card_holder_name'), 
                'card_expiry_month'            => @$request->get('card_expiry_month'),
                'card_expiry_year'             => @$request->get('card_expiry_year'), 
                'billing_address'             => @$request->get('billing_address'), 
                'bank_name'             => @$request->get('bank_name')
                ]); 
            

            Notify::success('Admin Updated Successfully.');
            return redirect()->route('admin.sadmin.index');
        }catch(\Exception $e){
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
            $checkUser = User::where('id',$id)->first();
            $checkUser->delete();

            $deletecarddetails = CardDetails::where('user_id',$id)->first();
            if(isset($deletecarddetails)){
                $deletecarddetails->delete();
            }
            Notify::success('Admin deleted successfully.');
            return response()->json([
               'status'    => 'success',
               'title'     => 'Success!!',
               'message'   => 'Admin deleted successfully.'
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
                            'message'   => 'Admin not found !!'
                            ]);
                    }else{

                       if($user->status == "active"){

                           User::where('id',$request->id)->update([
                            'status' => "inactive",
                            ]);
                       }
                       if($user->status == "inactive"){

                           User::where('id',$request->id)->update([
                             'status'=> "active",
                             ]);
                       }

                   }

                   Notify::success('Admin status updated successfully !!');

                   return response()->json([
                    'status'    => 'success',
                    'title'     => 'Success!!',
                    'message'   => 'Admin status updated successfully.'
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