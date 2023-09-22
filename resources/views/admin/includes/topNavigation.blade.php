<div class="row border-bottom">
  <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
    <div class="navbar-header">
      <a class="navbar-minimalize minimalize-styl-2 btn bars" href="#">

        <i class="fa fa-bars"></i>
      </a>
    </div>
    @php 
    $total_emergencyDetails_req = App\Models\EmergencyRequest::with('user','driver','emergency_type')->where('view_status','0')->whereDay('created_at', Carbon\Carbon::now()->day)->get();
    @endphp
    <ul class="nav navbar-top-links navbar-right">
      <li>
        <span class="m-r-sm text-muted welcome-message">
        Welcome to {{Settings::get('application_title')}} Admin Panel.
      </span>
    </li>
  <li>
    @if(!empty(Auth::user()->avatar))
    <img class="rounded-circle" src="{!!  \Auth::user()->avatar !== '' ? asset("storage/avatar/".\Auth::user()->avatar) : asset('storage/avatar/default.png') !!}" alt="user-img" height="60px" width="60px">
    @else
    <img class="rounded-circle" src="{!! asset('storage/avatar/default.png') !!}" alt="user-img" accept="image/*" height="60px" width="60px">
    @endif
  </li>
  <li>
    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
      <span class="block m-t-xs font-bold" style="color:#212529;">{{  \Auth::user()->first_name }}<b class="caret"></b></span>
    </a>
    <ul class="dropdown-menu animated fadeInLeft m-t-xs">
      <li><a class="dropdown-item" href="{{ route('admin.profile') }}">Profile</a></li>
      <li class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="{{ route('admin.logout') }}">Logout</a></li>
    </ul>
  </li>
</ul>
</nav>
</div>