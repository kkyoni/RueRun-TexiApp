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
use App\Models\Wallet;
use Event;
use Illuminate\Http\Request;
use Settings;

class UsersCreditController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.user_credit.';
        $this->middleware('auth');
    }
    public function index(Builder $builder, Request $request){
        $users = Wallet::with('userDetail')->orderBy('id','desc');
        if (request()->ajax()) {
            return DataTables::of($users->get())
            ->addIndexColumn()
            ->editColumn('username', function (Wallet $users) {
                return $users->userDetail->username;
            })
            ->editColumn('action', function (Wallet $users) {
                $action = '';
                $action .= '<a title="Edit" class="ml-2 mr-2" href='.route('admin.user_credit.edit',[$users->id]).'><i class="fa fa-pencil"></i></a>';
                return $action;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'username', 'name'    => 'username', 'title' => 'User Name','width'=>'15%'],
            ['data' => 'amount', 'name' => 'amount', 'title' => 'Credit','width'=>'15%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
        ])
        ->parameters(['order' =>[]]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        return view($this->pageLayout.'create');
    }

    public function editCredit($id){
        $users = Wallet::find($id);
        if(!empty($users)){
            return view($this->pageLayout.'edit',compact('users','id'));
        }else{
            return redirect()->route('admin.index');
        }
    }

    public function store(Request $request){

    }

    public function updateCredit(Request $request,$id){
        $validatedData = Validator::make($request->all(),[
            'credit'        => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            Wallet::where('id',$id)->update([
                'amount'        => @$request->get('amount'),
            ]);
            Notify::success('User Wallet Updated Successfully.');
            return redirect()->route('admin.user_credit.index');
        } catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }
}