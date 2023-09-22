@extends('admin.layouts.app')
@section('title')
	Contacts Management
@endsection
@section('mainContent')
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Contacts Management</h2>
	</div>
	<div class="col-lg-2"></div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
	<div class="row">
		@foreach($contacts_list as $list)
		<div class="col-lg-3">
			<div class="contact-box center-version">
				<a href="profile.html">
					@if (!empty($list->avatar))
					<img alt="image" class="rounded-circle" src="{{url('storage/avatar/'.$list->avatar)}}">
					@else
					<img alt="image" class="rounded-circle" src="{{url('storage/avatar/default.png')}}"/>
					@endif
					
					<h3 class="m-b-xs"><strong>{{$list->first_name}} {{ str_limit($list->last_name, $limit = 5, $end = '...') }}</strong></h3>
					<div class="font-bold">{{$list->driver_signup_as}}</div>
					<address class="m-t-md">
						<strong>{{$list->user_type}}</strong><br>{{ str_limit($list->address, $limit = 25, $end = '...') }}<br>{{$list->city_id}}<br><abbr title="Phone">P:</abbr> ({{$list->country_code}}) {{$list->contact_number}}<br>Email:{{$list->email}}
					</address>
				</a>
				<div class="contact-box-footer">
					<div class="m-t-xs btn-group">
						<a href="#"  class="btn btn-xs btn-white" title="{{$list->country_code}} {{$list->contact_number}}"><i class="fa fa-phone"></i> Call </a>
						<a href="#"  class="btn btn-xs btn-white" title="{{$list->email}}"><i class="fa fa-envelope"></i> Email</a>
					</div>
				</div>
			</div>
		</div>
		@endforeach
	</div>
</div>
@endsection
@section('styles')
@endsection
@section('scripts')
@endsection