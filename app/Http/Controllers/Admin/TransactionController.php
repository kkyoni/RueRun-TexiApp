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
use App\Models\TransactionDetail;
use App\Models\Booking;
use Event;
use Illuminate\Http\Request;
use App\Models\Setting;
use Carbon\Carbon;
use Yajra\DataTables\Services\DataTable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class TransactionController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.transaction_detail.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['transaction-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $transaction = TransactionDetail::with(['userDetail','tripDetail'])->where('booking_id','!=', null)->orWhere('parcel_id','!=', null)->where('status','complete')->orderBy('updated_at','desc');
        if(!empty($request->filterby) && !empty($request->filterby_end)){
            $type =  Carbon::createFromFormat('m/d/Y', $request->filterby)->format('Y-m-d')." 00:00:00";
            $type_2 = Carbon::createFromFormat('m/d/Y', $request->filterby_end)->format('Y-m-d')." 23:59:59";
            $transaction->whereBetween('created_at', [$type,$type_2]);
        }
        if (request()->ajax()) {
            return DataTables::of($transaction->get())
            ->addIndexColumn()
            ->editColumn('userDetail.first_name', function (TransactionDetail $transaction) {
                return $transaction->userDetail->first_name.' '.$transaction->userDetail->last_name;
            })
            ->editColumn('amount', function (TransactionDetail $transaction) {
                return '$'.$transaction->amount;
            })
            ->editColumn('tripDetail.base_fare', function (TransactionDetail $transaction) {
                if(!empty($transaction->tripDetail)){
                    return '$'.$transaction->tripDetail->base_fare;
                }else if(!empty($transaction->parcelDetail)){
                    return '$'.$transaction->parcelDetail->base_fare;
                }
            })
            ->editColumn('tripDetail.total_km', function (TransactionDetail $transaction) {
                if(!empty($transaction->tripDetail)){
                    return '$'.$transaction->tripDetail->total_km;
                }else if(!empty($transaction->parcelDetail)){
                    return '$'.$transaction->parcelDetail->total_km;
                }
            })
            ->addColumn('tripDetail.trip_status', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return ucfirst(@$transaction->tripDetail->trip_status);
                }else if($transaction->parcel_id){
                    return ucfirst(@$transaction->parcelDetail->parcel_status);
                }
            })
            ->editColumn('total_amount', function (TransactionDetail $transaction) {
                if(!empty($transaction->tripDetail)){
                    return '$'.$transaction->tripDetail->total_amount;
                }else if(!empty($transaction->parcelDetail)){
                    return '$'.$transaction->parcelDetail->total_amount;
                }
            })
            ->editColumn('tripDetail.pick_up_location', function (TransactionDetail $transaction) {
                if(!empty($transaction->tripDetail)){
                    return strip_tags(str_limit($transaction->tripDetail->pick_up_location, $limit = 50, $end = '...'));
                }else if(!empty($transaction->parcelDetail)){
                    return strip_tags(str_limit($transaction->parcelDetail->pick_up_location, $limit = 50, $end = '...'));
                }
            })
            ->editColumn('tripDetail.drop_location', function (TransactionDetail $transaction) {
                if(!empty($transaction->tripDetail)){
                    return strip_tags(str_limit($transaction->tripDetail->drop_location, $limit = 50, $end = '...'));
                }else if(!empty($transaction->parcelDetail)){
                    return strip_tags(str_limit($transaction->parcelDetail->drop_location, $limit = 50, $end = '...'));
                }
            })
            ->editColumn('userDetail.uuid', function (TransactionDetail $transaction) {
                return $transaction->userDetail->uuid;
            })
            ->editColumn('promoDetail.promo_code', function (TransactionDetail $transaction) {
                if(!empty($transaction->promoDetail))
                    return $transaction->promoDetail->promo_code;
            })
            ->editColumn('tripDetail.ride_setting.name', function (TransactionDetail $transaction) {
                if(!empty($transaction->tripDetail)){
                    return $transaction->tripDetail->ride_setting->name;
                }else if(!empty($transaction->parcelDetail)){
                    return $transaction->parcelDetail->ride_setting->name;
                }
            })
            ->editColumn('userDetail.user_type', function (TransactionDetail $transaction) {
                if(!empty($transaction->userDetail))
                    return $transaction->userDetail->user_type;
            })
            ->editColumn('action', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return '<a href="javascript::void(0);" title="View" class="btn btn-info btn-sm get_trnsection_details ml-1" data-id ="'.$transaction->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
                }else if($transaction->parcel_id){
                    return '<a href="javascript::void(0);" title="View" class="btn btn-info btn-sm ml-1 get_parcel_details" data-id ="'.$transaction->id.'" href="javascript:void(0)"><i class="fa fa-eye "></i></a>';
                }
            })
            ->editColumn('booking_type', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return "Ride Booking";
                }else if($transaction->parcel_id){
                    return "Parcel Booking";
                }
            })
            ->rawColumns(['avatar','amount','userDetail.doc_status','transaction_company','total_amount','action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'userDetail.first_name', 'name'    => 'userDetail.first_name', 'title' => 'User Name','width'=>'5%'],
            ['data' => 'total_amount', 'name'    => 'total_amount', 'title' => 'Total Amount','width'=>'5%'],
            ['data' => 'tripDetail.trip_status', 'name' => 'tripDetail.trip_status', 'title' => 'Trip Status','width'=>'5%'],
            ['data' => 'booking_type', 'name' => 'booking_type', 'title' => 'Booking Type','width'=>'5%'],
            ['data' => 'tripDetail.pick_up_location', 'name' => 'tripDetail.pick_up_location', 'title' => 'Pickup Location','width'=>'5%'],
            ['data' => 'tripDetail.drop_location', 'name' => 'tripDetail.drop_location', 'title' => 'Drop Location','width'=>'5%'],
            ['data' => 'userDetail.uuid', 'name' => 'userDetail.uuid', 'title' => 'Refer Code','width'=>'5%'],
            ['data' => 'tripDetail.ride_setting.name', 'name' => 'tripDetail.ride_setting.name', 'title' => 'Service Type','width'=>'5%'],
            ['data' => 'userDetail.user_type', 'name' => 'userDetail.user_type', 'title' => 'Member','width'=>'5%'],
            ['data' => 'action', 'name'    => 'action', 'title' => 'Action','width'=>'3%'],
            ])
        // ->ajax([
        //     'url' => route('admin.transaction_detail.filter_by'),
        //     'type' => 'POST',
        //     'data' => 'function(d) {
        //         d._token = "'.csrf_token().'";
        //         d.filterby = $("#start_date").val();
        //         d.filterby_end = $("#end_date").val();
        //     }',
        // ])
        ->parameters([ 'order' =>[] ]);
        $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
        \View::share('roles',$roles);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function transaction_info($id){
        $transactiondetail = TransactionDetail::with(['tripDetail','userDetail'])->where('id',$id)->first();
        $house_edge = Setting::where('code','house_edge')->first();
        
        $total_per_day = $transactiondetail->amount * $house_edge->value /100;
        // dd($total_per_day);
        $promocode='';
        if(@$transactiondetail->promo_id){
            $promocode = @$transactiondetail->promoDetail->promo_code;
        }else{
            $promocode = 'No Promocode Found';
        }
        return response()->json([
            'amount'                => @$transactiondetail->amount,
            'trip_id'               => @$transactiondetail->booking_id,
            'user_id'               => @$transactiondetail->user_id,
            'promo_id'              => @$promocode,
            'admin_commision'       => @$total_per_day,
            'pick_up_location'       => @$transactiondetail->tripDetail->pick_up_location,
            'drop_location'       => @$transactiondetail->tripDetail->drop_location,
            'booking_date'       => date("m-d-Y", strtotime(@$transactiondetail->tripDetail->booking_date)),
            'booking_end_time'       => @$transactiondetail->tripDetail->booking_end_time,
            'user_name'       => @$transactiondetail->userDetail->first_name.' '.@$transactiondetail->userDetail->last_name,
            'driver_name'       => @$transactiondetail->tripDetail->driver->first_name.' '.@$transactiondetail->tripDetail->driver->last_name,
            'total_km'       => @$transactiondetail->tripDetail->total_km,
            'start_date'       => \Carbon\Carbon::parse(@$transactiondetail->tripDetail->created_at)->format('m-d-Y') ,
            'start_time'       => @$transactiondetail->tripDetail->start_time,
            'end_time'       => @$transactiondetail->tripDetail->end_time,
            ]);
    }

    public function get_parcel_details($id){
        $transactiondetail = TransactionDetail::with(['tripDetail','userDetail','parcelDetail'])->where('id',$id)->first();
        $promocode='';
        if(@$transactiondetail->promo_id){
            $promocode = @$transactiondetail->promoDetail->promo_code;
        }else{
            $promocode = 'No Promocode Found';
        }
        return response()->json([
            'amount'                => @$transactiondetail->amount,
            'trip_id'               => @$transactiondetail->booking_id,
            'user_id'               => @$transactiondetail->user_id,
            'promo_id'              => @$promocode,
            'admin_commision'       => @$transactiondetail->parcelDetail->admin_commision,
            'pick_up_location'       => @$transactiondetail->parcelDetail->pick_up_location,
            'drop_location'       => @$transactiondetail->parcelDetail->drop_location,
            'booking_date'       => @$transactiondetail->parcelDetail->booking_date,
            'booking_end_time'       => @$transactiondetail->parcelDetail->booking_end_time,
            'user_name'       => @$transactiondetail->userDetail->first_name.' '.@$transactiondetail->userDetail->last_name,
            'driver_name'       => @$transactiondetail->parcelDetail->driver->first_name.' '.@$transactiondetail->parcelDetail->driver->last_name,
            'total_km'       => @$transactiondetail->parcelDetail->total_distance,
            'start_date'       => \Carbon\Carbon::parse(@$transactiondetail->parcelDetail->created_at)->format('Y-m-d') ,
            'start_time'       => @$transactiondetail->parcelDetail->start_time,
            'end_time'       => @$transactiondetail->parcelDetail->end_time,
            ]);
    }

    public function reports_index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['reports-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        
        $transaction = TransactionDetail::with(['userDetail','tripDetail','parcelDetail'])->where('booking_id','!=', null)->orWhere('parcel_id','!=', null)->orderBy('updated_at','desc');
        if (request()->ajax()) {
            return DataTables::of($transaction->get())
            ->addIndexColumn()
            ->editColumn('action', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return '<a href="javascript::void(0);" title="View" class="btn btn-info btn-sm get_trnsection_details ml-1" data-id ="'.$transaction->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
                }else if($transaction->parcel_id){
                    return '<a href="javascript::void(0);" title="View" class="btn btn-info btn-sm get_parcel_details ml-1" data-id ="'.$transaction->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a>';
                }
            })
            ->addColumn('tripDetail.ride_setting_id', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return @$transaction->tripDetail->ride_setting->name;
                }else if($transaction->parcel_id){
                    return @$transaction->parcelDetail->ride_setting->name;
                }
            })
            ->addColumn('tripDetail.booking_date', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return @$transaction->tripDetail->booking_date.' '.@$transaction->tripDetail->start_time;
                }else if($transaction->parcel_id){
                    return @$transaction->parcelDetail->booking_date.' '.@$transaction->parcelDetail->start_time;
                }
            })
            ->addColumn('tripDetail.booking_start_time', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return @$transaction->tripDetail->booking_start_time;
                }else if($transaction->parcel_id){
                    return @$transaction->parcelDetail->booking_start_time;
                }
            })
            ->addColumn('tripDetail.trip_status', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return ucfirst(@$transaction->tripDetail->trip_status);
                }else if($transaction->parcel_id){
                    return ucfirst(@$transaction->parcelDetail->parcel_status);
                }
            })
            ->addColumn('year', function (TransactionDetail $transaction) {
                if($transaction->booking_id){
                    return Carbon::parse(@$transaction->tripDetail->booking_date)->format('Y');;
                }else if($transaction->parcel_id){
                    return Carbon::parse(@$transaction->parcelDetail->booking_date)->format('Y');;
                }
            })
            ->rawColumns(['booking_id','tripDetail.ride_setting_id','tripDetail.booking_date','tripDetail.booking_date','tripDetail.booking_start_time','year','action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'tripDetail.ride_setting_id', 'name'=> 'tripDetail.ride_setting_id', 'title' => 'Service Type','width'=>'15%'],
            ['data' => 'tripDetail.booking_date', 'name' => 'tripDetail.booking_date', 'title' => 'Date & Time','width'=>'15%'],
            ['data' => 'tripDetail.trip_status', 'name' => 'tripDetail.trip_status', 'title' => 'Status','width'=>'15%'],
            ['data' => 'year', 'name' => 'year', 'title' => 'Year','width'=>'5%', 'visible'=>false],
            ['data' => 'action', 'name'    => 'action', 'title' => 'Action','width'=>'8%'],
            ])
        ->parameters([
            'order' =>[],
            'paging'      => true,
            'info'        => true,
            'searchDelay' => 350,
            'dom'         => 'lBfrtip',
            'buttons'     => [
            ['extend' => 'print','title' => "Transaction Report", 'text' => '<i class="fa fa-print" aria-hidden="true" style="font-size:16px"></i>','exportOptions' => ['columns'=> [0,1,2,3]]],
            ['extend' => 'excel','title' => "Transaction Report", 'text' => '<i class="fa fa-file-excel-o" aria-hidden="true" style="font-size:16px"></i>','exportOptions' => ['columns'=> [0,1,2,3]]],
            ['extend' => 'pdf','title' => "Transaction Report", 'text' => '<i class="fa fa-file-pdf-o" aria-hidden="true" style="font-size:16px"></i>','exportOptions' => ['columns'=> [0,1,2,3]]],
            ],
            'searching'   => true,
            ]);
        return view($this->pageLayout.'reports_index',compact('html'));
    }

    public function export(){
        $transaction = TransactionDetail::with(['userDetail','tripDetail'])->orderBy('updated_at','desc');
        return Excel::download(new UsersExport, 'reports.xlsx');
    }
}