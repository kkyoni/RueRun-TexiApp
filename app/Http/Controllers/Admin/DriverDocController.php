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
use App\Models\DriverDocuments;
use Event;
use Illuminate\Http\Request;
use Settings;
use App\Models\User;
use App\Models\DriverVehicleDocument;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;


class DriverDocController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.driverdoc.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['driver-document-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        
        $driver = DriverDocuments::with(['userDetail'])->whereNotNull('driver_id')->orderBy('updated_at','desc')->groupBy('driver_id');
        if (request()->ajax()) {
            return DataTables::of($driver->get())
            ->addIndexColumn()
            ->editColumn('avatar', function (DriverDocuments $driver) {
                $i="";
                if($driver->userDetail->avatar != ""){
                    if (file_exists( 'storage/avatar/'.$driver->userDetail->avatar)) {
                        $i .= "<img src=".url('storage/avatar/'.$driver->userDetail->avatar)."  width='60px'/>";
                    }else{
                        $i .= "<img src=".url('storage/avatar/default.png')."  width='60px'/>";
                    }
                }else{
                    $i .= "<img src=".url('storage/avatar/default.png')."  width='60px'/>";
                }
                return $i;
            })
            ->editColumn('userDetail.first_name', function (DriverDocuments $driver) {
                $i="";
                if(!empty($driver->userDetail)){
                    return $driver->userDetail->first_name.' '.$driver->userDetail->last_name;
                }else{
                    return '-';
                }
            })
            ->editColumn('action', function (DriverDocuments $driver) {
                return '<a title="View" href="javascript::void(0);" class="btn btn-info btn-sm ml-1 get_user_doc" data-id ="'.$driver->driver_id.'" href="javascript:void(0)"><i class="fa fa-eye "></i></a>';
            })
            ->editColumn('doc_status', function (DriverDocuments $driver) {
                if($driver->userDetail->doc_status == 'approved') {
                    return '<span class="label label-success">Approved</span>';
                }
                $html = '<select class="form-control" id="changeDocStatus" data-id='.$driver->driver_id.'>';
                if($driver->userDetail->doc_status == 'pending') {
                    $html .='<option value="pending" selected>Pending</oprion>';
                }else{
                    $html .='<option value="pending">Pending</oprion>';
                }
                if($driver->userDetail->doc_status=='reject') {
                    $html .='<option value="reject" selected>Reject</oprion>';
                }else{
                    $html .='<option value="reject">Reject</oprion>';
                }
                if($driver->userDetail->doc_status=='approved') {
                    $html .='<option value="approved" selected>Approved</oprion>';
                }else{
                    $html .='<option value="approved">Approved</oprion>';
                }
                $html .= '</select>';
                return $html;
            })
            ->editColumn('userDetail.doc_status', function (DriverDocuments $driver) {
                return $driver->userDetail->doc_status;
            })
            ->editColumn('first_name', function (DriverDocuments $driver) {
                if( !empty($driver->userDetail->first_name)){
                    return $driver->userDetail->first_name.' '.$driver->userDetail->last_name;
                }else if($driver->userDetail->company_name){
                    return $driver->userDetail->company_name;
                }else{
                    return '-';
                }
            })
            ->rawColumns(['avatar','licence','doc_image','doc_status','action','first_name'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'avatar', 'name'    => 'userDetail.avatar', 'title' => 'Avatar','width'=>'10%',"orderable" => false, "searchable" => false],
            ['data' => 'userDetail.first_name', 'name'    => 'userDetail.first_name', 'title' => 'Driver Name','width'=>'10%'],
            ['data' => 'doc_status', 'name' => 'doc_status', 'title' => 'Doc Status','width'=>'15%','searchable'=>false],
            ['data' => 'userDetail.doc_status', 'name' => 'userDetail.doc_status', 'title' => 'Doc Status','width'=>'15%','visible'=>false],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'5%'],
            ])
        ->parameters(['order' =>[]]);
        $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
        \View::share('roles',$roles);
        return view($this->pageLayout.'index',compact('html'));
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

    public function change_status(Request $request){
        try{
            $user = User::where('id',$request->id)->first();
            if($user === null){
                return redirect()->back()->with([
                    'status'    => 'warning',
                    'title'     => 'Warning!!',
                    'message'   => 'Driver not found !!'
                    ]);
            }else{
                if($request->status == "reject"){
                    User::where('id',$request->id)->update(['doc_status' => $request->status,'driver_doc' => "0"]);
                }else {
                    User::where('id',$request->id)->update(['doc_status' => $request->status]);    
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

    public function user_doc($id){
        $user_doc_list = DriverDocuments::where('driver_id',$id)->get();
        $view = \View::make('admin.pages.driverdoc.driver',compact('user_doc_list'))->render();
        return response()->json(['data'=>$view,'status' => 'success','message' =>"success"],200);
    }

    public function vehcile_index(Builder $builder, Request $request){
        $userRole = Helper::checkPermission(['vehicle-document-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }

        $driver = DriverVehicleDocument::with(['userDetail'])->whereHas('userDetail')->orderBy('updated_at','desc')->groupBy('driver_id');
        if (request()->ajax()) {
            return DataTables::of($driver->get())
            ->addIndexColumn()
            ->editColumn('avatar', function (DriverVehicleDocument $driver) {
                $i="";
                if($driver->userDetail->avatar != ""){
                    if (file_exists( 'storage/avatar/'.$driver->userDetail->avatar)) {
                        $i .= "<img src=".url('storage/avatar/'.$driver->userDetail->avatar)."  width='60px' />";
                    }else{
                        $i .= "<img src=".url('storage/avatar/default.png')."  width='60px'/>";
                    }
                }else{
                    $i .= "<img src=".url('storage/avatar/default.png')."  width='60px'/>";
                }
                return $i;
            })
            ->editColumn('userDetail.first_name', function (DriverVehicleDocument $driver) {
                $i="";
                if(!empty($driver->userDetail)){
                    return $driver->userDetail->first_name.' '.$driver->userDetail->last_name;
                }else{
                    return '-';
                }
            })
            ->editColumn('action', function (DriverVehicleDocument $driver) {
                return '<a title="View" href="javascript::void(0);" class="btn btn-info ml-1 btn-sm get_vehicle_doc" data-id ="'.$driver->driver_id.'" href="javascript:void(0)"><i class="fa fa-eye "></i></a>';
            })
            ->editColumn('vehicle_doc_status', function (DriverVehicleDocument $driver) {
                if($driver->userDetail->vehicle_doc_status=='approved') {
                    return '<span class="label label-success">Approved</span>';
                }
                $html = '<select class="form-control" id="changeVehicleStatus" data-id='.$driver->driver_id.'>';
                if($driver->userDetail->vehicle_doc_status=='pending') {
                    $html .='<option value="pending" selected>Pending</oprion>';
                }else{
                    $html .='<option value="pending">Pending</oprion>';
                }
                if($driver->userDetail->vehicle_doc_status=='reject') {
                    $html .='<option value="reject" selected>Reject</oprion>';
                }else{
                    $html .='<option value="reject">Reject</oprion>';
                }
                if($driver->userDetail->vehicle_doc_status=='approved') {
                    $html .='<option value="approved" selected>Approved</oprion>';
                }else{
                    $html .='<option value="approved">Approved</oprion>';
                }
                $html .= '</select>';
                return $html;
            })
            ->editColumn('userDetail.vehicle_doc_status', function (DriverVehicleDocument $driver) {
                return $driver->userDetail->vehicle_doc_status;
            })
            ->editColumn('first_name', function (DriverVehicleDocument $driver) {
                if( !empty($driver->userDetail->first_name)){
                    return $driver->userDetail->first_name.' '.$driver->userDetail->last_name;
                }else if($driver->userDetail->company_name){
                    return $driver->userDetail->company_name;
                }else{
                    return '-';
                }
            })
            ->rawColumns(['avatar','licence','doc_image','vehicle_doc_status','action','first_name'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'avatar', 'name' => 'userDetail.avatar', 'title' => 'Avatar','width'=>'7%',"orderable" => false, "searchable" => false],
            ['data' => 'userDetail.first_name', 'name'    => 'userDetail.first_name', 'title' => 'Driver Name','width'=>'15%'],
            ['data' => 'userDetail.vehicle_doc_status', 'name' => 'userDetail.vehicle_doc_status', 'title' => 'Vehicle Doc Status','width'=>'15%','visible'=>false],
            ['data' => 'vehicle_doc_status', 'name' => 'vehicle_doc_status', 'title' => 'Vehicle Doc Status','width'=>'15%',"searchable" => false],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'5%'],
            ])
        ->parameters(['order' =>[] ]);
        return view($this->pageLayout.'vehicle_index',compact('html'));
    }

    public function vehicle_doc($id){
        $vehicle_doc_list = DriverVehicleDocument::with('userDetail')->where('driver_id', (int)$id)->get();
        $view = \View::make('admin.pages.driverdoc.vehicle',compact('vehicle_doc_list'))->render();
        return response()->json(['data'=>$view,'status' => 'success','message' =>"success"],200);
    }

    public function vehicle_change_status(Request $request){
        try{
            $user = User::where('id',$request->id)->first();
            if($user === null){
                return redirect()->back()->with([
                    'status'    => 'warning',
                    'title'     => 'Warning!!',
                    'message'   => 'Driver not found !!'
                    ]);
            }else{
                if($request->status == "reject"){
                    User::where('id',$request->id)->update(['vehicle_doc_status' => $request->status,'vehicle_id' => ""]);
                }else {
                    User::where('id',$request->id)->update(['vehicle_doc_status'=> $request->status]);
                }
            }
            Notify::success('Vehicle status updated successfully !!');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Vehicle status updated successfully.'
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