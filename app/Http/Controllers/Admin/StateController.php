<?php
namespace App\Http\Controllers\Admin;
use App\Models\State;
use App\Http\Controllers\Controller;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use Auth;
use Event,Str,Storage;
use App\Models\City;
use App\Models\User;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;

class StateController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.states.';
        $this->citypageLayout = 'admin.pages.cities.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        if (request()->ajax()) {
            return DataTables::of(State::orderBy('updated_at','DESC')->get())
            ->addIndexColumn()
            ->editColumn('action', function(State $state) {
                $checkuserstate = User::where('state_id', $state->id)->first();
                $action='';
                if(!empty($checkuserstate)){
                    $action = '';
                }else{
                    $action = '<a title="Delete" class="btn btn-danger btn-sm deletestate ml-1" data-id ="'.$state->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                }
                return '<a title="Edit" class="btn btn-warning btn-sm show_info ml-1" data-toggle="modal" data-target="#exampleModalCenter'.$state->id.'" data-id ="'.$state->id.'" href="javascript:void(0)"><i class="fa fa-pencil"></i></a>'.$action.'
                <div class="modal fade" id="exampleModalCenter'.$state->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Edit State</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <form action="'.route('admin.states.update').'" method="POST" >
                <div class="modal-body">
                '.csrf_field().'
                <div class="form-group  row "><label class="col-sm-3 col-form-label"><strong>State</strong> <span class="text-danger">*</span></label>
                <div class="col-sm-9">

                <input class="form-control state" id="state" name="state"   maxlength ="35" type="text" value="'.$state->state.'">
                <input class="form-control" id="id" name="id" type="hidden" value="'.$state->id.'">
                </div>
                </div>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
                </div>
                </form>
                </div>
                </div>
                </div>';
            })
            ->rawColumns(['state','action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'state', 'name'    => 'state', 'title' => 'State','width'=>'20%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'6%',"orderable" => false],
        ])->parameters(['order' =>[],]);
        return view($this->pageLayout.'index',compact('html'));
    }
    public function create(){

    }
    
    public function store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'state'        => 'required|unique:states,state,NULL,id,deleted_at,NULL',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            $userID=State::create(['state' => @$request->get('state')]);
            Notify::success('State Created Successfully.');
            return redirect()->route('admin.states.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function show(State $state){

    }

    public function edit(State $state, $id){
        $state = State::find($id);
        return view($this->pageLayout.'edit',compact('state'));
    }

    public function update(Request $request, State $state){
        $validatedData = Validator::make($request->all(),[
            'state'        => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            State::where('id', $request->id)->update(['state' => @$request->get('state')]);
            Notify::success('State Updated Successfully.');
            return redirect()->route('admin.states.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function delete(State $state, $id){
        try{
            State::where('id', $id)->delete();
            Notify::success('State Deleted Successfully.');
            return redirect()->route('admin.states.index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function city_index(Builder $builder, Request $request){
        $states = State::get();
        if (request()->ajax()) {
            return DataTables::of(City::orderBy('updated_at','DESC')->get())
            ->addIndexColumn()
            ->editColumn('action', function(City $city) {
                $checkusercity = User::where('city_id', $city->id)->first();
                $action='';
                if(!empty($checkusercity)){
                    $action = '';
                }else{
                    $action = '<a title="Delete" class="btn btn-danger btn-sm deletecity ml-1" data-id ="'.$city->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                }
                return '<a title="Edit" class="btn btn-warning btn-sm show_info ml-1"  data-id ="'.$city->id.'" href="'.route('admin.states.city_edit',[$city->id]).'"><i class="fa fa-pencil"></i></a>'.$action.'';
            })
            ->editColumn('state_id', function(City $city) {
                if(!empty($city->state)){
                    return $city->state->state;
                }else{
                    return '';
                }
            })
            ->rawColumns(['city','action'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'5%',"orderable" => false, "searchable" => false],
            ['data' => 'city', 'name'    => 'city', 'title' => 'City','width'=>'20%'],
            ['data' => 'state_id', 'name'=> 'state_id', 'title' => 'State','width'=>'20%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'6%',"orderable" => false],
            ])
        ->parameters(['order' =>[],]);
        return view($this->pageLayout.'city_index',compact('html','states'));
    }

    public function city_store(Request $request){
        $validatedData = Validator::make($request->all(),[
            'city'        => 'required|unique:cities',
            'state_id'        => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            City::create([
                'city' => $request->city,
                'state_id' => $request->state_id
            ]);
            Notify::success('City Created Successfully.');
            return redirect()->route('admin.states.city_index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function city_edit(City $city, $id){
        $city = City::find($id);
        $states = State::get();
        return view($this->pageLayout.'city_edit',compact('city','states'));
    }

    public function city_update(Request $request, State $state, $id){
        $validatedData = Validator::make($request->all(),[
            'state_id'        => 'required',
            'city'        => 'required',
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try{
            City::where('id', $id)->update([
                'state_id' => @$request->get('state_id'),
                'city' => @$request->get('city')
            ]);
            Notify::success('City Updated Successfully.');
            return redirect()->route('admin.states.city_index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }

    public function city_delete(City $city, $id){
        try{
            City::where('id', $id)->delete();
            Notify::success('City Deleted Successfully.');
            return redirect()->route('admin.states.city_index');
        }catch(\Exception $e){
            return back()->with([
                'alert-type'    => 'danger',
                'message'       => $e->getMessage()
            ]);
        }
    }
}
