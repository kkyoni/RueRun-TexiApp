<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use App\Models\Promocodes;
use Auth;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;

class PromocodeManagementController extends Controller{
  protected $authLayout = '';
  protected $pageLayout = '';
  /*** Create a new controller instance.** @return void*/
  public function __construct(){
    $this->authLayout = 'admin.auth.';
    $this->pageLayout = 'admin.pages.promocode.';
    $this->middleware('auth');
  }

  public function index(Builder $builder, Request $request){
    $promocodes = Promocodes::orderBy('updated_at','DESC');
    if (request()->ajax()) {
      return DataTables::of($promocodes->get())
      ->addIndexColumn()
      ->editColumn('action', function(Promocodes $promocodes) {
        $action  = '';
        $action .= '<a title="Edit" href='.route('admin.promocode.edit',[$promocodes->id]).' class="btn btn-warning btn-sm ml-1"><i class="fa fa-pencil"></i></a><a title="Delete" class="ml-1 btn btn-danger btn-sm deletepromocode" data-id ="'.$promocodes->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        if($promocodes->status == "active"){
          $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$promocodes->id.'"><i class="fa fa-unlock"></i></a>';
        }else{
          $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$promocodes->id.'"><i class="fa fa-lock" ></i></a>';
        }
        return $action;
      })
      ->editColumn('status', function(Promocodes $promocodes) {
        if ($promocodes->status == "active") {
          return '<span class="label label-success">Active</span>';
        } else {
          return '<span class="label label-danger">Block</span>';
        }
      })
      ->rawColumns(['status','action'])
      ->make(true);
    }
    $html = $builder->columns([
      ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
      ['data' => 'promo_code', 'name'    => 'promo_code', 'title' => 'Promocode','width'=>'10%'],
      ['data' => 'amount', 'name'    => 'amount', 'title' => 'Amount (in %)','width'=>'10%'],
      ['data' => 'start_date', 'name'    => 'start_date', 'title' => 'Start Date','width'=>'10%'],
      ['data' => 'end_date', 'name'    => 'end_date', 'title' => 'End Date','width'=>'10%'],
      ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'3%'],
      ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'10%',"orderable" => false],
    ])
    ->parameters(['order' =>[],]);
    return view($this->pageLayout.'index',compact('html'));
  }

  public function create(){
    $users = User::where('user_type','user')->get()->pluck('first_name','id');
    return view($this->pageLayout.'create', compact('users'));
  }

  public function store(Request $request){
    $validatedData = Validator::make($request->all(),[
      'promo_code'           => 'required|unique:promocodes,promo_code|between:1,20',
      'amount'          => 'required|integer|between:1,100',
      'start_date'        => 'required',
      'end_date'         => 'required',
      'description'       => 'required',
      'status'            => 'required',
    ]);
    if($validatedData->fails()){
      return redirect()->back()->withErrors($validatedData)->withInput();
    }
    try{
      if(isset($request->promo_for_users)){
        $promousers = implode(',',$request->promo_for_users);
      }else{
        $promousers = '';
      }
      $promocodes = Promocodes::create([
        'promo_code'      => @$request->get('promo_code'),
        'amount'          => @$request->get('amount'),
        'start_date'      => @$request->get('start_date'),
        'end_date'        => @$request->get('end_date'),
        'description'     => @$request->get('description'),
        'status'          => @$request->get('status'),
        'promo_for_users' => @$promousers,
      ]);
      Notify::success('Promocodes Created Successfully.');
      return redirect()->route('admin.promocode.index');
    }catch(\Exception $e){
      return back()->with([
        'alert-type'    => 'danger',
        'message'       => $e->getMessage()
      ]);
    }
  }

  public function edit($id){
    $promocode = Promocodes::find($id);
    $users = User::where('user_type','user')->get()->pluck('first_name','id');
    $promousers=$promouserstemp=[];
    if(!empty($promocode) && $promocode->promo_for_users){
      $prmouserlist = explode(',',$promocode->promo_for_users);
      $promousers = User::whereIn('users.id',$prmouserlist)->get()->pluck('id');
    }
    return view($this->pageLayout.'edit',compact('promocode','users','promousers'));
  }

  public function update(Request $request, $id){
    $validatedData = Validator::make($request->all(),[
      'promo_code'           => 'required|between:1,20|unique:promocodes,promo_code,'.$id,
      'amount'          => 'required|integer|between:1,100',
      'start_date'        => 'required',
      'end_date'         => 'required',
      'description'       => 'required',
    ]);
    if($validatedData->fails()){
      return redirect()->back()->withErrors($validatedData)->withInput();
    }
    try{
      if(isset($request->promo_for_users)){
        $promousers = implode(',',$request->promo_for_users);
      }else{
        $promousers = '';
      }
      $promocodes_data = Promocodes::where('id',$id)->update([
        'promo_code'      => @$request->get('promo_code'),
        'amount'     => @$request->get('amount'),
        'start_date'    => @$request->get('start_date'),
        'end_date' => @$request->get('end_date'),
        'description' => @$request->get('description'),
        'status'      => @$request->get('status'),
        'promo_for_users' => @$promousers,
      ]);
      Notify::success('Promocode Updated Successfully.');
      return redirect()->route('admin.promocode.index');
    }catch(\Exception $e){
      return back()->with([
        'alert-type'    => 'danger',
        'message'       => $e->getMessage()
      ]);
    }
  }

  public function delete($id){
    $Promocodes = Promocodes::where('id',$id)->first();
    $Promocodes->delete();
    Notify::success('Promocodes deleted successfully.');
    return response()->json([
      'status'    => 'success',
      'title'     => 'Success!!',
      'message'   => 'Promocodes removed successfully.'
    ]);
  }

  public function statusupdate(Request $request){
    $promocodes = Promocodes::where('id',$request->id)->first();
    if($promocodes->status == "active"){
      Promocodes::where('id',$request->id)->update([
        'status'             => "inactive",
      ]);
    }
    if($promocodes->status == "inactive"){
      Promocodes::where('id',$request->id)->update([
        'status'             => "active",
      ]);
    }
    Notify::success('Status updated Successfully.');
    return response()->json([
      'status'    => 'success',
      'title'     => 'Success!!',
      'message'   => 'Status updated successfully.'
    ]);
  }
}