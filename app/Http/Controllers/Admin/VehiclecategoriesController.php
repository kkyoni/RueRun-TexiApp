<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use Auth;
use Event,Str,Storage;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;
use App\Models\VehicleType;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;


class VehiclecategoriesController extends Controller{
  protected $authLayout = '';
  protected $pageLayout = '';
  /*** Create a new controller instance.** @return void*/
  public function __construct(){
    $this->authLayout = 'admin.auth.';
    $this->pageLayout = 'admin.pages.vehiclecategories.';
    $this->middleware('auth');
  }

  public function index(Builder $builder, Request $request){
    $userRole = '';
        $userRole = Helper::checkPermission(['vehicle-list','vehicle-create','vehicle-edit','vehicle-delete']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
        $permission_data['hasUpdatePermission'] = Helper::checkPermission(['vehicle-edit']);
        $permission_data['hasRemovePermission'] = Helper::checkPermission(['vehicle-delete']);

    $vehicleCategories = Vehicle::orderBy('updated_at','desc');
    if (request()->ajax()) {
      return DataTables::of($vehicleCategories->get())
      ->addIndexColumn()
      ->editColumn('vehicle_image', function(Vehicle $vehicleCategories) {
        if($vehicleCategories->vehicle_image){
          $i='';
          if (file_exists( 'storage/vehicle_images/'. @$vehicleCategories->vehicle_image)) {
            return '<img class="img-thumbnail" src="' . asset('storage/vehicle_images') . '/' . @$vehicleCategories->vehicle_image . '" width="60px">';
          }else{
            return '<img class="img-thumbnail" src="' . asset('storage/vehicle_images/default.png').'" width="60px">';
          }
        }else{
          return '<img class="img-thumbnail" src="' . asset('storage/vehicle_images/default.png').'" width="60px">';
        }
      })
      ->editColumn('action', function(Vehicle $vehicleCategories) use($permission_data) {
        $action  = '';
        if($permission_data['hasUpdatePermission']){
        $action .= '<a title="Edit" href='.route('admin.vehiclecategoriesedit',[$vehicleCategories->id]).' class="btn btn-warning btn-sm ml-1"><i class="fa fa-pencil"></i></a>';
        }
        if($permission_data['hasRemovePermission']){
        $action .= '<a title="Delete" class="btn btn-danger btn-sm deletevehiclecategories ml-1" data-id ="'.$vehicleCategories->id.'" href="javascript:void(0)"><i class="fa fa-trash"></i></a>';
        }
        if($vehicleCategories->status == "active"){
          $action .= '<a href="javascript:void(0)" data-value="1"   data-toggle="tooltip" title="Active" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$vehicleCategories->id.'"><i class="fa fa-unlock"></i></a>';
        }else{
          $action .= '<a href="javascript:void(0)" data-value="0"  data-toggle="tooltip" title="Block" class="btn btn-sm btn-dark ml-1 changeStatusRecord" data-id="'.$vehicleCategories->id.'"><i class="fa fa-lock" ></i></a>';
        }
        return $action;
      })
      ->editColumn('status', function(Vehicle $vehicleCategories) {
        if ($vehicleCategories->status == "active") {
          return '<span class="label label-success">Active</span>';
        } else {
          return '<span class="label label-danger">Block</span>';
        }
      })
      ->rawColumns(['status','action','vehicle_image'])
      ->make(true);
    }
    $html = $builder->columns([
      ['data' => 'DT_RowIndex', 'name' => '', 'title' => 'Sr no','width'=>'3%'],
      ['data' => 'vehicle_image', 'name'    => 'vehicle_image', 'title' => 'Image','width'=>'5%',"orderable" => false, "searchable" => false],
      ['data' => 'name', 'name'    => 'name', 'title' => 'Name','width'=>'5%'],
      ['data' => 'base_fare', 'name'    => 'base_fare', 'title' => 'Fare','width'=>'2%'],
      ['data' => 'price_per_km', 'name'    => 'price_per_km', 'title' => 'Price Per Km','width'=>'2%'],
      ['data' => 'extra_cost_dropdown', 'name'    => 'extra_cost_dropdown', 'title' => 'Extra Cost','width'=>'2%'],
      ['data' => 'extra_cost_include', 'name' => 'extra_cost_include', 'title' => 'Extra Cost Include','width'=>'2%'],
      ['data' => 'total_seat', 'name' => 'total_seat', 'title' => 'Total Seats','width'=>'2%'],
      ['data' => 'ranking', 'name' => 'ranking', 'title' => 'Ranking','width'=>'2%'],
      ['data' => 'status', 'name' => 'status', 'title' => 'Status','width'=>'2%'],
      ['data' => 'action', 'name' => 'action', 'title' => 'Action','width'=>'20%',"orderable" => false],
      ])
    ->parameters(['order' =>[]]);
    return view($this->pageLayout.'index',compact('html'));
  }

  public function create(){
    $userRole = Helper::checkPermission(['vehicle-create']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
    $car_type = VehicleType::get()->pluck('fullname','id');
    return view($this->pageLayout.'create',compact('car_type'));
  }

  public function store(Request $request){
    $validatedData = Validator::make($request->all(),[
      'name'                              => 'required|unique:vehicles,name|min:1|max:60',
      'base_fare'                         => 'required|integer|between:0,99',
      'price_per_km'                      => 'required|integer|between:0,60',
      'extra_cost_dropdown'               => 'required',
      'cancellation_time_in_minutes'      => 'required|integer|between:0,60',
      'cancellation_charge_in_per'        => 'required|integer|between:0,60',
      'vehicle_image'                     => 'sometimes|mimes:jpeg,jpg,png',
      'total_seat'                        => 'required|integer|between:0,60',
      'ranking'                           => 'required|unique:vehicles,ranking|min:1|max:60',
      // 'ranking'                           => 'required|integer|between:0,60',
      ],
      [
      'vehicle_categories_name.required' => 'The vehicle categories field is required.',
      'extra_cost_dropdown.required'  => 'The extra cost field is required.',
      'cancellation_charge_in_per.required' => 'The cancellation charge in per minutes field is required.'
      ]);
    if($validatedData->fails()){
      return redirect()->back()->withErrors($validatedData)->withInput();
    }
    try{
      if($request->hasFile('vehicle_image') !=''){
        $file = $request->file('vehicle_image');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(10).'.'.$extension;
        Storage::disk('public')->putFileAs('vehicle_images', $file,$filename);
      }else{
        $filename = 'default.png';
      }
      if(@$request->get('vehicle_type_id')){
        $getvehiclemodel = VehicleType::where('id', @$request->get('vehicle_type_id'))->first();
        $model_id = (int)$getvehiclemodel->model_id;
      }else{
        $model_id = 0;
      }
      $vehiclecategories = Vehicle::create([
        'vehicle_image'           => @$filename,
        'name'                    => @$request->get('name'),
        'base_fare'               => @$request->get('base_fare'),
        'vehicle_type_id'         => @$request->get('vehicle_type_id'),
        'price_per_km'            => @$request->get('price_per_km'),
        'extra_cost_dropdown'     => @$request->get('extra_cost_dropdown'),
        'extra_cost_include'      => @$request->get('extra_cost_include'),
        'status'                  => @$request->get('status'),
        'cancellation_time_in_minutes'    => @$request->get('cancellation_time_in_minutes'),
        'cancellation_charge_in_per'      => @$request->get('cancellation_charge_in_per'),
        'total_seat'                      => @$request->get('total_seat'),
        'vehicle_model_id'         => $model_id,
        'wheel_type'               => @$request->get('wheel_type'),
        'ranking'                  => @$request->get('ranking'),
      ]);
      Notify::success('Vehicle Categories Created Successfully.');
      return redirect()->route('admin.vehicleindex');
    }catch(\Exception $e){
      return back()->with([
        'alert-type'    => 'danger',
        'message'       => $e->getMessage()
      ]);
    }
  }

  public function edit($id){
    $userRole = Helper::checkPermission(['vehicle-edit']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
    $vehiclecategories = Vehicle::find($id);
    $car_type = VehicleType::get()->pluck('fullname','id');
    return view($this->pageLayout.'edit',compact('vehiclecategories','car_type'));
  }

  public function update(Request $request, $id){
    $validatedData = Validator::make($request->all(),[
      'name'              => 'required|min:1|max:60|unique:vehicles,name,'.$id,
      'base_fare'           => 'required|integer|between:0,99',
      'price_per_km'          => 'required|integer|between:0,60',
      'extra_cost_dropdown'        => 'required',
      'cancellation_time_in_minutes'       => 'required|integer|between:0,60',
      'cancellation_charge_in_per'        =>'required|integer|between:0,60',
      'vehicle_image'                     =>'sometimes|mimes:jpeg,jpg,png',
      'total_seat'        => 'required|integer|between:0,60',
      'ranking'                           => 'required|unique:vehicles,ranking,'.$id,
      // 'ranking'                           => 'required|integer|between:0,60',
      ],[
      'vehicle_categories_name.required'  => 'The vehicle categories field is required.',
      'extra_cost_dropdown.required'      => 'The extra cost field is required.',
      'cancellation_charge_in_per.required'=> 'The cancellation charge in per minutes field is required.'
      ]);
    if($validatedData->fails()){
      return redirect()->back()->withErrors($validatedData)->withInput();
    }
    try{
      $allowedfileExtension=['pdf','jpg','png'];
      if($request->hasFile('vehicle_image')){
        $file = $request->file('vehicle_image');
        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(10).'.'.$extension;
        Storage::disk('public')->putFileAs('vehicle_images', $file,$filename);
      }else{
        $vehiclecategories=Vehicle::where('id',$id)->first()->vehicle_image;
        $filename = $vehiclecategories;
      }
      if(@$request->get('vehicle_type_id')){
        $getvehiclemodel = VehicleType::where('id', @$request->get('vehicle_type_id'))->first();
        $model_id = (int)$getvehiclemodel->model_id;
      }else{
        $model_id = 0;
      }
      $vehicleCategories = Vehicle::where('id',$id)->update([
        'vehicle_image'           => @$filename,
        'name'                    => @$request->get('name'),
        'base_fare'               => @$request->get('base_fare'),
        'vehicle_type_id'         => @$request->get('vehicle_type_id'),
        'price_per_km'            => @$request->get('price_per_km'),
        'extra_cost_dropdown'     => @$request->get('extra_cost_dropdown'),
        'extra_cost_include'      => @$request->get('extra_cost_include'),
        'status'                  => @$request->get('status'),
        'cancellation_time_in_minutes'  => @$request->get('cancellation_time_in_minutes'),
        'cancellation_charge_in_per'    => @$request->get('cancellation_charge_in_per'),
        'total_seat'                    => @$request->get('total_seat'),
        'vehicle_model_id'         => $model_id,
        'wheel_type'               => @$request->get('wheel_type'),
        'ranking'                  => @$request->get('ranking'),
      ]);
      Notify::success('Vehicle Updated Successfully.');
      return redirect()->route('admin.vehicleindex');
    }catch(\Exception $e){
      return back()->with([
        'alert-type'    => 'danger',
        'message'       => $e->getMessage()
      ]);
    }
  }

  public function delete($id){
    $userRole = Helper::checkPermission(['vehicle-delete']);
        if(!$userRole){
            $message = "You don't have permission to access this module.";
            return view('error.permission',compact('message'));
        }
    $vehiclecategories = Vehicle::where('id',$id)->first();
    $vehiclecategories->delete();
    Notify::success('Vehicle Categorie deleted successfully.');
    return response()->json([
      'status'    => 'success',
      'title'     => 'Success!!',
      'message'   => 'Vehicle Categorie removed successfully.'
    ]);
  }

  public function change_status(Request $request){
    $vehiclecategories = Vehicle::where('id',$request->id)->first();
    if($vehiclecategories->status == "active"){
      Vehicle::where('id',$request->id)->update([
        'status'             => "inactive",
      ]);
    }
    if($vehiclecategories->status == "inactive"){
      Vehicle::where('id',$request->id)->update([
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