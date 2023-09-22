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
use App\Models\Support;
use App\Models\SupportComment;
use Event;
use Illuminate\Http\Request;
use Settings;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class SupportController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.supports.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['support-list','support-edit']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $permission_data['hasUpdatePermission'] = Helper::checkPermission(['support-edit']);

        $support = Support::with(['userDetail','supportCategoryDetail','supportComment'])->orderBy('updated_at','desc');
        if (request()->ajax()) {
            return DataTables::of($support->get())
            ->addIndexColumn()
            ->editColumn('image', function (Support $support) {
                if(!empty($support->userDetail)){
                    if (file_exists( 'storage/avatar/'.$support->userDetail->avatar)) {
                        return "<img src=".url("storage/avatar/".$support->userDetail->avatar)."  width='60px'/>";
                    }else{
                        return "<img src=".url("storage/avatar/default.png")."  width='60px'/> ";
                    }
                }else{
                    return "<img src=".url("storage/avatar/default.png")."  width='60px'/> ";
                }
            })
            ->editColumn('userDetail.username', function (Support $support) {
                if(!empty($support->userDetail)){
                    if($support->userDetail->first_name){
                        return @$support->userDetail->first_name.' '.@$support->userDetail->last_name;
                    }else if($support->userDetail->company_name){
                        return @$support->userDetail->company_name;
                    }
                }elseif(!empty($support->driverDetail)){
                    if($support->driverDetail->first_name){
                        return @$support->driverDetail->first_name.' '.@$support->driverDetail->last_name;
                    }else if($support->driverDetail->company_name){
                        return @$support->driverDetail->company_name;
                    }
                } else{
                    return '';
                }
            })
            ->editColumn('action', function(Support $support) use($permission_data) {
                if($permission_data['hasUpdatePermission']){
                    $hasOneComment = SupportComment::where('read','1')->where('user_id',$support->user_id)->count();
                    if($hasOneComment !== null){
                        return '<a title="Edit" class="btn btn-warning btn-sm open_modal ml-1" data-id ="'.$support->user_id.'" href="javascript:void(0)"><i class="fa fa-pencil"></i></a>('.$hasOneComment.')';
                    }
                    return '<a title="Edit" class="m-l-10 open_modal ml-1" data-id ="'.$support->user_id.'" href="javascript:void(0)"><i class="fa fa-pencil"></i></a>('.$hasOneComment.')';
                }
            })
            ->editColumn('userDetail.user_type', function (Support $support) {
                if(!empty($support->userDetail)){
                    return @$support->userDetail->user_type;
                }elseif(!empty($support->driverDetail)){
                    return @$support->driverDetail->user_type;
                }  else{
                    return '';
                }
            })
            ->editColumn('status', function (Support $support) {
                $html = '<select class="form-control changeDocStatus" id="changeDocStatus" data-id='.$support->id.'>';
                if($support->status=='pending') {
                    $html .='<option value="pending" selected>Pending</oprion>';
                }else{
                    $html .='<option value="pending">Pending</oprion>';
                }if($support->status=='onhold') {
                    $html .='<option value="onhold" selected>On Hold</oprion>';
                }else{
                    $html .='<option value="onhold">On Hold</oprion>';
                }if($support->status=='resolved') {
                    $html .='<option value="resolved" selected>Resolved</oprion>';
                }else{
                    $html .='<option value="resolved">Resolved</oprion>';
                }
                $html .= '</select>';
                return $html;
            })
            ->editColumn('admin_comment', function (Support $support) {
                return  strlen(@$support->admin_comment) > 50 ? substr(@$support->admin_comment,0,50)."..." : @$support->admin_comment;
            })
            ->editColumn('supportCategoryDetail.cat_name', function (Support $support) {
                return @$support->supportCategoryDetail->name;
            })
            ->rawColumns(['action','image','status','userDetail.user_type','admin_comment'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'image', 'name'    => 'avatar', 'title' => 'Avatar','width'=>'10%',"orderable" => false, "searchable" => false],
            ['data' => 'userDetail.username', 'name'    => 'userDetail.username', 'title' => 'User Name','width'=>'10%'],
            ['data' => 'userDetail.user_type', 'name'    => 'userDetail.user_type', 'title' => 'User Type','width'=>'15%'],
            ['data' => 'supportCategoryDetail.cat_name', 'name'    => 'supportCategoryDetail.cat_name', 'title' => 'Category Type','width'=>'10%'],
            ['data' => 'description', 'name' => 'description', 'title' => 'Description','width'=>'15%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'15%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Admin Add Comment ','width'=>'5%',"orderable" => false, "searchable" => false],
            ])
        ->parameters(['order' =>[]]);
        $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
        \View::share('roles',$roles);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        return view($this->pageLayout.'create');
    }

    public function edit($id){
        $userRole = Helper::checkPermission(['support-edit']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $users = User::find($id);
        if(!empty($users)){
            return view($this->pageLayout.'edit',compact('users','id'));
        }else{
            return redirect()->route('admin.index');
        }
    }

    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password'          => 'required|min:6',
            'gender'            => 'required',
            'contact_number'    => 'required|numeric|digits:10'
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
                'avatar'            => @$filename,
                'email'             => @$request->get('email'),
                'password'          => \Hash::make($request->get('password')),
                'status'            => @$request->get('status'),
                'user_type'         => 'user',
                'gender'            => @$request->get('gender'),
                'login_status'      => 'offline',
                'sign_up_as'        => 'web',
                'doc_status'        => 'pending',
                'contact_number'    => @$request->get('contact_number')
                ]);
            $userID->uuid = $userID->id;
            $userID->save();
            Notify::success('User Created Successfully.');
            return redirect()->route('admin.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function update(Request $request,$id){
        $validatedData = Validator::make($request->all(),[
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique:users,email,'.$id.',id,deleted_at,NULL',
            'gender'            => 'required',
            'contact_number'    => 'required|numeric|digits:10'
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
                'avatar'            => @$filename,
                'email'             => @$request->get('email'),
                'password'          => @$password,
                'status'            => @$request->get('status'),
                'user_type'         => 'user',
                'gender'            => @$request->get('gender'),
                'contact_number'    => @$request->get('contact_number')
                ]);
            Notify::success('User Updated Successfully.');
            return redirect()->route('admin.index');
        } catch(\Exception $e){
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

    public function change_status(Request $request){
        try{
            $support = Support::where('id',$request->id)->first();
            if($support === null){
                return redirect()->back()->with([
                    'status'    => 'warning',
                    'title'     => 'Warning!!',
                    'message'   => 'Support not found !!'
                    ]);
            }else{
                Support::where('id',$request->id)->update([
                    'status' => $request->status,
                    ]);
            }
            Notify::success('Support status updated successfully !!');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Support status updated successfully.'
                ]);
        }catch (Exception $e){
            return response()->json([
                'status'    => 'error',
                'title'     => 'Error!!',
                'message'   => $e->getMessage()
                ]);
        }
    }

    public function add_comment_admin(Request $request){
        try{
            $support = Support::where('user_id',$request->id)->first();
            if($support === null){
                return redirect()->back()->with([
                    'status'    => 'warning',
                    'title'     => 'Warning!!',
                    'message'   => 'Support not found !!'
                    ]);
            }else{
                $userID=SupportComment::create([
                    'admin_id'        => Auth::user()->id,
                    'user_id'         => @$request->get('id'),
                    'sender_id'       => Auth::user()->id,
                    'receiver_id'     => @$request->get('id'),
                    'comment'         => @$request->get('add_comment_admin')
                    ]);
            }
            Notify::success('Comment add successfully.');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Comment add successfully.'
                ]);
        }catch (Exception $e){
            Notify::error('Comment not add successfully.');
            return response()->json([
                'status'    => 'error',
                'title'     => 'Error!!',
                'message'   => $e->getMessage()
                ]);
        }
    }

    public function add_comment_info($id){
        $update_view = SupportComment::where('user_id',$id)->where('read','1')->get();
        foreach ($update_view as $view) {
            $view->read = '0';
            $view->save();
        }
        $get_comment_data = SupportComment::with('userDetail')->where('sender_id',$id)->orWhere('receiver_id',$id)->get();
        $user_id =Auth::user()->id;
        $view = \View::make('admin.pages.supports.chat',compact('get_comment_data','user_id'))->render();
        return response()->json(['data'=>$view,'status' => 'success','message' =>"success"],200);
    }
}