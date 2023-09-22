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
use App\Model\User;
use App\Models\Booking;
use App\Models\CardDetails;
use Event;
use Illuminate\Http\Request;
use Settings;
use App\Models\ParcelDetail;
use App\Models\ParcelImage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class ParcelController extends Controller{
  protected $authLayout = '';
  protected $pageLayout = '';
  /*** Create a new controller instance.** @return void*/
  public function __construct(){
    $this->authLayout = 'admin.auth.';
    $this->pageLayout = 'admin.pages.parcels.';
    $this->middleware('auth');
  }

  public function index(Builder $builder, Request $request){
    $userRole = '';
        $userRole = Helper::checkPermission(['parcel-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }

    $parcels = ParcelDetail::with('user','driver','parcel_images')->where('user_id','!=',null)->groupBy('user_id')->orderBy('updated_at','DESC');
    if (request()->ajax()) {
      return DataTables::of($parcels->get())
      ->addIndexColumn()
      ->editColumn('driver_id', function (ParcelDetail $parcels) {
        if(!empty($parcels->driver)){
          $lasttrip = ParcelDetail::where('driver_id',$parcels->driver_id)->orderBy('updated_at','DESC')->first();
          if($lasttrip->driver['first_name']){
            return @$lasttrip->driver['first_name'].'&nbsp;<a title="View" class="m-l-10 show_driver "  data-id="'.@$lasttrip->driver['id'].'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>  <br><a class="ml-2 mr-2 " href='.route('admin.parcelslistdriver',[$parcels->driver_id]).'>View Parcel</a> ';
          }else if($lasttrip->driver['company_name']){
            return @$lasttrip->driver['company_name'].'&nbsp;<a title="View" class="m-l-10 show_driver "  data-id="'.@$lasttrip->driver['id'].'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>  <br><a class="ml-2 mr-2 " href='.route('admin.parcelslistdriver',[$parcels->driver_id]).'>View Parcel</a>';
          }else{
            return '-';
          }
        }else{
          return '-';
        }
      })
      ->editColumn('user_id', function (ParcelDetail $parcels) {
        if(!empty($parcels->user)){
          if($parcels->user->first_name){
            return @$parcels->user->first_name.'&nbsp;<a title="View" class="m-l-10 show_user "  data-id="'.@$parcels->user_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> <br><a class="ml-2 mr-2 " href='.route('admin.parcelslistuser',[$parcels->user_id]).'>View Parcel</a>';
          }else if($parcels->user->company_name){
            return @$parcels->user->company_name.'&nbsp;<a title="View" class="m-l-10 show_user "  data-id="'.@$parcels->user_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> <br><a class="ml-2 mr-2 " href='.route('admin.parcelslistuser',[$parcels->user_id]).'>View Parcel</a>';
          }else{
            return '-';
          }
        }else{
          return '-';
        }
      })
      ->editColumn('pick_up_location', function (ParcelDetail $parcels) {
        if(!empty($parcels->user)){
          $lasttrip = ParcelDetail::where('user_id',$parcels->user_id)->orderBy('updated_at','DESC')->first();
          return $lasttrip->pick_up_location;
        }else{
          return '-';
        }
      })
      ->editColumn('drop_location', function (ParcelDetail $parcels) {
        if(!empty($parcels->user)){
          $lasttrip = ParcelDetail::where('user_id',$parcels->user_id)->orderBy('updated_at','DESC')->first();
          return $lasttrip->drop_location;
        }else{
          return '-';
        }
      })
      ->editColumn('parcel_status', function(ParcelDetail $parcels) {
        $s="";
        $lasttrip = ParcelDetail::where('user_id',$parcels->user_id)->orderBy('updated_at','DESC')->first();
        if($lasttrip->parcel_status == "completed"){
          $s .= '<span class="label label-success">Completed</span>';
        }if($lasttrip->parcel_status == "pending"){
          $s .= '<span class="label label-primary">Pending</span>';
        }if($lasttrip->parcel_status == "on_going"){
          $s .= '<span class="label label-warning">On Going</span>';
        }if($lasttrip->parcel_status == "driver_arrived"){
          $s .= '<span class="label label-info">Driver Arrived</span>';
        }if($lasttrip->parcel_status == "cancelled"){
          $s .= '<span class="label label-danger">Cancelled</span>';
        }if($lasttrip->parcel_status == "accepted"){
          $s .= '<span class="label label-info">Accepted</span>';
        }
        return $s;
      })
      ->editColumn('action', function (ParcelDetail $parcels) {
        $action = '';
        $lasttrip = ParcelDetail::where('user_id',$parcels->user_id)->orderBy('updated_at','DESC')->first();
        $action .= '<a title="Edit" class="btn btn-warning btn-sm ml-1 hidden" href='.route('admin.driver.edit',[$lasttrip->id]).'><i class="fa fa-pencil"></i></a>';
        $action .= ' <a title="View" class="btn btn-info btn-sm ml-1 show_info" data-id ="'.$lasttrip->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
        $action .= '<a title="Parcel Images" class="btn btn-sm btn-success ml-1 show_parcelimages" data-id ="'.$lasttrip->id.'" href="javascript:void(0)"><i class="fa fa-image"></i></a>';
        return $action;
      })
      ->rawColumns(['action','driver_id','user_id','parcel_status','pick_up_location'])
      ->make(true);
    }
    $html = $builder->columns([
      ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
      ['data' => 'driver_id', 'name' => 'driver_id', 'title' => 'Driver','width'=>'10%'],
      ['data' => 'user_id', 'name' => 'user_id', 'title' => 'User','width'=>'10%'],
      ['data' => 'pick_up_location', 'name'    => 'pick_up_location', 'title' => 'Pickup Location','width'=>'10%'],
      ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location','width'=>'10%'],
      ['data' => 'parcel_status','name'=> 'parcel_status', 'title' => 'Status','width'=>'10%'],
      ['data' => 'total_amount', 'name' => 'total_amount', 'title' => 'Amount','width'=>'10%'],
      ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'10%',"orderable" => false],
    ])
    ->parameters([
      'order' =>[]
    ]);
    $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
    \View::share('roles',$roles);
    return view($this->pageLayout.'index',compact('html'));
  }

  public function parcelslistuser(Builder $builder, Request $request){
    $parcels = ParcelDetail::with('user','driver','parcel_images')->where('user_id',$request->id)->orderBy('updated_at','DESC');
    if (request()->ajax()) {
      return DataTables::of($parcels->get())
      ->addIndexColumn()
      ->editColumn('driver_id', function (ParcelDetail $parcels) {
        if(!empty($parcels->driver)){
          return @$parcels->driver->first_name.'&nbsp;<a title="View" class="m-l-10 show_driver "  data-id="'.@$parcels->driver_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';;
        }else{
          return '';
        }
      })
      ->editColumn('user_id', function (ParcelDetail $parcels) {
        if(!empty($parcels->user)){
          return @$parcels->user->first_name.'&nbsp;<a title="View" class="m-l-10 show_user "  data-id="'.@$parcels->user_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
        }else{
          return '';
        }
      })
      ->editColumn('parcel_status', function(ParcelDetail $parcels) {
        $s="";
        if($parcels->parcel_status == "completed"){
          $s .= '<span class="label label-success">Completed</span>';
        }if($parcels->parcel_status == "pending"){
          $s .= '<span class="label label-primary">Pending</span>';
        }if($parcels->parcel_statuss == "on_going"){
          $s .= '<span class="label label-warning">Active</span>';
        }if($parcels->parcel_status == "driver_arrived"){
          $s .= '<span class="label label-info">Driver Arrived</span>';
        }if($parcels->parcel_status == "cancelled"){
          $s .= '<span class="label label-danger">Cancelled</span>';
        }if($parcels->parcel_status == "accepted"){
          $s .= '<span class="label label-info">Accepted</span>';
        }
        return $s;
      })
      ->editColumn('action', function (ParcelDetail $parcels) {
        $action = '';
        $action .= '<a title="Edit" class="btn btn-warning btn-sm ml-1 hidden" href='.route('admin.driver.edit',[$parcels->id]).'><i class="fa fa-pencil"></i></a>';
        $action .= '<a title="View" class="btn btn-info ml-1 btn-sm  show_info" data-id ="'.$parcels->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
        $action .='<a title="Delete" class="btn btn-danger btn-sm ml-1 deleteuser" data-id ="'.$parcels->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        $action .= '<a title="Parcel Images" class="btn btn-sm btn-success ml-1 show_parcelimages" data-id ="'.$parcels->id.'" href="javascript:void(0)"><i class="fa fa-image"></i></a>';
        return $action;
      })
      ->rawColumns(['action','driver_id','user_id','parcel_status'])
      ->make(true);
    }
    $html = $builder->columns([
      ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
      ['data' => 'driver_id', 'name' => 'driver_id', 'title' => 'Driver','width'=>'10%',"orderable" => false, "searchable" => false],
      ['data' => 'user_id', 'name' => 'user_id', 'title' => 'User','width'=>'10%',"orderable" => false, "searchable" => false],

      ['data' => 'pick_up_location', 'name'    => 'pick_up_location', 'title' => 'Pickup Location','width'=>'10%'],
      ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location','width'=>'10%'],
      ['data' => 'parcel_status','name'=> 'parcel_status', 'title' => 'Status','width'=>'5%'],
      ['data' => 'total_amount', 'name' => 'total_amount', 'title' => 'Amount','width'=>'5%'],
      ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'15%',"orderable" => false, "searchable" => false],
    ])
    ->parameters([
      'order' =>[]
    ]);
    $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
    \View::share('roles',$roles);
    return view($this->pageLayout.'listuserparcels',compact('html'));
  }

  public function parcelslistdriver(Builder $builder, Request $request){
    $parcels = ParcelDetail::with('user','driver','parcel_images')->where('driver_id',$request->id)->orderBy('updated_at','DESC');
    if (request()->ajax()) {
      return DataTables::of($parcels->get())
      ->addIndexColumn()
      ->editColumn('driver_id', function (ParcelDetail $parcels) {
        if(!empty($parcels->driver)){
          return @$parcels->driver->first_name.'&nbsp;<a title="View" class="m-l-10 show_driver "  data-id="'.@$parcels->driver_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
        }else{
          return '';
        }
      })
      ->editColumn('user_id', function (ParcelDetail $parcels) {
        if(!empty($parcels->user)){
          return @$parcels->user->first_name.'&nbsp;<a title="View" class="m-l-10 show_user "  data-id="'.@$parcels->user_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
        }else{
          return '';
        }
      })
      ->editColumn('parcel_status', function(ParcelDetail $parcels) {
        $s="";
        if($parcels->parcel_status == "completed"){
          $s .= '<span class="label label-success">Completed</span>';
        }if($parcels->parcel_status == "pending"){
          $s .= '<span class="label label-primary">Pending</span>';
        }if($parcels->parcel_statuss == "on_going"){
          $s .= '<span class="label label-warning">Active</span>';
        }if($parcels->parcel_status == "driver_arrived"){
          $s .= '<span class="label label-info">Driver Arrived</span>';
        }if($parcels->parcel_status == "cancelled"){
          $s .= '<span class="label label-danger">Cancelled</span>';
        }if($parcels->parcel_status == "accepted"){
          $s .= '<span class="label label-info">Accepted</span>';
        }
        return $s;
      })
      ->editColumn('action', function (ParcelDetail $parcels) {
        $action = '';
        $action .= '<a title="Edit" class="btn btn-warning btn-sm ml-1 hidden" href='.route('admin.driver.edit',[$parcels->id]).'><i class="fa fa-pencil"></i></a>';
        $action .= '<a title="View" class="btn btn-info btn-sm ml-1 show_info" data-id ="'.$parcels->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
        $action .='<a title="Delete" class="btn btn-danger btn-sm ml-1 deleteuser" data-id ="'.$parcels->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        $action .= '<a title="Parcel Images" class="btn btn-sm btn-success ml-1 show_parcelimages" data-id ="'.$parcels->id.'" href="javascript:void(0)"><i class="fa fa-image"></i></a>';
        return $action;
      })
      ->rawColumns(['action','driver_id','user_id','parcel_status'])
      ->make(true);
    }
    $html = $builder->columns([
      ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
      ['data' => 'driver_id', 'name' => 'driver_id', 'title' => 'Driver','width'=>'10%',"orderable" => false, "searchable" => false],
      ['data' => 'user_id', 'name' => 'user_id', 'title' => 'User','width'=>'10%',"orderable" => false, "searchable" => false],
      ['data' => 'pick_up_location', 'name'    => 'pick_up_location', 'title' => 'Pickup Location','width'=>'15%'],
      ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location','width'=>'15%'],
      ['data' => 'parcel_status','name'=> 'parcel_status', 'title' => 'Status','width'=>'5%'],
      ['data' => 'total_amount', 'name' => 'total_amount', 'title' => 'Amount','width'=>'5%'],
      ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'15%',"orderable" => false, "searchable" => false],
    ])
    ->parameters([
      'order' =>[]
    ]);
    $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
    \View::share('roles',$roles);
    return view($this->pageLayout.'listuserparcels',compact('html'));
  }

  public function delete(Request $request, $id){
    $tripDetails = ParcelDetail::where('id',$id)->first();
    $tripDetails->delete();
    Notify::success('Parcel Detail deleted successfully.');
    return response()->json([
      'status'    => 'success',
      'title'     => 'Success!!',
      'message'   => 'Parcel Detail deleted successfully.'
    ]);
  }

  public function show_info($id){
    $tripDetails = ParcelDetail::with(['driver','user','parcel_packages'])->where('id',$id)->first();
    return response()->json([
      'driver'       => @$tripDetails['driver']->first_name.' '.@$tripDetails['driver']->last_name,
      'user'       => @$tripDetails['user']->first_name.' '.@$tripDetails['user']->last_name,
      'pick_up_location'       => @$tripDetails->pick_up_location,
      'drop_location'       => @$tripDetails->drop_location,
      'start_time'       => @$tripDetails->start_time,
      'end_time' => @$tripDetails->end_time,
      'hold_time' => @$tripDetails->hold_time,
      'base_fare' => @$tripDetails->base_fare,
      'total_distance' => @$tripDetails->total_distance,
      'admin_commision' => @$tripDetails->admin_commision,
      'transaction_id'  => @$tripDetails->transaction_id,
      'trip_status'  => @$tripDetails->parcel_status,
      'extra_notes'  => @$tripDetails->extra_notes,
      'promo_name'  => @$tripDetails->promo_name,
      'promo_amount'  => @$tripDetails->promo_amount,
      'promo_id'  => @$tripDetails->promo_id,
      'total_amount'  => @$tripDetails->total_amount,
      'parcel_length' =>@$tripDetails->parcel_length,
      'package_type' =>@$tripDetails->package_type,
      'parcel_deep' =>@$tripDetails->parcel_deep,
      'parcel_weight' =>@$tripDetails->parcel_weight,
      'parcel_packages' =>@$tripDetails->parcel_packages
    ]);
  }

  public function parcel_images($id){
    $parcelimages = ParcelImage::where('parcel_id',$id)->get();
    $view = \View::make('admin.pages.parcels.parcelimages',compact('parcelimages'))->render();
    return response()->json(['data'=>$view,'status' => 'success','message' =>"success"],200);
  }

  public function parcel_user_info($id){
    $tripDetails = ParcelDetail::with('user')->where('user_id',$id)->first();
    return response()->json([
      'first_name'       => $tripDetails['user']->first_name,
      'email_add'       => $tripDetails['user']->email,
      'gender'       => $tripDetails['user']->gender,
      'last_name'       => $tripDetails['user']->last_name,
      'status'       => $tripDetails['user']->status,
      'contact_number' => $tripDetails['user']->contact_number
    ]);
  }

  public function parcel_driver_info($id){
    $tripDetails = ParcelDetail::with('driver')->where('driver_id',$id)->first();
    return response()->json([
      'first_name'       => $tripDetails['driver']->first_name,
      'email_add'       => $tripDetails['driver']->email,
      'gender'       => $tripDetails['driver']->gender,
      'last_name'       => $tripDetails['driver']->last_name,
      'status'       => $tripDetails['driver']->status,
      'contact_number' => $tripDetails['driver']->contact_number
    ]);
  }
}
