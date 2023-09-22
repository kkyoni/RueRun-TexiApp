<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\JoinNetwork;
use App\Models\User;
use App\Models\Booking;
use App\Models\InviteContactList;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Auth;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;
use App\Helpers\Helper;
use DB;
use Carbon\Carbon;

class JoinNetworkController extends Controller{
  protected $authLayout = '';
  protected $pageLayout = '';
  

  public function __construct(){
    $this->authLayout = 'admin.auth.';
    $this->pageLayout = 'admin.pages.join_network.';
    $this->middleware('auth');
  }

  public function index(Builder $builder,Request $request)
  {
    $joinNtwk = JoinNetwork::groupBy('ref_id');
      if (request()->ajax()) {
            return DataTables::of($joinNtwk->get())
            ->addIndexColumn()
            ->editColumn('action', function (JoinNetwork $joinNtwk) {
              $user = User::withCount('referralCount')->where('user_type','driver')->where('id',$joinNtwk->ref_id)->get();
                $action = '';
                $action .= '<a title="View" class="btn btn-info btn-sm get_ref_detail ml-1" href="javascript::void(0);" data-id="'.$user[0]->uuid.'"><i class="fa fa-eye"></i></a>';
                return $action;
            })
            ->editColumn('first_name', function (JoinNetwork $joinNtwk) {
                $name = User::where('id',$joinNtwk->ref_id)->get();
              return $name[0]->first_name;
            })
             ->editColumn('total_earning', function (JoinNetwork $joinNtwk) {
                $wl = $joinNtwk->total_earning;

                $a = $wl * 20/100 ;
                    // $val = 100*20/100  ;

                   // $total_amount_of_earning = $a + $val;
                
                // $total = 20 ;
                if ($wl != 0){
                  return $a ;  
                }else{
                  return $percent = 0;
                }
                
            })
            // SELECT sum(total_earning) FROM `join_network` WHERE `ref_id` = 52
            ->rawColumns(['action','first_name'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'first_name','name' => 'first_name','title' =>'User Name','width'=>'30%'],
            ['data' => 'total_earning', 'name' => 'status', 'title' => 'Total earning','width'=>'20%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'20%',"orderable" => false, "searchable" => false],
        ])->parameters(['order' =>[]]);
        return view($this->pageLayout.'index',compact('html'));  
  }

  public function joinreferral_info($id) {
    // dd($id);
    $data = User::where('ref_id',$id)->get();
    // dd($data);
    $userData = User::where('uuid',$id)->first();
    $html = view($this->pageLayout.'modal',compact('data','userData'))->render();
    return response()->json(['status'=>'success','html'=>$html],200);
  }

  public function earn_info($id) {
    $data = ReferWallets::with('userDetail')->where('refer_id',$id)->get();
    $html = view($this->pageLayout.'earn_modal',compact('data'))->render();
    return response()->json(['status'=>'success','html'=>$html],200);
  }

}