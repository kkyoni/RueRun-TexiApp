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
use App\Models\Booking;
use Event;
use Illuminate\Http\Request;
use Settings;
use Carbon\Carbon;
use Yajra\DataTables\Services\DataTable;
use App\Models\ReferWallets;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class StarRatingController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/

    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.star_detail.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request) {
        $userRole = '';
        $userRole = Helper::checkPermission(['driver-star-rating-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $user = User::withCount('bookingDriverDetail')->where('user_type','driver')->orderBy('id','DESC')->get()->where('totalRat','!=','0');
        if (request()->ajax()) {
            return DataTables::of($user)
            ->addIndexColumn()
            ->editColumn('first_name', function (User $user) {
                return $user->first_name.' '.$user->last_name.'&nbsp;<a class="m-l-10 driver_show" title="View" data-id="'.$user->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
            })
            ->editColumn('totalKm', function (User $user) {
                return $user->totalKm;
            })
            ->editColumn('booking_driver_detail_count', function (User $user) {
                return $user->booking_driver_detail_count;
            })
            ->editColumn('totalRat', function (User $user) {
                return $user->totalRat;
            })
            ->rawColumns(['action','first_name'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'first_name','name' => 'first_name','title' =>'User Name','width'=>'30%'],
            ['data' => 'totalKm', 'name' => 'totalKm', 'title' => 'Total Km','width'=>'30%'],
            ['data' => 'booking_driver_detail_count', 'name' => 'booking_driver_detail_count', 'title' => 'Total Ride','width'=>'30%'],
            ['data' => 'totalRat', 'name' => 'totalRat', 'title' => 'Total Rating','width'=>'30%'],
        ])
        ->parameters(['order' =>[[ 2, "desc" ],[ 3, "desc" ],[ 4, "desc" ]]]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function user_index(Builder $builder, Request $request) {
        $userRole = '';
        $userRole = Helper::checkPermission(['user-star-rating-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $user = User::withCount('bookingUserDetail')->where('user_type','user')->orderBy('id','DESC')->get()->where('totalRat','!=','0');
        if (request()->ajax()) {
            return DataTables::of($user)
            ->addIndexColumn()
            ->editColumn('first_name', function (User $user) {
                return $user->first_name.' '.$user->last_name.'&nbsp;<a title="View" class="m-l-10 user_show "  data-id="'.$user->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
            })
            ->editColumn('uuid', function (User $user) {
                return $user->uuid;
            })
            ->editColumn('userTotalKm', function (User $user) {
                return $user->userTotalKm;
            })
            ->editColumn('booking_user_detail_count', function (User $user) {
                return $user->booking_user_detail_count;
            })
            ->editColumn('totalRat', function (User $user) {
                return $user->totalRat;
            })
            ->rawColumns(['action','first_name'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'first_name','name' => 'first_name','title' =>'User Name','width'=>'30%'],
            ['data' => 'userTotalKm', 'name' => 'userTotalKm', 'title' => 'Total Km','width'=>'30%'],
            ['data' => 'booking_user_detail_count', 'name' => 'booking_user_detail_count', 'title' => 'Total Ride','width'=>'30%'],
            ['data' => 'totalRat', 'name' => 'totalRat', 'title' => 'Total Rating','width'=>'30%'],
        ])
        ->parameters(['order' =>[[ 2, "desc" ],[ 3, "desc" ],[ 4, "desc" ]]]);
        return view($this->pageLayout.'user_index',compact('html'));
    }

    public function transaction_info($id){
        $transactiondetail = TransactionDetail::with(['tripDetail','userDetail'])->where('id',$id)->first();
        return response()->json([
            'amount'                => @$transactiondetail->amount,
            'trip_id'               => @$transactiondetail->booking_id,
            'user_id'               => @$transactiondetail->user_id,
            'promo_id'              => @$transactiondetail->promo_id,
            'admin_commision'       => @$transactiondetail->tripDetail->admin_commision,
            'pick_up_location'       => @$transactiondetail->tripDetail->pick_up_location,
            'drop_location'       => @$transactiondetail->tripDetail->drop_location,
            'booking_date'       => @$transactiondetail->tripDetail->booking_date,
            'booking_end_time'       => @$transactiondetail->tripDetail->booking_end_time,
            'user_name'       => @$transactiondetail->userDetail->first_name.' '.@$transactiondetail->userDetail->last_name,
            'driver_name'       => @$transactiondetail->tripDetail->driver->first_name.' '.@$transactiondetail->tripDetail->driver->last_name,
            'total_km'       => @$transactiondetail->tripDetail->total_km,
        ]);
    }

    public function reports_index(Builder $builder, Request $request){
        $transaction = TransactionDetail::with(['userDetail','tripDetail'])->orderBy('updated_at','desc');
        if (request()->ajax()) {
            return DataTables::of($transaction->get())
            ->addIndexColumn()
            ->editColumn('action', function (TransactionDetail $transaction) {
                return '<a href="#" class="get_trnsection_details" data-id ="'.$transaction->id.'" href="javascript:void(0)"><i class="fa fa-eye "></i></a>';
            })
            ->addColumn('tripDetail.ride_setting_id', function (TransactionDetail $transaction) {
                return $transaction->tripDetail->ride_setting->name;
            })
            ->addColumn('tripDetail.booking_date', function (TransactionDetail $transaction) {
                return $transaction->tripDetail->booking_date;
            })
            ->addColumn('tripDetail.booking_start_time', function (TransactionDetail $transaction) {
                return $transaction->tripDetail->booking_start_time;
            })
            ->addColumn('tripDetail.trip_status', function (TransactionDetail $transaction) {
                return ucfirst($transaction->tripDetail->trip_status);
            })
            ->addColumn('year', function (TransactionDetail $transaction) {
                return Carbon::parse($transaction->tripDetail->booking_date)->format('Y');;
            })
            ->rawColumns(['booking_id','tripDetail.ride_setting_id','tripDetail.booking_date','tripDetail.booking_date','tripDetail.booking_start_time','year','action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'booking_id', 'name' => 'booking_id', 'title' => 'Booking Id','width'=>'5%'],
            ['data' => 'tripDetail.ride_setting_id', 'name'=> 'tripDetail.ride_setting_id', 'title' => 'Service Type','width'=>'15%'],
            ['data' => 'tripDetail.booking_date', 'name' => 'tripDetail.booking_date', 'title' => 'Booking Date','width'=>'15%'],
            ['data' => 'tripDetail.booking_start_time', 'name' => 'tripDetail.booking_start_time', 'title' => 'Time','width'=>'15%'],
            ['data' => 'year', 'name' => 'year', 'title' => 'Year','width'=>'5%'],
            ['data' => 'action', 'name'    => 'action', 'title' => 'Action','width'=>'8%'],
        ])
        ->parameters([
            'order' =>[],
            'paging'      => true,
            'info'        => true,
            'responsive'  => true,
            'searchDelay' => 30,
            'dom'         => 'Bfrtip',
            'buttons'     => [['extend' => 'excel','title' => "Revenue Report", 'text' => 'Export','exportOptions' => ['columns'=> [0,1,2,3,4]]],
            ['extend' => 'pdf','title' => "Revenue Report", 'text' => 'PDF','exportOptions' => ['columns'=> [0,1,2,3,4]]],
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