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
use App\Models\LinerideBooking;
use App\Models\LinerideUserBooking;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class LineRideController extends Controller{
  protected $authLayout = '';
  protected $pageLayout = '';
  /*** Create a new controller instance.** @return void*/
  public function __construct(){
    $this->authLayout = 'admin.auth.';
    $this->pageLayout = 'admin.pages.lineride.';
    $this->middleware('auth');
  }

  public function index(Builder $builder, Request $request){
    $userRole = '';
        $userRole = Helper::checkPermission(['shuttle-ride-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        
    $lineride = LinerideBooking::orderBy('updated_at','desc');
    if (request()->ajax()) {
      return DataTables::of($lineride->get())
      ->addIndexColumn()
      ->editColumn('driver.first_name', function (LinerideBooking $lineride) {
        return $lineride->driver->first_name.' '.$lineride->driver->last_name;
      })
      ->editColumn('trip_status', function(LinerideBooking $lineride) {
        $s="";
        if($lineride->trip_status == "completed"){
          $s .= '<span class="label label-success">Completed</span>';
        }
        if($lineride->trip_status == "pending"){
          $s .= '<span class="label label-primary">Pending</span>';
        }
        if($lineride->trip_status == "on_going"){
          $s .= '<span class="label label-warning">On Going</span>';
        }
        if($lineride->trip_status == "driver_arrived"){
          $s .= '<span class="label label-info">Driver Arrived</span>';
        }
        if($lineride->trip_status == "cancelled"){
          $s .= '<span class="label label-danger">Cancelled</span>';
        }
        if($lineride->trip_status == "accepted"){
          $s .= '<span class="label label-info">Accepted</span>';
        }
        return $s;
      })
      ->editColumn('action', function (LinerideBooking $lineride) {
        $action = '';
        $action .= '<a title="View" class="btn btn-info btn-sm ml-1 show_info" href='.route('admin.listshuttleridedriver',[$lineride->id]).'><i class="fa fa-eye"></i></a>';
        return $action;
      })
      ->rawColumns(['action','driver_id','user_id','trip_status'])
      ->make(true);
    }
    $html = $builder->columns([
      ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
      ['data' => 'driver.first_name', 'name' => 'driver.first_name', 'title' => 'Driver','width'=>'10%'],
      ['data' => 'pick_up_location', 'name'    => 'pick_up_location', 'title' => 'Pickup Location','width'=>'15%'],
      ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location','width'=>'15%'],
      ['data' => 'total_amount', 'name' => 'total_amount', 'title' => 'Total Amount','width'=>'10%'],
      ['data' => 'trip_status','name'=> 'trip_status', 'title' => 'Trip Status','width'=>'10%'],
      ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
    ])
    ->parameters(['order' =>[]]);
    $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
    \View::share('roles',$roles);
    return view($this->pageLayout.'index',compact('html'));
  }

  public function listshuttleridedriver(Builder $builder, Request $request){
    $LinerideUserbooking = LinerideUserBooking::where('shuttle_driver_id',$request->id)->orderBy('updated_at','DESC');
    if (request()->ajax()) {
      return DataTables::of($LinerideUserbooking->get())
      ->addIndexColumn()
      ->editColumn('pick_up_location', function(LinerideUserBooking $LinerideUserbooking) {
        return '<lable>'.Str::words($LinerideUserbooking->pick_up_location, 4,'....').'</lable>';
      })
      ->editColumn('drop_location', function(LinerideUserBooking $LinerideUserbooking) {
        return '<lable>'.Str::words($LinerideUserbooking->drop_location, 4,'....').'</lable>';
      })
      ->editColumn('trip_status', function(LinerideUserBooking $LinerideUserbooking) {
        $s="";
        if($LinerideUserbooking->trip_status == "completed"){
          $s .= '<span class="label label-success">Completed</span>';
        }
        if($LinerideUserbooking->trip_status == "pending"){
          $s .= '<span class="label label-primary">Pending</span>';
        }
        if($LinerideUserbooking->trip_status == "on_going"){
          $s .= '<span class="label label-warning">On Going</span>';
        }
        if($LinerideUserbooking->trip_status == "driver_arrived"){
          $s .= '<span class="label label-info">Driver Arrived</span>';
        }
        if($LinerideUserbooking->trip_status == "cancelled"){
          $s .= '<span class="label label-danger">Cancelled</span>';
        }
        if($LinerideUserbooking->trip_status == "accepted"){
          $s .= '<span class="label label-warning">Accepted</span>';
        }
        return $s;
      })
      ->editColumn('user', function(LinerideUserBooking $LinerideUserbooking) {
        if(!empty($LinerideUserbooking['user'])){
          return $LinerideUserbooking['user']->first_name.' '.$LinerideUserbooking['user']->last_name;
        }else{
          return '-';
        }
      })
      ->rawColumns(['user','pick_up_location','drop_location','trip_status'])
      ->make(true);
    }
    $html = $builder->columns([
      ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
      ['data' => 'user', 'name'    => 'user', 'title' => 'User','width'=>'10%'],
      ['data' => 'pick_up_location', 'name'    => 'pick_up_location', 'title' => 'Pick Up Location','width'=>'10%'],
      ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location','width'=>'10%'],
      ['data' => 'total_amount', 'name'    => 'total_amount', 'title' => 'Total Amount','width'=>'10%'],
      ['data' => 'trip_status', 'name'    => 'trip_status', 'title' => 'Trip Status','width'=>'10%'],
    ])->parameters(['order' =>[],]);
    return view($this->pageLayout.'list_trip',compact('html'));
  }
}
