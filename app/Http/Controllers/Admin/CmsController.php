<?php
namespace App\Http\Controllers\Admin;
use App\Models\Cms;
use App\Http\Controllers\Controller;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use Auth;
use Event,Str,Storage;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;

class CmsController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.cms.';
        $this->middleware('auth');
    }
    public function index(Builder $builder, Request $request){
        $userRole = '';
        $userRole = Helper::checkPermission(['cms-list','cms-edit']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $permission_data['hasUpdatePermission'] = Helper::checkPermission(['cms-edit']);
        
        $cms = Cms::orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($cms->get())
            ->addIndexColumn()
            ->editColumn('action', function (Cms $cms) use($permission_data){
                $action = '';
                if($permission_data['hasUpdatePermission']){
                $action .= '<a title="Edit" class="btn btn-warning btn-sm ml-1" href='.route('admin.cms.edit',[$cms->id]).'><i class="fa fa-pencil"></i></a>';
                }
                return $action;
            })
            ->editColumn('page_description', function (Cms $cms) {
                return strip_tags(str_limit($cms->page_description, $limit = 100, $end = '...'));
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'page_title','name' => 'page_title','title' =>'Title','width'=>'30%'],
            ['data' => 'page_description', 'name' => 'page_description', 'title' => 'Page Content','width'=>'30%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'2%',"orderable" => false],
        ])
        ->parameters(['order' =>[]]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        return view($this->pageLayout.'create');
    }

    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'page_title'           => 'required',
            'page_description'     => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $emergency = Cms::create([
                'page_title'          => @$request->page_title,
                'page_code'           => str_slug($request->page_title, "_"),
                'page_description'    => @$request->get('page_description'),
            ]);
            Notify::success($request->page_title.' CMS Created Successfully.');
            return redirect()->route('admin.cms.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }
    public function show(Cms $cms){

    }
    public function edit(Cms $cms, $id){
        $userRole = Helper::checkPermission(['cms-edit']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $cms = Cms::where('id',$id)->first();
        return view($this->pageLayout.'edit',compact('cms','id'));
    }

    public function update(Request $request, Cms $cms, $id)
    {
        $validatedData = Validator::make($request->all(),[
            'page_title'           => 'required',
            'page_description'     => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $emergency = Cms::where('id', $id)->update([
                'page_title'          => @$request->page_title,
                'page_description'    => @$request->get('page_description'),
            ]);
            Notify::success($request->page_title.' CMS Updated Successfully.');
            return redirect()->route('admin.cms.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }
    public function destroy(Cms $cms){

    }
}