<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\RoleHasPermissions;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Notify,Validator,Str,Storage,Auth;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Event;
use App\Models\State;
use App\Models\City;


class RoleController extends Controller{
   public function __construct(){
       $this->authLayout = 'admin.auth.';
       $this->pageLayout = 'admin.pages.';
       $this->middleware('auth');
   }
    /* -----------------------------------------------------------------------------------------
    @Description: Function for index
    @input:
    @Output: Details data view
    -------------------------------------------------------------------------------------------- */

     public function index(Builder $builder){
    // role ane permission check
        $userRole = '';
        $userRole = Helper::checkPermission(['role-list','role-edit']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $permission_data['hasUpdatePermission'] = Helper::checkPermission(['role-edit']);

        // role ane permission check
        $users = User::with('role')
        ->whereHas('role',function ($q){
            return $q->where('name','!=','user')->where('name','!=','admin')->where('name','!=','driver')->where('name','!=','company');
        })
        ->orderBy('id','desc')->get();
       // dd($users);
        if (request()->ajax()) {
            return DataTables::of($users)
            ->addIndexColumn()
            ->editColumn('status', function (User $roles) {
                if ($roles->status == "active") {
                    return '<span class="label label-success">Active</span>';
                } else {
                    return '<span class="label label-danger">Block</span>';
                }
            })
            ->editColumn('encrypt_password', function (User $roles) {
                return $roles->encrypt_password;
            })
            ->editColumn('action', function (User $roles) use($permission_data) {
                $action = '';
                if($permission_data['hasUpdatePermission']){
                    $action .='<a href='.route('admin.role.edit',[$roles->id]).' class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i></a>&nbsp;&nbsp;';
                }
                $action .='<a class="btn btn-info btn-sm" href='.route('admin.role.view',[$roles->id]).'><i class="fa fa-eye"></i></a>';
                return $action;
            })
            ->rawColumns(['action','status','encrypt_password'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%'],
            ['data' => 'first_name', 'name' => 'first_name', 'title' => 'Sub Admin Name','width'=>'15%'],
            ['data' => 'email', 'name' => 'email', 'title' => 'Email','width'=>'20%'],
            ['data' => 'encrypt_password', 'name' => 'encrypt_password', 'title' => 'Password','width'=>'20%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'10%'],
            ['data' => 'action', 'name' => 'Action', 'title' => 'Action', "orderable"=> false, "searchable"=> false,'width'=>'10%'],
            ])->parameters(['order' =>[] ]);
        return view($this->pageLayout.'role.index', compact('html'));
    }

    /* -----------------------------------------------------------------------------------------
    @Description: Function for create page view
    @input: country
    @Output: create page view
    -------------------------------------------------------------------------------------------- */
    public function create(){
    // if(!auth()->guard('web')->user()->hasPermissionTo('role-create')){
    //     $message = 'You have not permission to create User.';
    //     return view('error.permission',compact('message'));
    // }
    // return view($this->pageLayout.'role.bo_level');
    // $permission = Permission::get();
    // return view($this->pageLayout.'role.add_sub_user',compact('permission'));
         $states = State::get()->pluck('state','id');
        $cities = City::get();
    return view($this->pageLayout.'subadmin.create',compact('states','cities'));
    }

    /* -----------------------------------------------------------------------------------------
    @Description: Function for store country data
    @input: country
    @Output: store country data
    -------------------------------------------------------------------------------------------- */
    // public function store(Request $request){
    //     // $this->validate($request,[
    //     //     'name' => 'required|unique:bo_level|max:20',
    //     //     'email' => 'required|email|unique:bo_level,email',
    //     //     'password' => 'required|min:6|max:16',
    //     //     'status' => 'required',
    //     // ]);
    //     // dd($request->name);
    //     $bo_level = new User();
    //     $bo_level->first_name = $request->name;
    //     $bo_level->last_name = $request->name;
    //     $bo_level->email = $request->email;
    //     $bo_level->password = \Hash::make($request->password);
    //     $bo_level->encrypt_password = $request->password;
    //     $bo_level->status = $request->status;
    //     $bo_level->save();
    //     $role = Role::create(['name'=>$bo_level->first_name]);
    //     $role->save();
    //     $bo_level1 = User::find($bo_level->id);
    //     $bo_level1->role_id = $role->id;
    //     // dd($bo_level1,$bo_level);
    //     $bo_level1->save();
    //     // $permission = Permission::defaultPermissions();
    //     // $role->syncPermissions($permission);
    //     // $role = Role::create(['name' => $request->input('name')]);
    //     // $role->syncPermissions($request->input('permission'));
    //     Notify::success('Role added successfully.');
    //     return redirect()->route('admin.role.index');
    // }
     public function store(Request $request){
          $this->validate($request,[
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|min:6|max:16',
            'status' => 'required',
            'address' => 'required',
            'city_id' => 'required',
            'country' => 'required',
            'state_id' => 'required',
            'contact_number' => 'required',
        ]);
       
       
        $user=User::create([
                'first_name'        => @$request->get('first_name'),
                'last_name'         => @$request->get('last_name'),
               
                'email'             => @$request->get('email'),
                'password'          => \Hash::make($request->get('password')),
                'encrypt_password'  => $request->get('password'),
                'status'            => @$request->get('status'),
                'user_type'         => 'bo_user',
                'login_status'      => 'offline',
                'sign_up_as'        => 'web',
                'doc_status'        => 'pending',
                'contact_number'    => @$request->get('contact_number'),
                'address'           => @$request->get('address'),
                'city_id'           => @$request->get('city_id'),
                'state_id'          => @$request->get('state_id'),
                'country'           => @$request->get('country'),
                'role_id'           => 3,
                ]);
       
        $user->assignRole($request->input('roles'));
        Notify::success('Role added successfully.');
        return redirect()->route('admin.role.index');
    }
    /* -----------------------------------------------------------------------------------------
    @Description: Function for destroy
    @input: id
    @Output: delete counrty
    -------------------------------------------------------------------------------------------- */
    public function view(Request $request,$id){
        $bol = User::where('id','=',$id)->first();
        if (!$bol){
            $message = 'You have not correct your User Id.';
            return view('error.permission',compact('message'));
        }
        $role = RoleHasPermissions::with('permission')->where('role_id','=',$bol->role_id)->get();
        $user_permission=[];
        foreach ($role as $key=>$value){
            foreach ($value->permission as $perm){
                $user_permission[]=$perm;
            }
        }
        $permissions = Permission::all();
        $permission = [];
        foreach ($permissions as $key => $value) {
            $permission[$value->module_name][] = $value;
        }
        $heading =  'Add Role';
        $bank_office_level = User::whereIn('role_id',['3','4','5'])->get();
        $bank_office_level_key=[];
        foreach ($bank_office_level as $key=>$value){
            $bank_office_level_key[$value->id]=$value->first_name;
        }
        $bank_office_level=$bank_office_level_key;
        return view('admin.pages.role.view',compact('permission','user_permission','bol','bank_office_level'));
    }

    /* -----------------------------------------------------------------------------------------
    @Description: Function for edit
    @input: country
    @Output: edit data view
    -------------------------------------------------------------------------------------------- */
    public function edit($id){
    // role ane permission check
        $userRole = '';
        $userRole = Helper::checkPermission(['role-edit']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $bol = User::where('id','=',$id)->first();
        // dd($bol->first_name);
        if (!$bol){
            $message = 'You have not correct your User Id.';
            return view('error.permission',compact('message'));
        }
        $role = RoleHasPermissions::with('permission')->where('role_id','=',$bol->role_id)->get();
        $user_permission=[];
        foreach ($role as $key=>$value){
            foreach ($value->permission as $perm){
                $user_permission[]=$perm;
            }
        }
        $permissions = Permission::all();
        $permission = [];
        foreach ($permissions as $key => $value) {
            $permission[$value->module_name][] = $value;
        }
        $heading =  'Update Role';
        $bank_office_level = User::whereIn('role_id',['3','4','5'])->get();
        $bank_office_level_key=[];
        foreach ($bank_office_level as $key=>$value){
            $bank_office_level_key[$value->id]=$value->first_name;
        }
        $bank_office_level=$bank_office_level_key;
        return view('admin.pages.role.create',compact('permission','user_permission','bol','bank_office_level'));
    }

    /* -----------------------------------------------------------------------------------------
    @Description: Function for update Role
    @input: id,name
    @Output: update Role in database
    -------------------------------------------------------------------------------------------- */
    public function update(Request $request){
        $validatedData = $request->validate([
            'id' => 'required',
            'permission' => 'required',
        ]);
        $role = Role::find($request->id);
        $role->syncPermissions($request->permission);
        Notify::success('Role updated successfully.');
        return redirect()->route('admin.role.index');
    }

    /* -----------------------------------------------------------------------------------------
    @Description: Function for delete Role
    @input: id
    @Output: remove Role from database
    -------------------------------------------------------------------------------------------- */
    public function destroy($id){
        $role = Role::where('id',$id)->first();
        $role->delete();
        return ["status"=>'success',"message"=>'Record deleted sucessfully'];
        return redirect()->to('admin/role');
    }
    /* -----------------------------------------------------------------------------------------
    @Description: Function for destroy
    @input: id
    @Output: delete counrty
    -------------------------------------------------------------------------------------------- */
    public function addRole(Request $request){
        $role = Role::find($request->role_id);
        $role->syncPermissions($request->permission);
        Notify::success('Role updated successfully.');
        return redirect()->route('admin.role.index');
    }
    /* -----------------------------------------------------------------------------------------
    @Description: Function for destroy
    @input: id
    @Output: delete counrty
    -------------------------------------------------------------------------------------------- */
    public function getPermissionDetail(Request $request){
        $bol = User::where('id','=',$request->id)->first();
        if (!$bol){
            $message = 'You have not correct your User Id.';
            return view('error.permission',compact('message'));
        }
        $role = RoleHasPermissions::with('permission')->where('role_id','=',$bol->role_id)->get();
        $user_permission=[];
        foreach ($role as $key=>$value){
            foreach ($value->permission as $perm){
                $user_permission[]=$perm;
            }
        }
        $permissions = Permission::all();
        $permission = [];
        foreach ($permissions as $key => $value) {
            $permission[$value->module_name][] = $value;
        }
        $heading =  'Add Role';
        $bank_office_level = User::whereIn('role_id',['3','4','5'])->get();
        $bank_office_level_key=[];
        foreach ($bank_office_level as $key=>$value){
            $bank_office_level_key[$value->id]=$value->name;
        }
        $bank_office_level=$bank_office_level_key;
        return response()->json([
            'permission'=>$permission,
            'user_permission'=>$user_permission,
            'bol'=>$bol,
            'bank_office_level'=>$bank_office_level
            ]);
        return view('admin.pages.role.view',compact('permission','user_permission','bol','bank_office_level'));
    }
}