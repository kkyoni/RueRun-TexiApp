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
use App\Models\Preferences;
use Illuminate\Http\Request;
use Settings;

class PreferenceController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.preferences.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request) {
        $preferences = Preferences::orderBy('id','desc');
        if (request()->ajax()) {
            return DataTables::of($preferences->get())
            ->addIndexColumn()
            ->editColumn('image', function (Preferences $preferences) {
                if($preferences->avatar != ""){
                    return "<img src=".url("storage/preferences/".$preferences->avatar)."  width='60px'/> ";
                }else{
                    return "<img src=".url("storage/preferences/default.png")."  width='60px'/> ";
                }
            })
            ->editColumn('status', function (Preferences $preferences) {
                $s="";
                if($preferences->status == "active"){
                    $s .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-icon btn-success changeStatusRecord" data-id="'.$preferences->id.'">Active</a>';
                }else{
                    $s .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="InActive" class="btn btn-sm btn-icon btn-danger changeStatusRecord" data-id="'.$preferences->id.'">Inactive</a>';
                }
                return $s;
            })
            ->editColumn('description', function (Preferences $preferences) {
                return ''.Str::words($preferences->description, 10,'....').'';
            })
            ->editColumn('action', function (Preferences $preferences) {
                $action = '';
                $action .= '<a title="Edit" class="ml-2 mr-2" href='.route('admin.preference.edit',[$preferences->id]).'><i class="fa fa-pencil"></i></a>';
                $action .='<a title="Delete" class="ml-2 mr-2 deleteuser" data-id ="'.$preferences->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                return $action;
            })
            ->rawColumns(['action','image','status','description'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'image', 'name'    => 'avatar', 'title' => 'Avatar','width'=>'10%',"orderable" => false, "searchable" => false],
            ['data' => 'description', 'name'    => 'description', 'title' => 'Description','width'=>'15%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'5%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'8%',"orderable" => false, "searchable" => false],
        ])
        ->parameters([
            'order' =>[]
        ]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        return view($this->pageLayout.'create');
    }

    public function edit($id){
        $preferences = Preferences::find($id);
        if(!empty($preferences)){
            return view($this->pageLayout.'edit',compact('preferences','id'));
        }else{
            return redirect()->route('roles1.index');
        }
    }

    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'description'        => 'required|max:5000',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10).'.'.$extension;
                Storage::disk('public')->putFileAs('preferences', $file,$filename);
            }else{

            }
            $preferences=Preferences::create([
                'avatar'            => @$filename,
                'status'            => @$request->get('status'), 
                'description'       => @$request->get('description'),
            ]);
            $preferences->save();
            Notify::success('Preferences Created Successfully.');
            return redirect()->route('admin.preference.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request,$id){
        $validatedData = Validator::make($request->all(),[
            'description'        => 'required|max:5000',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $oldDetails = Preferences::find($id);
            if($request->hasFile('avatar')){
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(10).'.'.$extension;
                Storage::disk('public')->putFileAs('preferences', $file,$filename);
            }else{
                if($oldDetails->avatar !== null){
                    $filename = $oldDetails->avatar;
                }else{

                }
            }
            Preferences::where('id',$id)->update([
                'description'        => @$request->get('description'),
                'avatar'             => @$filename,
                'status'             => @$request->get('status')
            ]);
            Notify::success('Preferences Updated Successfully.');
            return redirect()->route('admin.preference.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function delete($id){
        try{
            $preferences = Preferences::where('id',$id)->first();
            $preferences->delete();
            Notify::success('Preference deleted successfully.');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Preference deleted successfully.'
            ]);
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function change_status(Request $request){
        try{
            $user = Preferences::where('id',$request->id)->first();
            if($user === null){
                return redirect()->back()->with([
                    'status'    => 'warning',
                    'title'     => 'Warning!!',
                    'message'   => 'User not found !!'
                ]);
            }else{
                if($user->status == "active"){
                    Preferences::where('id',$request->id)->update([
                        'status' => "inactive",
                    ]);
                }
                if($user->status == "inactive"){
                    Preferences::where('id',$request->id)->update([
                        'status'=> "active",
                    ]);
                }
            }
            Notify::success('Preference status updated successfully !!');
            return response()->json([
                'status'    => 'success',
                'title'     => 'Success!!',
                'message'   => 'Preference status updated successfully.'
            ]);
        }catch (Exception $e){
            return response()->json([
                'status'    => 'error',
                'title'     => 'Error!!',
                'message'   => $e->getMessage()
            ]);
        }
    }
}