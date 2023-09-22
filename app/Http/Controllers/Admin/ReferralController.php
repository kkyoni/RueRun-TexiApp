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

class ReferralController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.referral_detail.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request) {
        $userRole = '';
        $userRole = Helper::checkPermission(['driver-referral-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $user = User::withCount('referralCount')->where('user_type','driver')->orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($user->get())
            ->addIndexColumn()
            ->editColumn('first_name', function (User $user) {
                if($user->first_name){
                    return $user->first_name.' '.$user->last_name.'&nbsp;<a title="View" class="m-l-10 driver_show "  data-id="'.$user->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
                }else if($user->company_name){
                    return $user->company_name.'&nbsp;<a title="View" class="m-l-10 driver_show "  data-id="'.$user->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
                }else{
                    return '-';
                }
            })
            ->editColumn('uuid', function (User $user) {
                return $user->uuid;
            })
            ->editColumn('referral_count_count', function (User $user) {
                return $user->referral_count_count;
            })
            ->editColumn('action', function (User $user) {
                $action = '';
                $action .= '<a title="View" class="btn btn-info btn-sm get_ref_detail ml-1" href="javascript::void(0);" data-id="'.$user->uuid.'"><i class="fa fa-eye"></i></a>';
                return $action;
            })
            ->rawColumns(['action','first_name'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'first_name','name' => 'first_name','title' =>'User Name','width'=>'30%'],
            ['data' => 'uuid', 'name' => 'uuid', 'title' => 'Referral Code','width'=>'30%'],
            ['data' => 'referral_count_count', 'name' => 'referral_count_count', 'title' => 'Referral Code Count','width'=>'30%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'30%',"orderable" => false, "searchable" => false],
        ])->parameters(['order' =>[]]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function referral_info($id) {
        $data = User::where('ref_id',$id)->get();
        $userData = User::where('uuid',$id)->first();
        $html = view($this->pageLayout.'modal',compact('data','userData'))->render();
        return response()->json(['status'=>'success','html'=>$html],200);
    }

    public function earn_info($id) {
        $data = ReferWallets::with('userDetail')->where('refer_id',$id)->get();
        $html = view($this->pageLayout.'earn_modal',compact('data'))->render();
        return response()->json(['status'=>'success','html'=>$html],200);
    }

    public function user_index(Builder $builder, Request $request) {
        $userRole = Helper::checkPermission(['user-referral-list']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $user = User::withCount('referralCount')->where('user_type','user')->orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($user->get())
            ->addIndexColumn()
            ->editColumn('first_name', function (User $user) {
                 if($user->first_name){
                     return $user->first_name.' '.$user->last_name.'&nbsp;<a title="View" class="m-l-10 user_show "  data-id="'.$user->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
                 }else if($user->company_name){
                     return $user->company_name.'&nbsp;<a title="View" class="m-l-10 user_show "  data-id="'.$user->id.'" href="javascript:void(0)"><i class="fa fa-eye"></i></a> ';
                 }else{
                     return '-';
                 }
            })
            ->editColumn('uuid', function (User $user) {
                return $user->uuid;
            })
            ->editColumn('referral_count_count', function (User $user) {
                return $user->referral_count_count;
            })
            ->editColumn('action', function (User $user) {
                $action = '';
                $action .= '<a title="View" class="btn btn-info btn-sm get_ref_detail ml-1" href="javascript::void(0);" data-id="'.$user->uuid.'"><i class="fa fa-eye"></i></a>';
                return $action;
            })
            ->rawColumns(['action','first_name'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'first_name','name' => 'first_name','title' =>'User Name','width'=>'30%'],
            ['data' => 'uuid', 'name' => 'uuid', 'title' => 'Referral Code','width'=>'30%'],
            ['data' => 'referral_count_count', 'name' => 'referral_count_count', 'title' => 'Referral Code Count','width'=>'30%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'30%',"orderable" => false, "searchable" => false],

            ])->parameters(['order' =>[]]);
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
                return Carbon::parse($transaction->tripDetail->booking_date)->format('Y');
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
            'buttons'     => [
                ['extend' => 'excel','title' => "Revenue Report", 'text' => 'Export','exportOptions' => ['columns'=> [0,1,2,3,4]]],
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

    public function get_user_info_data($id){
        $tripDetails = User::where('id',$id)->first();
        $firstname=$lastname='';
        if($tripDetails->first_name){
            $firstname =  $tripDetails->first_name;
            $lastname =  $tripDetails->last_name;
        }else if($tripDetails->company_name){
            $firstname =  $tripDetails->company_name;
            $lastname =  $tripDetails->company_name;
        }else{
            $firstname =  '-';
            $lastname =  '-';
        }
        return response()->json([
            'first_name'       => $firstname,
            'email_add'       => $tripDetails->email,
            'gender'       => $tripDetails->gender,
            'last_name'       => $lastname,
            'status'       => $tripDetails->status,
            'contact_number' => $tripDetails->contact_number
        ]);
    }

    public function get_driver_info_data($id){
        $tripDetails = User::where('id',$id)->first();
        $firstname=$lastname='';
        if($tripDetails->first_name){
            $firstname =  $tripDetails->first_name;
            $lastname =  $tripDetails->last_name;
        }else if($tripDetails->company_name){
            $firstname =  $tripDetails->company_name;
            $lastname =  $tripDetails->company_name;
        }else{
            $firstname =  '-';
            $lastname =  '-';
        }
        return response()->json([
            'first_name'       => $firstname,
            'email_add'       => $tripDetails->email,
            'gender'       => $tripDetails->gender,
            'last_name'       => $lastname,
            'status'       => $tripDetails->status,
            'contact_number' => $tripDetails->contact_number
        ]);
    }
}