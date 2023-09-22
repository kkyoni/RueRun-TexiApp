<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Helmesvs\Notify\Facades\Notify;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Html\Builder;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Validator;
use Ixudra\Curl\Facades\Curl;
use config;
use Settings;

class MapController extends Controller{
  protected $authLayout = '';
  protected $pageLayout = '';
  /*** Create a new controller instance.** @return void*/
  public function __construct(){
    $this->authLayout = 'admin.auth.';
    $this->pageLayout = 'admin.pages.map.';
    $this->middleware('auth');
  }

  public function index(Builder $builder, Request $request){
    $user = User::whereIn('user_type',['user','driver'])->get();
    return view($this->pageLayout.'index',compact('user'));
  }
}