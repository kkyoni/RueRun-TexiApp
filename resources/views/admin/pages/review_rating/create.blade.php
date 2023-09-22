
<!-- admin user update -->
@extends('admin.layouts.app')
@section('title')
Review And Rating Management - Create
@endsection
@section('mainContent')
@if(Session::has('message'))
<div class="row">
	<div class="col-md-12">
		<div class="alert alert-{{ Session::has('alert-type') }}">
			{!! Session::get('message') !!}
		</div>
	</div>
</div>
@endif
<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Add Promocode</h2>
	</div>
	<div class="col-lg-2">

	</div>
</div>
<div class="wrapper wrapper-content">
	<div class="row">
		<div class="col-lg-12">
			<div class="ibox ">
				<div class="ibox-title">
					<h5></small></h5>

				</div>
				<div class="ibox-content">
					{!! 
					Form::open([
					'route'	=> ['admin.promocode.store'],
					'id'	=> 'userCreateForm',
					'files' => 'true' 
					])
					!!}
					@include('admin.pages.promocode.form')
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>

@endsection


