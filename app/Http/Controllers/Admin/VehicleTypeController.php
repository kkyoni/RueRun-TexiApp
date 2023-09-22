<?php
namespace App\Http\Controllers\Admin;
use App\Models\VehicleType;
use App\Models\VehicleBody;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Validator;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use App\Models\Vehicle;

class VehicleTypeController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/
    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.vehicletype.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $vehicleCategories = VehicleType::with('model')->orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($vehicleCategories->get())
            ->addIndexColumn()
            ->editColumn('action', function(VehicleType $vehicletype) {
                $action  = '';
                $action .= '<a title="Edit" href='.route('admin.vehicletype.edit',[$vehicletype->id]).' class="btn btn-warning btn-sm ml-1"><i class="fa fa-pencil"></i></a><a title="Delete" class="ml-1 btn btn-danger btn-sm  deletevehiclecategories" data-id ="'.$vehicletype->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                if($vehicletype->status == "active"){
                    $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$vehicletype->id.'"><i class="fa fa-unlock"></i></a>';
                }else{
                    $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$vehicletype->id.'"><i class="fa fa-lock" ></i></a>';
                }
                return $action;
            })
            ->editColumn('status', function(VehicleType $vehicletype) {
                if ($vehicletype->status == "active") {
                    return '<span class="label label-success">Active</span>';
                } else {
                    return '<span class="label label-danger">Block</span>';
                }
            })
            ->editColumn('model_id', function(VehicleType $vehicletype){
                return $vehicletype->model->name;
            })
            ->rawColumns(['status','action','vehicle_image'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'10%'],
            ['data' => 'name', 'name'    => 'name', 'title' => 'Vehicle Type','width'=>'25%'],
            ['data' => 'model_id', 'name'    => 'model_id', 'title' => 'Make Model','width'=>'25%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'25%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'40%',"orderable" => false],
        ])
        ->parameters(['order' =>[]]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        $car_model = VehicleBody::where('status','active')->get()->pluck('name','id');
        return view($this->pageLayout.'create',compact('car_model'));
    }

    public function store(Request $request){
        $r = $request->all();
        $validatedData = Validator::make($request->all(),[
            'name'      => 'required|unique:vehicle_types,name|min:1|max:60',
            'status'    => 'required|in:active,inactive'
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try {
            $rawmaterial = new VehicleType;
            $rawmaterial->fill($r);
            $rawmaterial->save();
            $error = 'success' ;
            $msg = 'Vehicle Model Created Successfully.';
            Notify::success($msg);
            $redirectpath = 'index';
        }  catch (Exception $e) {
            $msg = $e->getMessage();
            if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                $msg = $e->errorInfo[2];
            }
            Notify::error($msg);
            $error = 'danger';
            $redirectpath = 'create';
        } catch (\Illuminate\Database\QueryException $e) {
            $msg = $e->getMessage();
            if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                $msg = $e->errorInfo[2];
            }
            Notify::error($msg);
            $error = 'danger';
            $redirectpath = 'create';
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $msg = $e->getMessage();
            if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                $msg = $e->errorInfo[2];
            }
            Notify::error($msg);
            $error = 'danger';
            $redirectpath = 'create';
        }
        return redirect()->route('admin.vehicletype'.'.'.$redirectpath)->with(['alert-type'=>$error,'message'=>$msg]);
    }

    public function show(VehicleType $vehicletype){

    }

    public function edit(VehicleType $vehicletype) {
        $car_model = VehicleBody::where('status','active')->get()->pluck('name','id');
        return view($this->pageLayout.'edit',compact('vehicletype','car_model'));
    }

    public function update(Request $request, VehicleType $vehicletype) {
        $r = $request->all();
        $validatedData = $request->validate([
            'name'      => 'required|min:1|max:60|unique:vehicle_types,name,'.$vehicletype->id,
            'status'    => 'required|in:active,inactive',
            'model_id'  => 'required'
        ]);
        if($vehicletype){
            try {
                $vehicletype = VehicleType::find($vehicletype->id);
                $vehicletype->fill($r);
                $vehicletype->save();
                $error = 'success' ;
                $msg = 'Vehicle Model updated successfully';
                $redirectpath = 'index';
            }  catch (Exception $e) {
                $msg = $e->getMessage();
                if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                    $msg = $e->errorInfo[2];
                }
                Notify::error($msg);
                $error = 'warning';
                $redirectpath = 'create';
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = $e->getMessage();
                if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                    $msg = $e->errorInfo[2];
                }
                Notify::error($msg);
                $error = 'warning';
                $redirectpath = 'create';
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $msg = $e->getMessage();
                if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                    $msg = $e->errorInfo[2];
                }
                Notify::error($msg);
                $error = 'warning';
                $redirectpath = 'create';
            }
            return redirect()->route('admin.vehicletype'.'.'.$redirectpath)->with(['alert-type'=>$error,'message'=>$msg]);
        }else{
            return redirect()->route('admin.vehicletype.index')->with(['alert-type'=>'danger','message'=>'Vehicle Model Not Found']);
        }
    }

    public function destroy(VehicleType $vehicletype) {
        if($vehicletype){
            $rawmaterial= VehicleType::find($vehicletype->id);
            try {
                $vehicletype->delete();
                $msg = 'Vehicle Body removed sucessfully.';
                return ["status"=>'success','title'=> 'Success!!',"message"=>$msg];
            } catch (Exception $e) {
                $msg = $e->getMessage();
                if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                    $msg = $e->errorInfo[2];
                }
                return ["status"=>'error','title'=> 'Fail!!',"message"=>$msg];
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = $e->getMessage();
                if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                    $msg = $e->errorInfo[2];
                }
                return ["status"=>'error','title'=> 'Fail!!',"message"=>$msg];
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $msg = $e->getMessage();
                if(isset($e->errorInfo[2]) && !empty($e->errorInfo[2])){
                    $msg = $e->errorInfo[2];
                }
                return ["status"=>'error','title'=> 'Fail!!',"message"=>$msg];
            }
        }else{
            return ["status"=>'error','title'=> 'Fail!!',"message"=>'Vehicle Model Not Found'];
        }
    }

    public function changestatus(Request $request){
        $vehiclecategories = VehicleType::where('id',$request->id)->first();
        if($vehiclecategories->status == "active"){
            VehicleType::where('id',$request->id)->update(['status' => "inactive"]);
        }
        if($vehiclecategories->status == "inactive"){
            VehicleType::where('id',$request->id)->update(['status'=> "active", ]);
        }
        Notify::success('Status updated Successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Status updated successfully.'
        ]);
    }
}