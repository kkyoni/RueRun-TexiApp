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
use App\Models\SupportCategory;
use Event;
use Illuminate\Http\Request;
use Settings;

class SupportCategoryController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.support_category.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $category = SupportCategory::orderBy('id','desc');
        if (request()->ajax()) {
            return DataTables::of($category->get())
            ->addIndexColumn()
            ->editColumn('action', function (SupportCategory $category) {
                $action = '';
                $action .= '<a title="Edit" class="ml-2 mr-2" href='.route('admin.support_cat.edit',[$category->id]).'><i class="fa fa-pencil"></i></a>';
                $action .='<a title="Delete" class="ml-2 mr-2 deleteuser" data-id ="'.$category->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                return $action;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'cat_name', 'name'    => 'cat_name', 'title' => 'Category Name','width'=>'15%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
        ])
        ->parameters(['order' =>[]]);
        $roles = array('superadmin'=>'Super Admin','user'=>'User','driver'=>'Driver');
        \View::share('roles',$roles);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        return view($this->pageLayout.'create');
    }

    public function edit($id){
        $category = SupportCategory::find($id);
        if(!empty($category)){
            return view($this->pageLayout.'edit',compact('category','id'));
        }else{
            return redirect()->route('admin.index');
        }
    }

    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'cat_name'          => 'required|unique:support_category,cat_name,NULL,id,deleted_at,NULL',
        ],[
            'cat_name.required'          => 'The category name field is required.'
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $category=SupportCategory::create([
                'cat_name'        => @$request->get('cat_name'),
            ]);
            Notify::success('Category Created Successfully.');
            return redirect()->route('admin.support_cat.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request,$id){
        $validatedData = Validator::make($request->all(),[
            'cat_name'          => 'required|unique:support_category,cat_name,'.$id.',id,deleted_at,NULL',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            SupportCategory::where('id',$id)->update([
                'cat_name'        => @$request->get('cat_name'),
            ]);
            Notify::success('Category Updated Successfully.');
            return redirect()->route('admin.support_cat.index');
        } catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function delete($id){
        try{
            $checkUser = SupportCategory::where('id',$id)->first();
            $checkUser->delete();
            Notify::success('Category deleted successfully.');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Category deleted successfully.'
            ]);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }
}