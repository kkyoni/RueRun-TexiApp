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
use Event;
use Illuminate\Http\Request;
use Settings;
use Mail;
use App\Jobs\SendEmailTest;
use App\Jobs\sendNotification;
use App\Models\Notifications;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class NotificationController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.notification.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['notification-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $users = User::where('user_type','user')->orderBy('id','desc')->pluck('email','id');
        $drivers = User::where('user_type','driver')->where('driver_signup_as','individual')->orderBy('id','desc')->pluck('email','id');
        $subadmins = User::where('user_type','sub_admin')->orderBy('id','desc')->pluck('email','id');
        $companies = User::where('user_type','driver')->where('driver_signup_as','company')->orderBy('id','DESC')->pluck('email','id');
        return view($this->pageLayout.'index',compact('users','drivers','subadmins','companies'));
    }

    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'title'             => 'required',
            'description'       => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            if(isset($request->user_email)){
                foreach ($request->user_email as $key => $value) {
                    $userid = User::where('id', $value)->select('id','first_name','email')->first();
                    Notifications::create([
                        'title'        => @$request->get('title'),
                        'description'  => @$request->get('description'),
                        'user_id'      => $userid->id,
                    ]);
                    $emailcontent = array (
                        'text' => $request->description,
                        'title' => $request->title,
                        'userName' => $userid->first_name
                    );
                    // dd($emailcontent);
                    $details['email'] = $userid->email;
                    $details['username'] = $userid->first_name;
                    $details['subject'] = 'Notification For Users';
                    if(env('MAIL_HOST') == 'smtp.mailtrap.io'){
                        sleep(2);
                    }
                    dispatch(new SendEmailTest($details,$emailcontent));
                }
            }
            if(isset($request->driver_email)){
                foreach ($request->driver_email as $key => $value) {
                    $userid = User::where('id', $value)->select('id','first_name','email')->first();
                    Notifications::create([
                        'title'        => @$request->get('title'),
                        'description'  => @$request->get('description'),
                        'user_id'      => $userid->id,
                    ]);
                    $emailcontent = array (
                        'text' => $request->description,
                        'title' => $request->title,
                        'userName' => $userid->first_name
                    );
                    $details['email'] = $userid->email;
                    $details['username'] = $userid->first_name;
                    $details['subject'] = 'Notification For Drivers';
                    if(env('MAIL_HOST') == 'smtp.mailtrap.io'){
                        sleep(2);
                    }
                    dispatch(new SendEmailTest($details,$emailcontent));
                }
            }
            if(isset($request->company_email)){
                foreach ($request->company_email as $key => $value) {
                    $userid = User::where('id', $value)->select('id','first_name','email')->first();
                    Notifications::create([
                        'title'        => @$request->get('title'),
                        'description'  => @$request->get('description'),
                        'user_id'      => $userid->id,
                    ]);
                    $emailcontent = array (
                        'text' => $request->description,
                        'title' => $request->title,
                        'userName' => $userid->first_name
                    );
                    $details['email'] = $userid->email;
                    $details['username'] = $userid->first_names;
                    $details['subject'] = 'Notification For Company';
                    if(env('MAIL_HOST') == 'smtp.mailtrap.io'){
                        sleep(2);
                    }
                    dispatch(new SendEmailTest($details,$emailcontent));
                }
            }
            if(empty($request->user_email) && empty($request->driver_email) && empty($request->company_email)){
                Notify::error('Select Atleast One Email Id from Any User, Driver, Company');
                return redirect()->route('admin.notification.index');
            }
            Notify::success('Notification Created Successfully.');
            return redirect()->route('admin.notification.index');
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
            'username'          => 'required|unique:users,username,'.$id.',id,deleted_at,NULL',
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
                'username'          => @$request->get('username'),
                'avatar'            => @$filename,
                'email'             => @$request->get('email'),
                'password'          => @$password,
                'status'            => @$request->get('status'),
                'gender'            => @$request->get('gender'),
                'contact_number'    => @$request->get('contact_number')
            ]);
            Notify::success('Driver Updated Successfully.');
            return redirect()->route('admin.driver.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function mailbox(Builder $builder, Request $request){
        $notifications = Notifications::with('user')->orderBy('id','desc')->get();
        $notifications_count = Notifications::get()->count();
        return view($this->pageLayout.'list',compact('notifications','notifications_count'));
    }

    public function mail_detail(Request $request,$id){
        $notifications_list = Notifications::with('user')->where('id',$id)->first();
        Notifications::where('id',$id)->update([ 'is_read_user'=>'read' ]);
        return view($this->pageLayout.'mail_detail',compact('notifications_list'));
    }

    public function deletemail($id){
        try{
            $checkUser = Notifications::where('id',$id)->first();
            $checkUser->delete();
            Notify::success('Email Deleted Successfully.');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Email Deleted Successfully.'
            ]);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function setreadall(Request $request){
        Notifications::where('deleted_at', null)->update([ 'is_read_user'=>'read' ]);
        return redirect()->route('admin.mailbox');
    }
}
