<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use Auth;
use Event,Str,Storage;
use App\Models\Booking;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class BookingController extends Controller{
  protected $authLayout = '';
  protected $pageLayout = '';
  /*** Create a new controller instance.** @return void*/
  public function __construct(){
    $this->authLayout = 'admin.auth.';
    $this->pageLayout = 'admin.pages.trip.';
    $this->middleware('auth');
  }

  /*Details pages*/
  public function index(Builder $builder, Request $request){
    $userRole = '';
    $userRole = Helper::checkPermission(['booking-list']);
    if(!$userRole){
      $message = "You don't have permission to access this module.";
      return view('error.permission',compact('message'));
    }
    $booking = Booking::where('user_id','!=',null)->groupBy('user_id')->orderBy('updated_at','DESC');
    if (request()->ajax()) {
      return DataTables::of($booking->get())
      ->addIndexColumn()
      ->editColumn('action', function(Booking $booking) {
        $action='';
        $lasttrip = Booking::where('user_id',$booking->user_id)->orderBy('updated_at','DESC')->first();
        if($booking->trip_status !== 'on_going'){
          $action = '<a title="Delete" class="btn btn-danger btn-sm deletetrip ml-1" data-id ="'.$lasttrip->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        }
        return '<a title="View" class="btn btn-info btn-sm show_info ml-1" data-id ="'.$lasttrip->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
      })
      ->editColumn('driver_id', function(Booking $booking) {
        if(!empty($booking->driver)){
          $lasttrip = Booking::where('driver_id',$booking->driver_id)->orderBy('updated_at','DESC')->first();
          if(!empty($lasttrip->driver['first_name'])){
            return ''.$lasttrip->driver['first_name'].'&nbsp;<a title="View" class="m-l-10 show_driver "  data-id="'.$lasttrip->driver_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a><br><a class="ml-2 mr-2 " href='.route('admin.listtripdriver',[$booking['driver']->id]).'>View Booking</a>';
          }else{
            return '-';
          }
        }else{
          return '-';
        }
      })
      ->editColumn('pick_up_location', function(Booking $booking) {
        $lasttrip = Booking::where('user_id',$booking->user_id)->orderBy('updated_at','DESC')->first();
        return '<lable>'.Str::words($lasttrip->pick_up_location, 4,'....').'</lable>';
      })
      ->editColumn('drop_location', function(Booking $booking) {
        $lasttrip = Booking::where('user_id',$booking->user_id)->orderBy('updated_at','DESC')->first();
        return '<lable>'.Str::words($lasttrip->drop_location, 4,'....').'</lable>';
      })
      ->editColumn('user', function(Booking $booking) {
        if(!empty($booking['user'])){
          return ''.$booking['user']->first_name.' &nbsp; <a title="View" class="m-l-10 show_user "  data-id="'.$booking->user_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>    <br><a class="ml-2 mr-2 " href='.route('admin.listtrip',[$booking['user']->id]).'>View Booking</a>';
        }else{
          return '-';
        }
      })
      ->editColumn('trip_map', function(Booking $booking) {
        return '<a class="m-l-10 ride_info "  data-id="'.$booking->id.'" href="javascript:void(0)"><i class="fa fa-map"></i></a>';
      })
      ->editColumn('total_km', function(Booking $booking) {
        $lasttrip = Booking::where('user_id',$booking->user_id)->orderBy('updated_at','DESC')->first();
        return $lasttrip->total_km;
      })
      ->editColumn('total_amount', function(Booking $booking) {
        $lasttrip = Booking::where('user_id',$booking->user_id)->orderBy('updated_at','DESC')->first();
        return $lasttrip->total_amount;
      })
      ->editColumn('trip_status', function(Booking $booking) {
        $s="";
        $lasttrip = Booking::where('user_id',$booking->user_id)->orderBy('updated_at','DESC')->first();
        if($lasttrip->trip_status == "completed"){
          $s .= '<span class="label label-success">Completed</span>';
        }if($lasttrip->trip_status == "pending"){
          $s .= '<span class="label label-primary">Pending</span>';
        }if($lasttrip->trip_status == "on_going"){
          $s .= '<span class="label label-warning">On Going</span>';
        }if($lasttrip->trip_status == "driver_arrived"){
          $s .= '<span class="label label-info">Driver Arrived</span>';
        }if($lasttrip->trip_status == "cancelled"){
          $s .= '<span class="label label-danger">Cancelled</span>';
        }if($lasttrip->trip_status == "accepted"){
          $s .= '<span class="label label-warning">Accepted</span>';
        }if($lasttrip->trip_status == "pick_up"){
          $s .= '<span class="label label-warning">Pick Up</span>';
        }
        return $s;
      })
      ->rawColumns(['trip_status','action','avatar','driver_id','user','trip_map','pick_up_location','drop_location','total_km','total_amount'])
      ->make(true);
    }
    $html = $builder->columns([
      ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
      ['data' => 'driver_id', 'name'    => 'driver_id', 'title' => 'Driver','width'=>'20%'],
      ['data' => 'user', 'name'    => 'user', 'title' => 'User','width'=>'20%'],
      ['data' => 'pick_up_location', 'name'    => 'pick_up_location', 'title' => 'Pick Up Location','width'=>'10%'],
      ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location','width'=>'10%'],
      ['data' => 'total_km', 'name'    => 'total_km', 'title' => 'Total Km','width'=>'10%'],
      ['data' => 'total_amount', 'name'    => 'total_amount', 'title' => 'Total Amount','width'=>'7%'],
      ['data' => 'trip_status', 'name'    => 'trip_status', 'title' => 'Trip Status','width'=>'12%'],
      ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'2%',"orderable" => false],
      ])->parameters([
      'order' =>[],
      ]);
      return view($this->pageLayout.'index',compact('html'));
    }

    public function listtrip(Builder $builder, Request $request){
      $booking = Booking::where('user_id',$request->id)->orderBy('updated_at','DESC');
      if (request()->ajax()) {
        return DataTables::of($booking->get())
        ->addIndexColumn()
        ->editColumn('action', function(Booking $booking) {
          $action='';
          if($booking->trip_status !== 'on_going'){
            $action = '<a title="Delete" class="btn btn-danger btn-sm ml-1 deletetrip" data-id ="'.$booking->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
          }
          return '<a title="View" class="btn btn-info btn-sm ml-1 show_info" data-id ="'.$booking->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>'.$action;
        })
        ->editColumn('driver_id', function(Booking $booking) {
          if(!empty($booking->driver)){
            return ''.$booking->driver->first_name.'&nbsp;<a title="View" class="m-l-10 show_driver "  data-id="'.$booking->driver_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
          }else{
            return '-';
          }
        })
        ->editColumn('pick_up_location', function(Booking $booking) {
          return '<lable>'.Str::words($booking->pick_up_location, 4,'....').'</lable>';
        })
        ->editColumn('drop_location', function(Booking $booking) {
          return '<lable>'.Str::words($booking->drop_location, 4,'....').'</lable>';
        })
        ->editColumn('user', function(Booking $booking) {
          if(!empty($booking['user'])){
            return ''.$booking['user']->first_name.' &nbsp; <a title="View" class="m-l-10 show_user "  data-id="'.$booking->user_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
          }else{
            return '-';
          }
        })
        ->editColumn('trip_map', function(Booking $booking) {
          return '<a class="m-l-10 ride_info "  data-id="'.$booking->id.'" href="javascript:void(0)"><i class="fa fa-map"></i></a>';
        })
        ->editColumn('trip_status', function(Booking $booking) {
          $s="";
          if($booking->trip_status == "completed"){
            $s .= '<span class="label label-success">Completed</span>';
          }if($booking->trip_status == "pending"){
            $s .= '<span class="label label-primary">Pending</span>';
          }if($booking->trip_status == "on_going"){
            $s .= '<span class="label label--warning">On Going</span>';
          }if($booking->trip_status == "driver_arrived"){
            $s .= '<span class="label label-info">Driver Arrived</span>';
          }if($booking->trip_status == "cancelled"){
            $s .= '<span class="label label-danger">Cancelled</span>';
          }if($booking->trip_status == "accepted"){
            $s .= '<span class="label label-warning">Accepted</span>';
          }if($booking->trip_status == "pick_up"){
            $s .= '<span class="label label-warning">Pick Up</span>';
          }
          return $s;
        })
        ->rawColumns(['trip_status','action','avatar','driver_id','user','trip_map','pick_up_location','drop_location'])
        ->make(true);
      }
      $html = $builder->columns([
        ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
        ['data' => 'driver_id', 'name'    => 'driver_id', 'title' => 'Driver','width'=>'10%'],
        ['data' => 'user', 'name'    => 'user', 'title' => 'User','width'=>'10%'],
        ['data' => 'pick_up_location', 'name'    => 'pick_up_location', 'title' => 'Pick Up Location','width'=>'10%'],
        ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location','width'=>'10%'],
        ['data' => 'total_km', 'name'    => 'total_km', 'title' => 'Total Km','width'=>'10%'],
        ['data' => 'total_amount', 'name'    => 'total_amount', 'title' => 'Total Amount','width'=>'10%'],
        ['data' => 'trip_status', 'name'    => 'trip_status', 'title' => 'Trip Status','width'=>'10%'],
        ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'10%',"orderable" => false],
        ])->parameters([
        'order' =>[],
        ]);
        return view($this->pageLayout.'list_trip',compact('html'));
      }

      public function listtripdriver(Builder $builder, Request $request){
        $booking = Booking::where('driver_id',$request->id)->orderBy('updated_at','DESC');
        if (request()->ajax()) {
          return DataTables::of($booking->get())
          ->addIndexColumn()
          ->editColumn('action', function(Booking $booking) {
            $action='';
            if($booking->trip_status !== 'on_going'){
              $action = '<a title="Delete" class="btn btn-danger btn-sm ml-1 deletetrip" data-id ="'.$booking->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
            }
            return '<a title="View" class="btn btn-info btn-sm ml-1 show_info" data-id ="'.$booking->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>'.$action;
          })
          ->editColumn('driver_id', function(Booking $booking) {
            if(!empty($booking->driver)){
              return ''.$booking->driver->first_name.'&nbsp;<a title="View" class="m-l-10 show_driver "  data-id="'.$booking->driver_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
            }else{
              return '-';
            }
          })
          ->editColumn('pick_up_location', function(Booking $booking) {
            return '<lable>'.Str::words($booking->pick_up_location, 4,'....').'</lable>';
          })
          ->editColumn('drop_location', function(Booking $booking) {
            return '<lable>'.Str::words($booking->drop_location, 4,'....').'</lable>';
          })
          ->editColumn('user', function(Booking $booking) {
            if(!empty($booking['user'])){
              return ''.$booking['user']->first_name.' &nbsp; <a title="View" class="m-l-10 show_user "  data-id="'.$booking->user_id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
            }else{
              return '-';
            }
          })
          ->editColumn('trip_map', function(Booking $booking) {
            return '<a class="m-l-10 ride_info "  data-id="'.$booking->id.'" href="javascript:void(0)"><i class="fa fa-map"></i></a>';
          })
          ->editColumn('trip_status', function(Booking $booking) {
            $s="";
            if($booking->trip_status == "completed"){
              $s .= '<span class="label label-success">Completed</span>';
            }if($booking->trip_status == "pending"){
              $s .= '<span class="label label-primary">Pending</span>';
            }if($booking->trip_status == "on_going"){
              $s .= '<span class="label label-warning">On Going</span>';
            }if($booking->trip_status == "driver_arrived"){
              $s .= '<span class="label label-info">Driver Arrived</span>';
            }if($booking->trip_status == "cancelled"){
              $s .= '<span class="label label-danger">Cancelled</span>';
            }if($booking->trip_status == "accepted"){
              $s .= '<span class="label label-warning">Accepted</span>';
            }if($booking->trip_status == "pick_up"){
              $s .= '<span class="label label-warning">Pick Up</span>';
            }
            return $s;
          })
          ->rawColumns(['trip_status','action','avatar','driver_id','user','trip_map','pick_up_location','drop_location'])
          ->make(true);
        }
        $html = $builder->columns([
          ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
          ['data' => 'driver_id', 'name'    => 'driver_id', 'title' => 'Driver','width'=>'10%'],
          ['data' => 'user', 'name'    => 'user', 'title' => 'User','width'=>'10%'],
          ['data' => 'pick_up_location', 'name'    => 'pick_up_location', 'title' => 'Pick Up Location','width'=>'10%'],
          ['data' => 'drop_location', 'name' => 'drop_location', 'title' => 'Drop Location','width'=>'10%'],
          ['data' => 'total_km', 'name'    => 'total_km', 'title' => 'Total Km','width'=>'10%'],
          ['data' => 'total_amount', 'name'    => 'total_amount', 'title' => 'Total Amount','width'=>'10%'],
          ['data' => 'trip_status', 'name'    => 'trip_status', 'title' => 'Trip Status','width'=>'10%'],
          ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'10%',"orderable" => false],
          ])->parameters([
          'order' =>[],
          ]);
          return view($this->pageLayout.'list_trip',compact('html'));
        }

        /* create Emergency details page view */
        public function create(){
          return view($this->pageLayout.'create');
        }

        public function get_driver_info($id){
          $tripDetails = Booking::with('driver')->where('driver_id',$id)->first();
          return response()->json([
            'first_name'      => @$tripDetails['driver']->first_name,
            'email_add'       => @$tripDetails['driver']->email,
            'gender'          => @$tripDetails['driver']->gender,
            'last_name'       => @$tripDetails['driver']->last_name,
            'status'          => @$tripDetails['driver']->status,
            'contact_number'  => @$tripDetails['driver']->contact_number
            ]);
        }

        public function show_info($id){
          $tripDetails = Booking::with('driver','user','ride_type_id','promocode_get')->where('id',$id)->first();
          if(!empty($tripDetails->start_date)){
            $start_date = date("m-d-Y", strtotime($tripDetails->start_date));
          }else{
            $start_date = '-';
          }
          return response()->json([
            'driver'           => @$tripDetails['driver']->first_name.' '.@$tripDetails['driver']->last_name,
            'user'             => @$tripDetails['user']->first_name.' '.@$tripDetails['user']->last_name,
            'pick_up_location' => $tripDetails->pick_up_location,
            'drop_location'    => $tripDetails->drop_location,
            'start_time'       => $tripDetails->start_time,
            'end_time'         => $tripDetails->end_time,
            'hold_time'        => $tripDetails->hold_time,
            'base_fare'        => $tripDetails->base_fare,
            'total_km'         => $tripDetails->total_km,
            'admin_commision'  => $tripDetails->admin_commision,
            'transaction_id'   =>$tripDetails->transaction_id,
            'trip_status'      =>$tripDetails->trip_status,
            'extra_notes'      =>$tripDetails->extra_notes,
            'total_amount'     => $tripDetails->total_amount,
            'date'             => date("m-d-Y", strtotime($tripDetails->booking_date)),
            'promocode'        => @$tripDetails['promocode_get']->promo_code,
            'trip_type_status' => @$tripDetails['ride_type_id']->name,
            'booking_date'     => date("m-d-Y", strtotime($tripDetails->booking_date)),
            'start_date'       => $start_date,
            ]);
        }

        public function get_user_info($id){
          $tripDetails = Booking::with('user')->where('user_id',$id)->first();
          return response()->json([
            'first_name'       => $tripDetails['user']->first_name,
            'email_add'       => $tripDetails['user']->email,
            'gender'       => $tripDetails['user']->gender,
            'last_name'       => $tripDetails['user']->last_name,
            'status'       => $tripDetails['user']->status,
            'contact_number' => $tripDetails['user']->contact_number
            ]);
        }

        public function ride_info($id){
          $tripDetails = Booking::with('user')->where('id',$id)->first();
          return response()->json([
            'start_latitude'       => $tripDetails->start_latitude,
            'start_longitude'       => $tripDetails->start_longitude,
            'drop_latitude'       => $tripDetails->drop_latitude,
            'drop_longitude'       => $tripDetails->drop_longitude
            ]);
        }

        /*Emergency details data destroy*/
        public function delete($id){
          $tripDetails = Booking::where('id',$id)->first();
          $tripDetails->delete();
          Notify::success('Booking deleted successfully.');
          return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Booking Details removed successfully.'
            ]);
        }
      }