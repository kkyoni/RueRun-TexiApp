<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\ParcelDetail;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use App\Models\RatingReviews;
use App\Models\Booking;
use Auth,Str;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;
class ReviewratingController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.review_rating.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['review-rating-list','review-rating-delete']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $permission_data['hasRemovePermission'] = Helper::checkPermission(['review-rating-delete']);

        $ratingreviews = RatingReviews::with('from_user','to_user')->orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($ratingreviews->get())
            ->addIndexColumn()
            ->editColumn('action', function(RatingReviews $ratingreviews) use($permission_data) {
                $action='';
                if($permission_data['hasRemovePermission']){
                    $action .= '<a title="Delete" class="btn btn-danger btn-sm ml-1 deleteratingreviews" data-id ="'.$ratingreviews->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                }
                return $action;
            })
            ->editColumn('from_user_id', function(RatingReviews $ratingreviews) {
                return'<lable>'.@$ratingreviews->from_user->first_name.' '.@$ratingreviews->from_user->last_name.' - '.@$ratingreviews->from_user->user_type.'</lable>';
            })
            ->editColumn('to_user_id', function(RatingReviews $ratingreviews) {
                return'<lable>'.@$ratingreviews->to_user->first_name.' '.@$ratingreviews->to_user->last_name.' - '.@$ratingreviews->to_user->user_type.'</lable>';
            })
            ->editColumn('booking_id', function(RatingReviews $ratingreviews) {
                if($ratingreviews->booking_id){
                    return'<a href="javascript:void(0)" data-value="1" title="View" class="trip_info" data-toggle="modal" data-id="'.$ratingreviews->booking_id.'"><i class="fa fa-eye"></i></a>';
                }else if($ratingreviews->parcel_id){
                    return'<a href="javascript:void(0)" data-value="1" title="View" class="parcel_trip_info" data-toggle="modal" data-id="'.$ratingreviews->parcel_id.'"><i class="fa fa-eye"></i></a>';
                }else{
                    return '-';
                }
            })
            ->editColumn('status', function(RatingReviews $ratingreviews) {
                $html='<select class="form-control revie_status approvebtn" data-id="'.$ratingreviews->id.'" id="approve_review_'.$ratingreviews->id.'">';
                if($ratingreviews->status=='pending'){
                    $html.='<option value="pending" selected>Pending</option>';
                    $html.='<option value="approved">Approved</option>';
                    $html.='<option value="rejected">Rejected</option>';
                }elseif($ratingreviews->status=='approved'){
                    $html.='<option value="pending">Pending</option>';
                    $html.='<option value="approved" selected>Approved</option>';
                    $html.='<option value="rejected">Rejected</option>';
                }elseif($ratingreviews->status=='rejected'){
                    $html.='<option value="pending">Pending</option>';
                    $html.='<option value="approved">Approved</option>';
                    $html.='<option value="rejected" selected>Rejected</option>';
                }else{
                    $html.='<option value="pending">Pending</option>';
                    $html.='<option value="approved">Approved</option>';
                    $html.='<option value="rejected">Rejected</option>';
                }
                $html.='</select>';
                return $html;
            })
            ->rawColumns(['action','from_user_id','to_user_id','booking_id','status'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'3%',"orderable" => false, "searchable" => false],
            ['data' => 'from_user_id', 'name'    => 'from_user_id', 'title' => 'Rating From','width'=>'15%'],
            ['data' => 'to_user_id', 'name'    => 'to_user_id', 'title' => 'Rating To','width'=>'15%'],
            ['data' => 'rating', 'name'    => 'rating', 'title' => 'Rating','width'=>'15%'],
            ['data' => 'booking_id', 'name'=> 'booking_id', 'title'=> 'Booking details','width'=>'3%'],
            ['data' => 'status', 'name' => 'status','title' => 'Approve Status','width'=>'5%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'3%',"orderable" => false],
            ])
        ->parameters(['order' =>[],]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function delete($id){
        $userRole = Helper::checkPermission(['review-rating-delete']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $ratingreviews = RatingReviews::where('id',$id)->first();
        $ratingreviews->delete();
        Notify::success('Rating reviews deleted successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Rating reviews removed successfully.'
            ]);
    }

    public function trip_info($id){
        $tripDetails = Booking::with('driver','user')->where('id',$id)->first();
        $ratingreviews = RatingReviews::where('booking_id',$tripDetails->id)->first();
        return response()->json([
            'driver'       => @$tripDetails['driver']->first_name,
            'driver_id'       => @$tripDetails['driver']->id,
            'user'       => @$tripDetails['user']->first_name,
            'user_id'       => @$tripDetails['user']->id,
            'pick_up_location'       => @$tripDetails->pick_up_location,
            'drop_location'       => @$tripDetails->drop_location,
            'start_time'       => @$tripDetails->start_time,
            'end_time' => @$tripDetails->end_time,
            'hold_time' => @$tripDetails->hold_time,
            'base_fare' => @$tripDetails->base_fare,
            'total_km' => @$tripDetails->total_km,
            'admin_commision' => @$tripDetails->admin_commision,
            'transaction_id'  => @$tripDetails->transaction_id,
            'trip_status'  => @$tripDetails->trip_status,
            'extra_notes'  => @$tripDetails->extra_notes,
            'promo_name'  => @$tripDetails->promo_name,
            'promo_amount'  => @$tripDetails->promo_amount,
            'promo_id'  => @$tripDetails->promo_id,
            'total_amount'  => @$tripDetails->total_amount,
            'comment' => $ratingreviews->comment,
            'booking_date'  => date("m-d-Y", strtotime($tripDetails->booking_date))
            ]);
    }

    public function get_parcel_details($id){
        $tripDetails = ParcelDetail::with('driver','user')->where('id',$id)->first();
        $ratingreviews = RatingReviews::where('parcel_id',$tripDetails->id)->first();
        return response()->json([
            'driver'            => @$tripDetails['driver']->first_name,
            'driver_id'         => @$tripDetails['driver']->id,
            'user'              => @$tripDetails['user']->first_name,
            'user_id'           => @$tripDetails['user']->id,
            'pick_up_location'  => @$tripDetails->pick_up_location,
            'drop_location'     => @$tripDetails->drop_location,
            'start_time'        => @$tripDetails->start_time,
            'end_time'          => @$tripDetails->end_time,
            'hold_time'         => @$tripDetails->hold_time,
            'base_fare'         => @$tripDetails->base_fare,
            'total_km'          => @$tripDetails->total_distance,
            'admin_commision'   => @$tripDetails->admin_commision,
            'transaction_id'    => @$tripDetails->transaction_id,
            'trip_status'       => @$tripDetails->parcel_status,
            'extra_notes'       => @$tripDetails->extra_notes,
            'promo_name'        => @$tripDetails->promo_name,
            'promo_amount'      => @$tripDetails->promo_amount,
            'promo_id'          => @$tripDetails->promo_id,
            'total_amount'      => @$tripDetails->total_amount,
            'comment'           => $ratingreviews->comment,
            'booking_date'      => $tripDetails->booking_date
            ]);
    }

    public function revie_status(Request $request){
        $ratingreviews = RatingReviews::where('id',$request->id)->first();
        $rating = floatval($ratingreviews->rating);
        if($request->status == "pending"){
            RatingReviews::where('id',$request->id)->update(['status'  => "pending",]);
        }if($request->status == "approved"){
            RatingReviews::where('id',$request->id)->update([ 'status'=> "approved",]);
            if($rating <=2){
                $bookingDetail = Booking::find($ratingreviews->booking_id);
                $driverDetail = User::find($bookingDetail->driver_id);
                $driverDetail->status = 'inactive';
                $driverDetail->reason_for_inactive = 'Inactive due to low rating';
                $driverDetail->save();
            }
        }
        if($request->status == "rejected"){
            RatingReviews::where('id',$request->id)->update([ 'status'=> "rejected",]);
        }
        Notify::success('Status updated Successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Status updated successfully.'
            ]);
    }

    public function driver_info($id){
        $tripDetails = User::where('id',$id)->first();
        return response()->json([
            'first_name'       => @$tripDetails->first_name,
            'email'       => @$tripDetails->email,
            'contact_number'       => @$tripDetails->contact_number
            ]);
    }

    public function user_info($id){
        $tripDetails = User::where('id',$id)->first();
        return response()->json([
            'first_name'       => @$tripDetails->first_name,
            'email'       => @$tripDetails->email,
            'contact_number'       => @$tripDetails->contact_number
            ]);
    }
}