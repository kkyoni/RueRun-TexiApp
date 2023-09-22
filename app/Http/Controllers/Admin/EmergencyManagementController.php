<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\UserReport;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use App\Models\EmergencyDetails;
use Auth;
use Event,Str,Storage;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;
use App\Models\RideSetting;
use App\Models\EmergencyRequest;
use App\Models\EmergencyType;
use App\Models\City;
use Carbon\Carbon;

class EmergencyManagementController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct()  {
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.emergency.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $emergency = EmergencyDetails::orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($emergency->get())
            ->addIndexColumn()
            ->editColumn('action', function(EmergencyDetails $emergency) {
                $action  = '';
                $action .= '<a title="Edit" href='.route('admin.emergencyedit',[$emergency->id]).' class="btn btn-warning btn-sm ml-1"><i class="fa fa-pencil"></i></a><a title="Delete" class="btn btn-danger btn-sm deleteemergency ml-1 hidden" data-id ="'.$emergency->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                if($emergency->status == "active"){
                    $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$emergency->id.'"><i class="fa fa-unlock"></i></a>';
                }else{
                    $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$emergency->id.'"><i class="fa fa-lock" ></i></a>';
                }
                return $action;
            })
            ->editColumn('avatar', function(EmergencyDetails $emergency) {
                if($emergency->image != ""){
                    return '<img class="img-thumbnail" src="' . asset('storage/avatar') . '/' . @$emergency->image . '" width="60px">';
                }else{
                    return '<img class="img-thumbnail" src="' . asset('storage/avatar/default.png').'" width="60px">';
                }
            })
            ->editColumn('status', function(EmergencyDetails $emergency) {
                if ($emergency->status == "active") {
                    return '<span class="label label-success">Active</span>';
                } else {
                    return '<span class="label label-danger">Block</span>';
                }
            })
            ->rawColumns(['status','action','avatar'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'avatar', 'name'    => 'avatar', 'title' => 'Avatar','width'=>'3%',"orderable" => false, "searchable" => false],
            ['data' => 'contact_person', 'name'    => 'contact_person', 'title' => 'Contact Person','width'=>'10%'],
            ['data' => 'contact_number', 'name'    => 'contact_number', 'title' => 'Contact Number','width'=>'10%'],
            ['data' => 'contact_details', 'name'    => 'contact_details', 'title' => 'Contact Details','width'=>'10%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'2%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'10%',"orderable" => false],
            ])
        ->parameters([
            'order' =>[],
            ]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        return view($this->pageLayout.'create');
    }

    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'contact_person'           => 'required',
            'contact_details'          => 'required',
            'contact_number'           => 'required|numeric'
            ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            if($request->hasFile('avatar') !=''){
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10).'.'.$extension;
                Storage::disk('public')->putFileAs('avatar', $file,$filename);
            }else{

            }
            $emergency = EmergencyDetails::create([
                'image'                 => @$filename,
                'contact_number'        => @$request->get('contact_number'),
                'contact_person'        => @$request->get('contact_person'),
                'contact_details'       => @$request->get('contact_details'),
                'status'                => @$request->get('status'),
                ]);
            Notify::success('Emergency Details Created Successfully.');
            return redirect()->route('admin.emergencyindex');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function edit($id){
        $emergency = EmergencyDetails::find($id);
        return view($this->pageLayout.'edit',compact('emergency'));
    }

    public function update(Request $request, $id){
        $validatedData = Validator::make($request->all(),[
            'contact_person'           => 'required',
            'contact_details'          => 'required',
            'contact_number'           => 'required|numeric'
            ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $allowedfileExtension=['pdf','jpg','png'];
            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10).'.'.$extension;
                Storage::disk('public')->putFileAs('avatar', $file,$filename);
            }else{
                $EmergencyDetails=EmergencyDetails::where('id',$id)->first()->avatar;
                $filename = $EmergencyDetails;
            }
            $emergency_data = EmergencyDetails::where('id',$id)->update([
                'image'               => @$filename,
                'contact_number'      => @$request->get('contact_number'),
                'contact_person'      => @$request->get('contact_person'),
                'contact_details'     => @$request->get('contact_details'),
                'status'              => @$request->get('status'),
                ]);
            Notify::success('Emergency Details Updated Successfully.');
            return redirect()->route('admin.emergencyindex');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function delete($id){
        $Promocodes = EmergencyDetails::where('id',$id)->first();
        $Promocodes->delete();
        Notify::success('Emergency User Details deleted successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Emergency User Details removed successfully.'
            ]);
    }

    public function change_status(Request $request){
        $emergency = EmergencyDetails::where('id',$request->id)->first();
        if($emergency->status == "active"){
            EmergencyDetails::where('id',$request->id)->update([
                'status'             => "inactive",
                ]);
        }
        if($emergency->status == "inactive"){
            EmergencyDetails::where('id',$request->id)->update(['status' => "active",]);
        }
        Notify::success('Status updated Successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Status updated successfully.'
            ]);
    }

    public function ridesetting_index(Builder $builder, Request $request){
        $emergency = RideSetting::orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($emergency->get())
            ->addIndexColumn()
            ->editColumn('status', function(RideSetting $emergency) {
                if ($emergency->status == "active") {
                    return '<span class="label label-success">Active</span>';
                } else {
                    return '<span class="label label-danger">Block</span>';
                }
            })
            ->editColumn('action', function(RideSetting $emergency) {
                $action  = '';
                $action .=  '<a title="Edit" href="'.route('admin.ridesetting.edit',$emergency->id).'" data-value="0"  data-toggle="tooltip" title="InActive" class="btn btn-warning btn-sm ml-1"><i class="fa fa-pencil"></i></a>';
                if($emergency->status == "active"){
                    $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeridestatus" data-id="'.$emergency->id.'"><i class="fa fa-unlock"></i></a>';
                }else{
                    $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeridestatus" data-id="'.$emergency->id.'"><i class="fa fa-lock" ></i></a>';
                }
                return $action;
            })
            ->rawColumns(['status','action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'name', 'name'=> 'name', 'title' =>'Ride Name','width'=>'10%'],
            ['data'=> 'status','name' =>'status', 'title' => 'Status','width'=>'2%'],
            ['data'=> 'action','name' =>'action', 'title' => 'Action','width'=>'2%'],
            ])
        ->parameters([
            'order' =>[],
            ]);
        return view($this->pageLayout.'ridesetting_index',compact('html'));
    }

    public function ridesetting_edit($id){
        $rideDetail = RideSetting::find($id);
        $cityList =City::pluck('city','id');
        $cityArr = explode(',',$rideDetail->city_list);
        return view($this->pageLayout.'ridesetting_edit',compact('rideDetail','cityList','cityArr'));
    }

    public function ridesetting_update($id,Request $request){
        $validatedData = Validator::make($request->all(),[
            'name'           => 'required',
            'city_list'          => 'required',
            ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        $rideDetail = RideSetting::find($id);
        $rideDetail->name = $request->name;
        $rideDetail->city_list = implode(',',$request->city_list);
        $rideDetail->save();
        Notify::success('Ride Settings Updated Successfully.');
        return redirect()->route('admin.ridesetting');
    }

    public function ride_change_status(Request $request){
        $emergency = RideSetting::where('id',$request->id)->first();
        if($emergency->status == "active"){
            RideSetting::where('id',$request->id)->update(['status'=>"inactive"]);
        }if($emergency->status == "inactive"){
            RideSetting::where('id',$request->id)->update(['status'=> "active"]);
        }
        Notify::success('Status updated Successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Status updated successfully.'
            ]);
    }

    public function request_index(Builder $builder, Request $request){
        $total_emergencyDetails_req = EmergencyRequest::with('user','driver','emergency_type')->whereDay('created_at',Carbon::now()->day)->get();
        foreach ($total_emergencyDetails_req as $value) {
            EmergencyRequest::where('id', $value->id)->update(['view_status' => '1']);
        }
        $requestlist = EmergencyRequest::orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($requestlist->get())
            ->addIndexColumn()
            ->editColumn('type_id', function(EmergencyRequest $requestlist) {
                return @$requestlist->emergency_type->type_name;
            })
            ->editColumn('driver_id', function(EmergencyRequest $requestlist) {
                return @$requestlist->driver->first_name.' '.@$requestlist->driver->last_name;
            })
            ->editColumn('created_at', function(EmergencyRequest $requestlist) {
                return date("m-d-Y g:i a", strtotime(@$requestlist->created_at.env('APP_TIMEZONE')));
            })
            ->editColumn('status', function(EmergencyRequest $requestlist) {
                $html='<select class="form-control changerequeststatus" data-id="'.$requestlist->id.'" id="changerequeststatus">';
                if($requestlist->status=='pending'){
                    $html.='<option value="pending" selected>Pending</option>';
                    $html.='<option value="resolved">Resolved</option>';
                    $html.='<option value="on_going">On Process</option>';
                }elseif($requestlist->status=='on_going'){
                    $html.='<option value="pending">Pending</option>';
                    $html.='<option value="on_going" selected>On Process</option>';
                    $html.='<option value="resolved">Resolved</option>';
                }elseif($requestlist->status=='resolved'){
                    $html.='<option value="pending">Pending</option>';
                    $html.='<option value="on_going">On Process</option>';
                    $html.='<option value="resolved" selected>Resolved</option>';
                }else{
                    $html.='<option value="pending">Pending</option>';
                    $html.='<option value="on_going">On Process</option>';
                    $html.='<option value="resolved">Resolved</option>';
                }
                $html.='</select>';
                return $html;
            })
            ->rawColumns(['type_id','driver_id','status'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'2%',"orderable" => false, "searchable" => false],
            ['data' => 'type_id', 'name'=> 'name', 'title' =>'Request Type','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'driver_id', 'name'=> 'driver_id', 'title' =>'User Name','width'=>'5%'],
            ['data' => 'created_at', 'name'=> 'created_at', 'title' =>'Date','width'=>'5%'],
            ['data'=> 'status','name' =>'status', 'title' => 'Status','width'=>'5%']
            ])
        ->parameters([
            'order' =>[],
            ]);
        return view($this->pageLayout.'request_index',compact('html'));
    }

    public function emergencytypes(Builder $builder, Request $request){
        $emergency = EmergencyType::orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($emergency->get())
            ->addIndexColumn()
            ->editColumn('status', function(EmergencyType $emergency) {
                if($emergency->status == "active"){
                    return '<span class="label label-success">Active</span>';
                } else {
                    return '<span class="label label-danger">Block</span>';
                }
            })
            ->editColumn('action', function(EmergencyType $emergency) {
                $action  = '';
                $action .= '<a title="Edit" href='.route('admin.emergencytypeedit',[$emergency->id]).' class="btn btn-warning btn-sm ml-1"><i class="fa fa-pencil"></i></a>';
                if($emergency->status == "active"){
                    $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changetypestatus" data-id="'.$emergency->id.'"><i class="fa fa-unlock"></i></a>';
                }else{
                    $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changetypestatus" data-id="'.$emergency->id.'"><i class="fa fa-lock" ></i></a>';
                }
                return $action;
            })
            ->rawColumns(['status','action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'code', 'name'=> 'code', 'title' =>'Code','width'=>'10%'],
            ['data' => 'type_name', 'name'=> 'type_name', 'title' =>'Emergency Type Name','width'=>'10%'],
            ['data'=> 'status','name' =>'status', 'title' => 'Status','width'=>'2%'],
            ['data'=> 'action','name' =>'action', 'title' => 'Action','width'=>'4%'],
            ])
        ->parameters(['order' =>[],]);
        return view($this->pageLayout.'emergency_index',compact('html'));
    }

    public function  emergencytypeedit(Request $request, $id){
        $emergency = EmergencyType::find($id);
        return view($this->pageLayout.'emergency_edit',compact('emergency'));
    }

    public function emergencytypeupdate(Request $request, $id){
        $validatedData = Validator::make($request->all(),[
            'contact_number' => 'required|numeric'
            ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $emergency_data = EmergencyType::where('id',$id)->update([
                'contact_number' => @$request->get('contact_number'),
                ]);
            Notify::success('Emergency Types Updated Successfully.');
            return redirect()->route('admin.emergencytypes');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
                ]);
        }
    }

    public function type_change_status(Request $request){
        $emergency = EmergencyType::where('id',$request->id)->first();
        if($emergency->status == "active"){
            EmergencyType::where('id',$request->id)->update(['status'=>"inactive"]);
        }
        if($emergency->status == "inactive"){
            EmergencyType::where('id',$request->id)->update(['status'=> "active"]);
        }
        Notify::success('Status updated Successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Status updated successfully.'
            ]);
    }

    public function emergecyrequest_changestatus(Request $request){
        try{
            $user = EmergencyRequest::where('id',$request->id)->first();
            if($user === null){
                return redirect()->back()->with([
                    'status'    => 'warning',
                    'title'     => 'Warning!!',
                    'message'   => 'Emergency Request  not found !!'
                    ]);
            }else{
                EmergencyRequest::where('id',$request->id)->update(['status'=> $request->status]);
            }
            Notify::success('Emergency Request status updated successfully !!');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Emergency Request status updated successfully .'
                ]);
        }catch (Exception $e){
            return response()->json([
                'status'    => 'error',
                'title'     => 'Error!!',
                'message'   => $e->getMessage()
                ]);
        }
    }

    public function userreports(Builder $builder, Request $request){
        $emergency = UserReport::with(['from_user','to_user'])->orderBy('id','DESC');
        if (request()->ajax()) {
            return DataTables::of($emergency->get())
            ->addIndexColumn()
            ->editColumn('action', function(UserReport $emergency) {
                return '<a title="View Comment" href="javascript::void(0);" class="btn btn-sm btn-success get_reports ml-1" data-id ="'.$emergency->id.'"><i class="fa fa-comment"></i></a>';
            })
            ->editColumn('from_user_id', function(UserReport $emergency) {
                if(!empty($emergency->from_user)){
                    return $emergency->from_user->first_name.' '.$emergency->from_user->last_name.' - '.$emergency->from_user->user_type;
                }else{
                    return '-';
                }
            })
            ->editColumn('to_user_id', function(UserReport $emergency) {
                if(!empty($emergency->to_user)){
                    return $emergency->to_user->first_name.' '.$emergency->to_user->last_name.' - '.$emergency->to_user->user_type;
                }else{
                    return '-';
                }
            })
            ->rawColumns(['status','action','avatar'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'from_user_id', 'name'    => 'from_user_id', 'title' => 'From User','width'=>'8%',"orderable" => false, "searchable" => false],
            ['data' => 'to_user_id', 'name'    => 'to_user_id', 'title' => 'To User','width'=>'8%'],
            ['data' => 'title', 'name'    => 'title', 'title' => 'Title','width'=>'10%'],
            ['data' => 'date', 'name'    => 'date', 'title' => 'Date & Time','width'=>'8%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'6%',"orderable" => false],
            ])
        ->parameters(['order' =>[],]);
        return view($this->pageLayout.'user_report',compact('html'));
    }

    public function get_reports($id){
        $tripDetails = UserReport::with(['from_user','to_user'])->where('id',$id)->first();
        return response()->json([
            'title'         => @$tripDetails->title,
            'description'   => @$tripDetails->description,
            'from_user'     => @$tripDetails->from_user->first_name,
            'date'          => @$tripDetails->date,
            ]);
    }
}