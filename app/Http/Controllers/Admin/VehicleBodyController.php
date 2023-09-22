<?php
namespace App\Http\Controllers\Admin;;
use App\Models\VehicleBody;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Validator;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;

class VehicleBodyController extends Controller{
    protected $authLayout = '';
    protected $pageLayout = '';
    /*** Create a new controller instance.** @return void*/

    public function __construct(){
        $this->authLayout = 'admin.auth.';
        $this->pageLayout = 'admin.pages.vehiclebody.';
        $this->middleware('auth');
    }

    public function index(Builder $builder, Request $request){
        $vehicleCategories = VehicleBody::orderBy('updated_at','DESC');
        if (request()->ajax()) {
            return DataTables::of($vehicleCategories->get())
            ->addIndexColumn()
            ->editColumn('action', function(VehicleBody $vehiclebody) {
                $action='';
                $checkvehiclemodal = VehicleType::where('model_id', $vehiclebody->id)->first();
                if(empty($checkvehiclemodal)){
                    $action .= '<a title="Delete" class="btn btn-danger btn-sm deletevehiclecategories ml-1" data-id ="'.$vehiclebody->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
                }
                $action .= '<a title="Edit" href='.route('admin.vehiclebody.edit',[$vehiclebody->id]).' class="btn btn-warning btn-sm ml-1"><i class="fa fa-pencil"></i></a>';
                if($vehiclebody->status == "active"){
                    $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$vehiclebody->id.'"><i class="fa fa-unlock"></i></a>';
                }else{
                    $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$vehiclebody->id.'"><i class="fa fa-lock" ></i></a>';
                }
                return $action;
            })
            ->editColumn('status', function(VehicleBody $vehiclebody) {
                if ($vehiclebody->status == "active") {
                    return '<span class="label label-success">Active</span>';
                } else {
                    return '<span class="label label-danger">Block</span>';
                }
            })
            ->rawColumns(['status','action','vehicle_image'])
            ->make(true);
        }
        $html = $builder->columns([
            ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'25%'],
            ['data' => 'name', 'name'    => 'name', 'title' => 'Make','width'=>'25%'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'25%'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'25%',"orderable" => false],
        ])
        ->parameters(['order' =>[] ]);
        return view($this->pageLayout.'index',compact('html'));
    }

    public function create(){
        return view($this->pageLayout.'create');
    }

    public function store(Request $request){
        $r = $request->all();
        $validatedData = Validator::make($request->all(),[
            'name'      => 'required|string|unique:vehicle_bodies,name|min:1|max:60',
            'status'    => 'required|in:active,inactive'
        ]);
        if($validatedData->fails()){
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
        try {
            $rawmaterial = new VehicleBody;
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
        return redirect()->route('admin.vehiclebody'.'.'.$redirectpath)->with(['alert-type'=>$error,'message'=>$msg]);
    }

    public function show(VehicleBody $vehiclebody){

    }

    public function edit(VehicleBody $vehiclebody){
        return view($this->pageLayout.'edit',compact('vehiclebody'));
    }

    public function update(Request $request, VehicleBody $vehiclebody){
        $r = $request->all();
        $validatedData = $request->validate([
            'name'      => 'required|min:1|max:60|unique:vehicle_bodies,name,'.$vehiclebody->id,
            'status'    => 'required|in:active,inactive'
        ]);
        if($vehiclebody){
            try {
                $vehiclebody = VehicleBody::find($vehiclebody->id);
                $vehiclebody->fill($r);
                $vehiclebody->save();
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
            return redirect()->route('admin.vehiclebody'.'.'.$redirectpath)->with(['alert-type'=>$error,'message'=>$msg]);
        }else{
            return redirect()->route('admin.vehiclebody.index')->with(['alert-type'=>'danger','message'=>'Vehicle Model Not Found']);
        }
    }

    public function destroy(VehicleBody $vehiclebody){
        if($vehiclebody){
            $rawmaterial= VehicleBody::find($vehiclebody->id);
            try {
                $vehiclebody->delete();
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
        $vehiclecategories = VehicleBody::where('id',$request->id)->first();
        if($vehiclecategories->status == "active"){
            VehicleBody::where('id',$request->id)->update([ 'status'  => "inactive",]);
        }
        if($vehiclecategories->status == "inactive"){
            VehicleBody::where('id',$request->id)->update(['status'  => "active",]);
        }
        Notify::success('Status updated Successfully.');
        return response()->json([
            'status'    => 'success',
            'title'     => 'Success!!',
            'message'   => 'Status updated successfully.'
        ]);
    }
}